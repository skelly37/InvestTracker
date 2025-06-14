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
                        <span class="metric__value"><?= isset($user['created_at']) && $user['created_at'] ? formatDate($user['created_at']) : 'N/A' ?></span>
                    </div>
                    <div class="metric">
                        <span class="metric__label">Last login:</span>
                        <span class="metric__value"><?= isset($user['last_login']) && $user['last_login'] ? formatDate($user['last_login']) : 'N/A' ?></span>
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
                        <label class="label">Chart Time Interval</label>
                        <select class="input" id="chartTimeInterval">
                            <option value="1d">1 Day</option>
                            <option value="5d">5 Days</option>
                            <option value="1mo">1 Month</option>
                            <option value="3mo">3 Months</option>
                            <option value="1y">1 Year</option>
                            <option value="5y">5 Years</option>
                            <option value="max">Maximum</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="label">Interval for Price Difference</label>
                        <select class="input" id="priceDifferenceInterval">
                            <option value="day-to-day">Day-to-Day</option>
                            <option value="week-to-week">Week-to-Week</option>
                            <option value="month-to-month">Month-to-Month</option>
                            <option value="year-to-year">Year-to-Year</option>
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
    const chartTimeInterval = localStorage.getItem('chartTimeInterval') || '1mo';
    const priceDifferenceInterval = localStorage.getItem('priceDifferenceInterval') || 'day-to-day';
    
    document.getElementById('chartTimeInterval').value = chartTimeInterval;
    document.getElementById('priceDifferenceInterval').value = priceDifferenceInterval;
    
    // Save preferences
    document.getElementById('savePreferences').addEventListener('click', function() {
        const chartInterval = document.getElementById('chartTimeInterval').value;
        const priceInterval = document.getElementById('priceDifferenceInterval').value;
        
        localStorage.setItem('chartTimeInterval', chartInterval);
        localStorage.setItem('priceDifferenceInterval', priceInterval);
        
        alert('Preferences saved successfully!');
    });
    
    // Clear recent history
    document.getElementById('clearRecent').addEventListener('click', function() {
        if (confirm('Are you sure you want to clear your recent history? This action cannot be undone.')) {
            const csrfToken = '<?= htmlspecialchars($csrf_token) ?>';

            fetch('/dashboard/clear-history', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `csrf_token=${encodeURIComponent(csrfToken)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                } else {
                    alert('Error: ' + (data.message || 'Failed to clear recent history'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to clear recent history');
            });
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