<?php
class AuthController extends BaseController {
    public function showLogin(): void {
        if (Session::isLoggedIn()) {
            $this->redirect('/dashboard');
        }
        
        $this->view('auth/login', [
            'title' => 'Sign In - InvestTracker',
            'csrf_token' => $this->generateCSRF()
        ]);
    }
    
    public function login(): void {
        if (!$this->isPost()) {
            $this->redirect('/login');
        }
        
        if (!$this->validateCSRF()) {
            $this->redirect('/login', 'Invalid security token. Please try again.');
        }
        
        $input = $this->sanitizeInput($_POST);
        
        $validator = Validator::make($input, [
            'username' => ['required', 'string', ['min', 3], ['max', 50], 'username'],
            'password' => ['required', 'string', ['min', 6]]
        ]);
        
        if (!$validator->validate()) {
            foreach ($input as $key => $value) {
                if ($key !== 'password') {
                    Session::set("login_old_$key", $value);
                }
            }
            
            foreach ($validator->getErrors() as $field => $errors) {
                Session::set("login_error_$field", $errors[0]);
            }
            
            $this->redirect('/login', 'Please fix the errors below.');
        }
        
        try {
            $userData = $this->user->login($input['username'], $input['password']);

            if ($userData) {
                Session::setUser($userData);

                $this->clearOldInput();

                $redirectTo = Session::get('intended_url', '/dashboard');
                Session::remove('intended_url');

                $this->redirect($redirectTo, 'Welcome back, ' . $input['username'] . '!');
            } else {
                Session::set('login_old_username', $input['username']);
                Session::set('login_error_credentials', 'Invalid username or password.');
                $this->redirect('/login');
            }
        } catch (Exception $e) {
            if ($e->getMessage() === 'ACCOUNT_INACTIVE') {
                Session::set('login_old_username', $input['username']);
                Session::set('login_error_credentials', 'Your account is inactive. Please contact the administrator to activate your account.');
                $this->redirect('/login');
            } else {
                Session::set('login_old_username', $input['username']);
                Session::set('login_error_credentials', 'A technical error occurred. Please try again.');
                $this->redirect('/login');
            }
        }
    }
    
    public function register(): void {
        if (!$this->isPost()) {
            $this->redirect('/login');
        }
        
        if (!$this->validateCSRF()) {
            $this->redirect('/login', 'Invalid security token. Please try again.');
        }
        
        $input = $this->sanitizeInput($_POST);
        
        $validator = Validator::make($input, [
            'username' => ['required', 'string', ['min', 3], ['max', 50], 'username'],
            'password' => ['required', 'string', ['min', 6], ['max', 255]],
            'confirm_password' => ['required', 'string']
        ]);
        
        if (!$validator->validate()) {
            $this->handleRegistrationValidationErrors($validator, $input);
            $this->redirect('/login', 'Please fix the errors below.');
        }
        
        if ($input['password'] !== $input['confirm_password']) {
            Session::set('register_old_username', $input['username']);
            Session::set('register_error_confirm_password', 'Passwords do not match.');
            $this->redirect('/login', 'Passwords do not match.');
        }
        
        $result = $this->user->register($input['username'], $input['password']);
        
        if (is_numeric($result) && $result > 0) {
            $this->clearOldInput();
            $this->redirect('/login', 'Account created successfully. Please login.');
        } elseif ($result === 'USER_EXISTS') {
            Session::set('register_old_username', $input['username']);
            Session::set('register_error_username', 'Username already exists. Please choose a different one.');
            $this->redirect('/login', 'Username already exists. Please choose a different one.');
        } else {
            Session::set('register_old_username', $input['username']);
            $this->redirect('/login', 'Registration failed due to a technical error. Please try again.');
        }
    }
    
    public function logout(): void {
        Session::logout();
        $this->redirect('/login', 'You have been logged out successfully.');
    }
    
    public function changePassword(): void {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            $this->redirect('/settings');
        }
        
        if (!$this->validateCSRF()) {
            $this->redirect('/settings', 'Invalid security token. Please try again.');
        }
        
        $input = $this->sanitizeInput($_POST);
        $userId = Session::getUserId();
        
        $validator = Validator::make($input, [
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', ['min', 6], ['max', 255]],
            'confirm_password' => ['required', 'string']
        ]);
        
        if (!$validator->validate()) {
            $this->handleValidationErrors($validator, $input);
            $this->redirect('/settings', 'Please fix the errors below.');
        }
        
        if ($input['new_password'] !== $input['confirm_password']) {
            Session::set('error_confirm_password', 'Passwords do not match.');
            $this->redirect('/settings', 'Passwords do not match.');
        }
        
        $result = $this->user->changePassword($userId, $input['current_password'], $input['new_password']);
        
        if ($result) {
            $this->redirect('/settings', 'Password changed successfully.');
        } else {
            Session::set('error_current_password', 'Current password is incorrect.');
            $this->redirect('/settings', 'Current password is incorrect.');
        }
    }
    
    private function handleValidationErrors(Validator $validator, array $input): void {
        foreach ($input as $key => $value) {
            if (!str_contains($key, 'password')) {
                Session::set("old_$key", $value);
            }
        }
        
        foreach ($validator->getErrors() as $field => $errors) {
            Session::set("error_$field", $errors[0]);
        }
    }
    
    private function handleRegistrationValidationErrors(Validator $validator, array $input): void {
        foreach ($input as $key => $value) {
            if (!str_contains($key, 'password')) {
                Session::set("register_old_$key", $value);
            }
        }
        
        foreach ($validator->getErrors() as $field => $errors) {
            Session::set("register_error_$field", $errors[0]);
        }
    }
    
    private function clearOldInput(): void {
        $loginKeys = ['login_old_username', 'login_error_username', 'login_error_password', 'login_error_credentials'];
        
        $registerKeys = ['register_old_username', 'register_error_username', 'register_error_password', 'register_error_confirm_password'];
        
        $settingsKeys = ['old_username', 'error_username', 'error_password', 'error_confirm_password', 'error_current_password', 'error_new_password'];
        
        $allKeys = array_merge($loginKeys, $registerKeys, $settingsKeys);
        
        foreach ($allKeys as $key) {
            Session::remove($key);
        }
    }

    public function deleteAccount(): void {
        $this->requireAuth();

        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Invalid request method'], 405);
            return;
        }

        if (!$this->validateCSRF()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }

        $userId = Session::getUserId();

        try {
            $result = $this->user->deleteUser($userId);

            if ($result) {
                Session::logout();
                $this->json(['success' => true, 'message' => 'Account deleted successfully', 'redirect' => '/login']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to delete account'], 500);
            }
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'An error occurred while deleting account'], 500);
        }
    }
}