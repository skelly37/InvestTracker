<?php
class AuthController extends BaseController {
    
    public function showLogin(): void {
        // Redirect if already logged in
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
        
        // Validate input
        $validator = Validator::make($input, [
            'username' => ['required', 'string', ['min', 3], ['max', 50], 'username'],
            'password' => ['required', 'string', ['min', 6]]
        ]);
        
        if (!$validator->validate()) {
            // Store old input and errors
            foreach ($input as $key => $value) {
                if ($key !== 'password') {
                    Session::set("old_$key", $value);
                }
            }
            
            foreach ($validator->getErrors() as $field => $errors) {
                Session::set("error_$field", $errors[0]);
            }
            
            $this->redirect('/login', 'Please fix the errors below.');
        }
        
        // Attempt authentication using login method
        $loginResult = $this->user->login($input['username'], $input['password']);
        
        if ($loginResult) {
            // Clear old input
            $this->clearOldInput();
            
            // Redirect to intended page or dashboard
            $redirectTo = Session::get('intended_url', '/dashboard');
            Session::remove('intended_url');
            
            $this->redirect($redirectTo, 'Welcome back, ' . $input['username'] . '!');
        } else {
            // Store username for convenience
            Session::set('old_username', $input['username']);
            $this->redirect('/login', 'Invalid username or password.');
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
        
        // Validate input (without email for now)
        $validator = Validator::make($input, [
            'username' => ['required', 'string', ['min', 3], ['max', 50], 'username'],
            'password' => ['required', 'string', ['min', 6], ['max', 255]],
            'confirm_password' => ['required', 'string']
        ]);
        
        if (!$validator->validate()) {
            $this->handleValidationErrors($validator, $input);
            $this->redirect('/login', 'Please fix the errors below.');
        }
        
        // Check password confirmation
        if ($input['password'] !== $input['confirm_password']) {
            Session::set('old_username', $input['username']);
            Session::set('error_confirm_password', 'Passwords do not match.');
            $this->redirect('/login', 'Passwords do not match.');
        }
        
        // Create user using register method (with dummy email for now)
        $result = $this->user->register($input['username'], $input['password']);
        
        if ($result) {
            $this->clearOldInput();
            $this->redirect('/login', 'Account created successfully. Please login.');
        } else {
            Session::set('old_username', $input['username']);
            $this->redirect('/login', 'Failed to create account. Username may already exist.');
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
        
        // Validate input
        $validator = Validator::make($input, [
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', ['min', 6], ['max', 255]],
            'confirm_password' => ['required', 'string']
        ]);
        
        if (!$validator->validate()) {
            $this->handleValidationErrors($validator, $input);
            $this->redirect('/settings', 'Please fix the errors below.');
        }
        
        // Check password confirmation
        if ($input['new_password'] !== $input['confirm_password']) {
            Session::set('error_confirm_password', 'Passwords do not match.');
            $this->redirect('/settings', 'Passwords do not match.');
        }
        
        // Change password using changePassword method
        $result = $this->user->changePassword($userId, $input['current_password'], $input['new_password']);
        
        if ($result) {
            $this->redirect('/settings', 'Password changed successfully.');
        } else {
            Session::set('error_current_password', 'Current password is incorrect.');
            $this->redirect('/settings', 'Current password is incorrect.');
        }
    }
    
    private function handleValidationErrors(Validator $validator, array $input): void {
        // Store old input (except passwords)
        foreach ($input as $key => $value) {
            if (!str_contains($key, 'password')) {
                Session::set("old_$key", $value);
            }
        }
        
        // Store errors
        foreach ($validator->getErrors() as $field => $errors) {
            Session::set("error_$field", $errors[0]);
        }
    }
    
    private function clearOldInput(): void {
        $keys = ['old_username', 'error_username', 'error_password', 'error_confirm_password', 'error_current_password', 'error_new_password'];
        foreach ($keys as $key) {
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
            // Delete user account
            $result = $this->user->deleteUser($userId);

            if ($result) {
                // Logout user after successful deletion
                Session::logout();
                $this->json(['success' => true, 'message' => 'Account deleted successfully', 'redirect' => '/login']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to delete account'], 500);
            }
        } catch (Exception $e) {
            error_log("Delete account error: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'An error occurred while deleting account'], 500);
        }
    }
}