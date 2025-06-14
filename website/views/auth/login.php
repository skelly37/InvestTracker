<?php
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="login-container">
    <div class="login-form">
        <h1 class="login-form__title">InvestTracker</h1>
        
        <?php if ($flashMessage): ?>
            <div class="alert alert--<?= strpos($flashMessage, 'success') !== false ? 'success' : 'danger' ?>">
                <?= htmlspecialchars($flashMessage) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="/login">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            
            <?php if ($loginCredentialsError = Session::get('login_error_credentials')): ?>
                <div class="alert alert--danger">
                    <?= htmlspecialchars($loginCredentialsError) ?>
                </div>
                <?php Session::remove('login_error_credentials'); ?>
            <?php endif; ?>
            
            <div class="form-group">
                <label class="label" for="username">Username</label>
                <input type="text" id="username" name="username" class="input <?= Session::get('login_error_username') ? 'input--error' : '' ?>" 
                       value="<?= htmlspecialchars(Session::get('login_old_username', '')) ?>" required>
                <?php if ($error = Session::get('login_error_username')): ?>
                    <div class="form-error"><?= htmlspecialchars($error) ?></div>
                    <?php Session::remove('login_error_username'); ?>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label class="label" for="password">Password</label>
                <input type="password" id="password" name="password" class="input <?= Session::get('login_error_password') ? 'input--error' : '' ?>" required>
                <?php if ($error = Session::get('login_error_password')): ?>
                    <div class="form-error"><?= htmlspecialchars($error) ?></div>
                    <?php Session::remove('login_error_password'); ?>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn--primary btn--full">Sign In</button>
            </div>
        </form>
        
        <div class="login-form__footer">
            <h3>New User? Auto-Register</h3>
            <form method="POST" action="/register">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                
                <div class="form-group">
                    <label class="label" for="reg_username">Username</label>
                    <input type="text" id="reg_username" name="username" class="input <?= Session::get('register_error_username') ? 'input--error' : '' ?>" 
                           value="<?= htmlspecialchars(Session::get('register_old_username', '')) ?>" required>
                    <?php if ($error = Session::get('register_error_username')): ?>
                        <div class="form-error"><?= htmlspecialchars($error) ?></div>
                        <?php Session::remove('register_error_username'); ?>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label class="label" for="reg_password">Password</label>
                    <input type="password" id="reg_password" name="password" class="input <?= Session::get('register_error_password') ? 'input--error' : '' ?>" required>
                    <?php if ($error = Session::get('register_error_password')): ?>
                        <div class="form-error"><?= htmlspecialchars($error) ?></div>
                        <?php Session::remove('register_error_password'); ?>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label class="label" for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="input <?= Session::get('register_error_confirm_password') ? 'input--error' : '' ?>" required>
                    <?php if ($error = Session::get('register_error_confirm_password')): ?>
                        <div class="form-error"><?= htmlspecialchars($error) ?></div>
                        <?php Session::remove('register_error_confirm_password'); ?>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn--primary btn--full">Create Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
Session::remove('login_old_username');
Session::remove('register_old_username');
require_once __DIR__ . '/../layouts/footer.php'; 
?>