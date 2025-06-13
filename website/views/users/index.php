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
            
            <?php if (isset($error)): ?>
                <div class="alert alert--danger mb-2">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <div class="flex flex--between mb-3">
                <h1>User Management</h1>
                <button class="btn btn--primary" id="addUserBtn">Add New User</button>
            </div>
            
            <?php if (!empty($users)): ?>
                <div class="card">
                    <div class="card__body p-0">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Last Login</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($user['id']) ?></td>
                                        <td><?= htmlspecialchars($user['username']) ?></td>
                                        <td>
                                            <select class="input" style="width: auto;" 
                                                    onchange="updateUserRole(<?= $user['id'] ?>, this.value)"
                                                    <?= $user['id'] == Session::getUserId() ? 'disabled' : '' ?>>
                                                <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                            </select>
                                        </td>
                                        <td>
                                            <span class="user-status user-status--<?= ($user['is_active'] ?? true) ? 'active' : 'inactive' ?>">
                                                <?= ($user['is_active'] ?? true) ? 'Active' : 'Inactive' ?>
                                            </span>
                                        </td>
                                        <td><?= date('Y-m-d', strtotime($user['created_at'] ?? 'now')) ?></td>
                                        <td><?= $user['last_login'] ? date('Y-m-d', strtotime($user['last_login'])) : 'Never' ?></td>
                                        <td>
                                            <div class="user-actions">
                                                <?php if ($user['id'] != Session::getUserId()): ?>
                                                    <button class="btn btn--small btn--secondary" 
                                                            onclick="toggleUserActive(<?= $user['id'] ?>)">
                                                        <?= ($user['is_active'] ?? true) ? 'Deactivate' : 'Activate' ?>
                                                    </button>
                                                    <button class="btn btn--small btn--danger" 
                                                            onclick="deleteUser(<?= $user['id'] ?>, '<?= htmlspecialchars($user['username']) ?>')">
                                                        Delete
                                                    </button>
                                                <?php else: ?>
                                                    <span class="text--neutral">Current User</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php else: ?>
                <div class="card text-center">
                    <div class="card__body">
                        <h3>No users found</h3>
                        <p>There are no users in the system.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div id="addUserModal" class="modal hidden">
    <div class="modal-overlay" onclick="closeAddUserModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add New User</h3>
            <button class="modal-close" onclick="closeAddUserModal()">×</button>
        </div>
        <form method="POST" action="/users/create" id="addUserForm">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            
            <div class="modal-body">
                <div class="form-group">
                    <label class="label" for="new_username">Username</label>
                    <input type="text" id="new_username" name="username" class="input" required>
                </div>
                
                <div class="form-group">
                    <label class="label" for="new_password">Password</label>
                    <input type="password" id="new_password" name="password" class="input" required>
                </div>
                
                <div class="form-group">
                    <label class="label" for="new_role">Role</label>
                    <select id="new_role" name="role" class="input">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn--secondary" onclick="closeAddUserModal()">Cancel</button>
                <button type="submit" class="btn btn--primary">Create User</button>
            </div>
        </form>
    </div>
</div>

<style>
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal.hidden {
    display: none;
}

.modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
}

.modal-content {
    background: #FFF8DC;
    border: 1px solid #4A4A4A;
    width: 90%;
    max-width: 500px;
    position: relative;
    z-index: 1001;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #4A4A4A;
}

.modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #4A4A4A;
}

.modal-body {
    padding: 20px;
}

.modal-footer {
    display: flex;
    gap: 10px;
    padding: 20px;
    border-top: 1px solid #4A4A4A;
    justify-content: flex-end;
}

.user-actions {
    display: flex;
    gap: 8px;
}

.user-status {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
}

.user-status--active {
    background: #d4edda;
    color: #155724;
}

.user-status--inactive {
    background: #f8d7da;
    color: #721c24;
}
</style>

<script>
const csrfToken = '<?= htmlspecialchars($csrf_token) ?>';

function openAddUserModal() {
    document.getElementById('addUserModal').classList.remove('hidden');
}

function closeAddUserModal() {
    document.getElementById('addUserModal').classList.add('hidden');
    document.getElementById('addUserForm').reset();
}

function updateUserRole(userId, newRole) {
    if (confirm(`Change user role to ${newRole}?`)) {
        fetch('/users/update-role', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `user_id=${userId}&role=${encodeURIComponent(newRole)}&csrf_token=${encodeURIComponent(csrfToken)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to update user role');
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to update user role');
            location.reload();
        });
    } else {
        location.reload();
    }
}

function toggleUserActive(userId) {
    if (confirm('Toggle user active status?')) {
        fetch('/users/toggle-active', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `user_id=${userId}&csrf_token=${encodeURIComponent(csrfToken)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to update user status');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to update user status');
        });
    }
}

function deleteUser(userId, username) {
    if (confirm(`Are you sure you want to delete user "${username}"? This action cannot be undone.`)) {
        fetch('/users/delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `user_id=${userId}&csrf_token=${encodeURIComponent(csrfToken)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to delete user');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to delete user');
        });
    }
}

// Add event listener for the Add User button
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('addUserBtn').addEventListener('click', openAddUserModal);
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>