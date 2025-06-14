<?php
class UserController extends BaseController {
    
    public function index(): void {
        $this->requireAdmin();
        
        try {
            $users = $this->user->getAllUsers();
            
            $this->view('users/index', [
                'title' => 'User Management - InvestTracker',
                'users' => $users,
                'csrf_token' => $this->generateCSRF()
            ]);
            
        } catch (Exception $e) {
            error_log("Users index error: " . $e->getMessage());
            
            $this->view('users/index', [
                'title' => 'User Management - InvestTracker',
                'users' => [],
                'error' => 'Unable to load users. Please try again later.',
                'csrf_token' => $this->generateCSRF()
            ]);
        }
    }
    
    public function toggleActive(): void {
        $this->requireAdmin();
        
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Invalid request method'], 405);
            return;
        }
        
        if (!$this->validateCSRF()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $input = $this->sanitizeInput($_POST);
        $userId = (int)($input['user_id'] ?? 0);
        
        if ($userId <= 0) {
            $this->json(['success' => false, 'message' => 'Invalid user ID'], 400);
            return;
        }
        
        if ($userId === Session::getUserId()) {
            $this->json(['success' => false, 'message' => 'You cannot deactivate your own account'], 400);
            return;
        }
        
        try {
            $result = $this->user->toggleActive($userId);
            
            if ($result) {
                $newStatus = $this->user->isActive($userId);
                
                $this->json([
                    'success' => true,
                    'message' => 'User status updated successfully',
                    'active' => $newStatus
                ]);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to update user status'], 500);
            }
        } catch (Exception $e) {
            error_log("Toggle active error: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Failed to update user status'], 500);
        }
    }
    
    public function updateRole(): void {
        $this->requireAdmin();
        
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Invalid request method'], 405);
            return;
        }
        
        if (!$this->validateCSRF()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $input = $this->sanitizeInput($_POST);
        $userId = (int)($input['user_id'] ?? 0);
        $role = $input['role'] ?? '';
        
        if ($userId <= 0) {
            $this->json(['success' => false, 'message' => 'Invalid user ID'], 400);
            return;
        }
        
        $validRoles = ['user', 'admin'];
        if (!in_array($role, $validRoles)) {
            $this->json(['success' => false, 'message' => 'Invalid role'], 400);
            return;
        }
        
        if ($userId === Session::getUserId()) {
            $this->json(['success' => false, 'message' => 'You cannot change your own role'], 400);
            return;
        }
        
        try {
            if ($this->user->updateRole($userId, $role)) {
                $this->json(['success' => true, 'message' => 'User role updated successfully']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to update user role'], 500);
            }
        } catch (Exception $e) {
            error_log("Update role error: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Failed to update user role'], 500);
        }
    }
    
    public function delete(): void {
        $this->requireAdmin();
        
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Invalid request method'], 405);
            return;
        }
        
        if (!$this->validateCSRF()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $input = $this->sanitizeInput($_POST);
        $userId = (int)($input['user_id'] ?? 0);
        
        if ($userId <= 0) {
            $this->json(['success' => false, 'message' => 'Invalid user ID'], 400);
            return;
        }
        
        if ($userId === Session::getUserId()) {
            $this->json(['success' => false, 'message' => 'You cannot delete your own account'], 400);
            return;
        }
        
        try {
            if ($this->user->delete($userId)) {
                $this->json(['success' => true, 'message' => 'User deleted successfully']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to delete user'], 500);
            }
        } catch (Exception $e) {
            error_log("Delete user error: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Failed to delete user'], 500);
        }
    }
    
    public function create(): void {
        $this->requireAdmin();
        
        if (!$this->isPost()) {
            $this->redirect('/users');
            return;
        }
        
        if (!$this->validateCSRF()) {
            $this->redirect('/users', 'Invalid security token. Please try again.');
            return;
        }
        
        $input = $this->sanitizeInput($_POST);
        
        $validator = Validator::make($input, [
            'username' => ['required', 'string', ['min', 3], ['max', 50], 'username'],
            'password' => ['required', 'string', ['min', 6], ['max', 255]],
            'role' => ['required', 'string', ['in', ['user', 'admin']]]
        ]);
        
        if (!$validator->validate()) {
            $errors = [];
            foreach ($validator->getErrors() as $field => $fieldErrors) {
                $errors[] = $fieldErrors[0];
            }
            $this->redirect('/users', 'Validation errors: ' . implode(', ', $errors));
            return;
        }
        
        try {
            $userId = $this->user->register($input['username'], $input['password'], $input['role']);
            
            if ($userId) {
                $this->redirect('/users', 'User created successfully.');
            } else {
                $this->redirect('/users', 'Failed to create user. Username may already exist.');
            }
        } catch (Exception $e) {
            error_log("Create user error: " . $e->getMessage());
            $this->redirect('/users', 'Failed to create user. Please try again.');
        }
    }
}