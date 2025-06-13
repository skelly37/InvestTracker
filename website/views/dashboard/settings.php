<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navigation.php';
?>

<div class="dashboard-layout">
    <div class="main-content">
        <div class="container">
            <?php if ($flashMessage): ?>
                <div class="alert alert--success mb-2">
                    <?= htmlspecialchars($flashMessage) ?>
                </div>
            <?php endif; ?>
            
            <h1 class="mb-3">Settings</h1>
            
            <div class="settings-grid">
                <!-- Account Information -->
                <div class="settings-section">
                    <div class="settings-section__title">Account Information</div>
                    
                    <div class="metric">
                        <span class="metric__label">Username:</span>
                        <span class="metric__value"><?= htmlspecialchars($user['username']) ?></span>
                    </div>
                    <div class="metric">
                        <span class="metric__label">Role:</span>
                        <span class="metric__value"><?= htmlspecialchars(ucfirst($user['role'])) ?></span>
                    </div>
                    <div class="metric">
                        <span class="metric__label">Member since:</span>
                        <span class="metric__value"><?= formatDate($user['created_at']) ?></span>
                    </div>
                    <div class="metric">
                        <span class="metric__label">Last login:</span>
                        <span class="metric__value"><?= formatDate($user['last_login']) ?></span>
                    </div>
                </div>
                
                <!-- Change Password -->
                <div class="settings-section">
                    <div class="settings-section__title">Change Password</div>
                    
                    <form method="POST" action="/auth/change-password">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        
                        <div class="form-group">
                            <label class="label" for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" 
                                   class="input <?= Session::get('error_current_password') ? 'input--error' : '' ?>" required>
                            <?php if ($error = Session::get('error_current_password')): ?>
                                <div class="form-error"><?= htmlspecialchars($error) ?></div>
                                <?php Session::remove('error_current_password'); ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label class="label" for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" 
                                   class="input <?= Session::get('error_new_password') ? 'input--error' : '' ?>" required>
                            <?php if ($error = Session::get('error_new_password')): ?>
                                <div class="form-error"><?= htmlspecialchars($error) ?></div>
                                <?php Session::remove('error_new_password'); ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label class="label" for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" 
                                   class="input <?= Session::get('error_confirm_password') ? 'input--error' : '' ?>" required>
                            <?php if ($error = Session::get('error_confirm_password')): ?>
                                <div class="form-error"><?= htmlspecialchars($error) ?></div>
                                <?php Session::remove('error_confirm_password'); ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn--primary">Change Password</button>
                        </div>
                    </form>
                </div>
                
                <!-- Application Settings -->
                <div class="settings-section">
                    <div class="settings-section__title">Preferences</div>
                    
                    <div class="form-group">
                        <label class="label">Dashboard Layout</label>
                        <select class="input" id="dashboardLayout">
                            <option value="3-column">3 Column Layout</option>
                            <option value="2-column">2 Column Layout</option>
                            <option value="list">List View</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="label">Auto-refresh Data</label>
                        <select class="input" id="autoRefresh">
                            <option value="30">Every 30 seconds</option>
                            <option value="60">Every minute</option>
                            <option value="300">Every 5 minutes</option>
                            <option value="0">Disabled</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <button type="button" class="btn btn--primary" id="savePreferences">
                            Save Preferences
                        </button>
                    </div>
                </div>
                
                <!-- Data Management -->
                <div class="settings-section">
                    <div class="settings-section__title">Data Management</div>
                    
                    <div class="form-group">
                        <label class="label">Export Your Data</label>
                        <p style="font-size: 14px; color: #666; margin-bottom: 10px;">
                            Download your favorites and recently viewed stocks as JSON.
                        </p>
                        <button type="button" class="btn btn--secondary" id="exportData">
                            Export Data
                        </button>
                    </div>
                    
                    <div class="form-group">
                        <label class="label">Clear Recently Viewed</label>
                        <p style="font-size: 14px; color: #666; margin-bottom: 10px;">
                            Remove all items from your recently viewed stocks list.
                        </p>
                        <button type="button" class="btn btn--secondary" id="clearRecent">
                            Clear Recent History
                        </button>
                    </div>
                </div>
                
                <!-- Danger Zone -->
                <div class="settings-section danger-zone">
                    <div class="settings-section__title">Danger Zone</div>
                    
                    <div class="form-group">
                        <label class="label">Delete Account</label>
                        <p style="font-size: 14px; color: #666; margin-bottom: 10px;">
                            Permanently delete your account and all associated data. This action cannot be undone.
                        </p>
                        <button type="button" class="btn btn--danger" id="deleteAccount">
                            Delete My Account
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load preferences from localStorage
    const dashboardLayout = localStorage.getItem('dashboardLayout') || '3-column';
    const autoRefresh = localStorage.getItem('autoRefresh') || '60';
    
    document.getElementById('dashboardLayout').value = dashboardLayout;
    document.getElementById('autoRefresh').value = autoRefresh;
    
    // Save preferences
    document.getElementById('savePreferences').addEventListener('click', function() {
        const layout = document.getElementById('dashboardLayout').value;
        const refresh = document.getElementById('autoRefresh').value;
        
        localStorage.setItem('dashboardLayout', layout);
        localStorage.setItem('autoRefresh', refresh);
        
        alert('Preferences saved successfully!');
    });
    
    // Export data
    document.getElementById('exportData').addEventListener('click', function() {
        // This would typically make an API call to get user data
        const userData = {
            exported_at: new Date().toISOString(),
            user_id: <?= Session::getUserId() ?>,
            username: '<?= htmlspecialchars($user['username']) ?>',
            message: 'Data export functionality would be implemented here'
        };
        
        const blob = new Blob([JSON.stringify(userData, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'investtracker-data.json';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    });
    
    // Clear recent history
    document.getElementById('clearRecent').addEventListener('click', function() {
        if (confirm('Are you sure you want to clear your recent viewing history?')) {
            // This would make an API call to clear recent history
            alert('Recent history cleared! (This would be implemented in the backend)');
        }
    });
    
    // Delete account
    document.getElementById('deleteAccount').addEventListener('click', function() {
        const confirmation = prompt('Type "DELETE" to confirm account deletion:');
        if (confirmation === 'DELETE') {
            if (confirm('Are you absolutely sure? This action cannot be undone!')) {
                alert('Account deletion would be processed here. Please contact an administrator.');
            }
        }
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>