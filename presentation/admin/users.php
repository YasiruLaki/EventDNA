<?php
require_once "includes/guard.php";
require_once "../../data/database.php";
require_once "../../application/controllers/AdminController.php";

$controller = new AdminController($conn);

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_valid()) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        $name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $roleId = (int)($_POST['role_id'] ?? 1);
        
        if ($name && $email && $password && $roleId) {
            $res = $controller->createUser($name, $email, $password, $roleId);
            if ($res['success']) {
                $success = "User created successfully.";
            } else {
                $error = $res['message'];
            }
        } else {
            $error = "All fields are required for creating a user.";
        }
    } elseif ($action === 'update') {
        $userId = (int)($_POST['user_id'] ?? 0);
        $name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $roleId = (int)($_POST['role_id'] ?? 1);
        $accountStatus = $_POST['account_status'] ?? 'ACTIVE';
        
        if ($userId && $name && $email && $roleId && $accountStatus) {
            $res = $controller->updateUser($userId, $name, $email, $roleId, $accountStatus, $password);
            if ($res['success']) {
                $success = "User updated successfully.";
            } else {
                $error = $res['message'];
            }
        } else {
            $error = "Name, email, and role are required.";
        }
    } elseif ($action === 'delete') {
        $userId = (int)($_POST['user_id'] ?? 0);
        if ($userId) {
            $res = $controller->deleteUser($userId);
            if ($res['success']) {
                $success = "User deleted successfully.";
            } else {
                $error = $res['message'];
            }
        }
    }
}

$search = $_GET['search'] ?? '';
$roleFilter = $_GET['role'] ?? '';
$statusFilter = $_GET['status'] ?? '';

$users = $controller->getAllUsers($search, $roleFilter, $statusFilter);

// Get roles for the forms
$rolesRes = $conn->query("SELECT role_id, role_name FROM roles ORDER BY role_id ASC");
$roles = $rolesRes->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>User Management - EventDNA Admin</title>
  <link rel="stylesheet" href="../../globals.css" />
  <link rel="stylesheet" href="./styles.css" />
  <style>
    .modal {
      display: none;
      position: fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(0,0,0,0.5);
      z-index: 9999;
      align-items: center;
      justify-content: center;
    }
    .modal.active {
      display: flex;
    }
    .modal-content {
      background: #fff;
      padding: 2rem;
      border-radius: 12px;
      width: 100%;
      max-width: 500px;
      position: relative;
    }
    .close-modal {
      position: absolute;
      top: 1rem;
      right: 1rem;
      cursor: pointer;
      background: none; border: none;
      color: var(--text-secondary);
    }
    .form-group {
      margin-bottom: 1rem;
    }
    .form-group label {
      display: block; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.9rem;
    }
    .form-input, .form-select {
      width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 8px; font-family: inherit;
    }
    .btn-submit {
      width: 100%; padding: 0.75rem; background: var(--primary); color: #fff; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; margin-top: 1rem;
    }
  </style>
</head>
<body>
  <div class="admin-layout">
    <aside class="admin-sidebar">
      <div class="admin-logo">
        <img src="../images/logo.png" alt="EventDNA" />
        <span style="display: block; font-size: 0.75rem; font-weight: 800; color: var(--primary); letter-spacing: 0.1em; text-transform: uppercase; margin-top: 0.25rem;">Admin</span>
      </div>
      <nav class="admin-nav">
        <a href="dashboard.php" class="admin-nav-item"><svg style="width: 18px;" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg> Dashboard</a>
        <a href="users.php" class="admin-nav-item active"><svg style="width: 18px;" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg> Users</a>
        <a href="events.php" class="admin-nav-item"><svg style="width: 18px;" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg> Events</a>
        <a href="moderation.php" class="admin-nav-item"><svg style="width: 18px;" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg> Moderation</a>
        <a href="analytics.php" class="admin-nav-item"><svg style="width: 18px;" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" x2="18" y1="20" y2="10"/><line x1="12" x2="12" y1="20" y2="4"/><line x1="6" x2="6" y1="20" y2="14"/></svg> Analytics</a>
      </nav>
      <div class="admin-footer">
        <a href="logout.php" class="admin-nav-item" style="color: var(--danger);"><svg style="width: 18px;" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg> Log Out</a>
      </div>
    </aside>

    <main class="admin-content">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h1 class="page-title">Users</h1>
        <button class="btn-primary" onclick="openModal('createModal')" style="padding: 0.6rem 1.25rem; border-radius: 8px; border: none; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; background: var(--primary); color: white; font-weight: 600;">
            <svg style="width: 16px;" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg> Add User
        </button>
      </div>

      <?php if ($success): ?>
          <div style="background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.2); color: var(--success); padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-weight: 600;">
              <?php echo htmlspecialchars($success); ?>
          </div>
      <?php endif; ?>
      <?php if ($error): ?>
          <div style="background: rgba(220, 38, 38, 0.06); border: 1px solid rgba(220, 38, 38, 0.25); color: var(--danger); padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-weight: 600;">
              <?php echo htmlspecialchars($error); ?>
          </div>
      <?php endif; ?>

      <form method="GET" class="filters-bar" style="display: flex; gap: 1rem; margin-bottom: 2rem;">
        <div class="search-box" style="flex: 1; display: flex; align-items: center; background: #fff; border: 1px solid var(--border-color); border-radius: 8px; padding: 0 1rem;">
          <svg style="color: var(--text-secondary); width: 18px;" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
          <input type="text" name="search" value="<?= h($search) ?>" placeholder="Search users by name or email..." style="border: none; outline: none; padding: 0.75rem; width: 100%;" />
        </div>
        <select class="filter-select" name="role" style="padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 8px; outline: none;" onchange="this.form.submit()">
          <option value="">All Roles</option>
          <?php foreach($roles as $r): ?>
            <option value="<?= h($r['role_name']) ?>" <?= $roleFilter === $r['role_name'] ? 'selected' : '' ?>><?= h(ucfirst(strtolower($r['role_name']))) ?></option>
          <?php endforeach; ?>
        </select>
        <select class="filter-select" name="status" style="padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 8px; outline: none;" onchange="this.form.submit()">
          <option value="">All Statuses</option>
          <option value="ACTIVE" <?= $statusFilter === 'ACTIVE' ? 'selected' : '' ?>>Active</option>
          <option value="DISABLED" <?= $statusFilter === 'DISABLED' ? 'selected' : '' ?>>Disabled</option>
          <option value="SUSPENDED" <?= $statusFilter === 'SUSPENDED' ? 'selected' : '' ?>>Suspended</option>
        </select>
      </form>

      <div style="overflow-x: auto; background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid var(--border-color);">
        <table class="data-table" style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
            <tr style="border-bottom: 1px solid var(--border-color); background: #f8fafc;">
                <th style="padding: 1rem 1.5rem; font-weight: 600; color: var(--text-secondary); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em;">Name</th>
                <th style="padding: 1rem 1.5rem; font-weight: 600; color: var(--text-secondary); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em;">Email</th>
                <th style="padding: 1rem 1.5rem; font-weight: 600; color: var(--text-secondary); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em;">Role</th>
                <th style="padding: 1rem 1.5rem; font-weight: 600; color: var(--text-secondary); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em;">Status</th>
                <th style="padding: 1rem 1.5rem; font-weight: 600; color: var(--text-secondary); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em;">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $u): ?>
            <tr style="border-bottom: 1px solid var(--border-color);">
                <td style="padding: 1.25rem 1.5rem; font-weight: 600; color: var(--secondary);"><?= h($u['full_name']) ?></td>
                <td style="padding: 1.25rem 1.5rem; color: var(--text-secondary);"><?= h($u['email']) ?></td>
                <td style="padding: 1.25rem 1.5rem;"><span style="background: var(--primary-tint); color: var(--primary); padding: 0.25rem 0.75rem; border-radius: 999px; font-size: 0.75rem; font-weight: 700;"><?= h(ucfirst(strtolower($u['role_name']))) ?></span></td>
                <td style="padding: 1.25rem 1.5rem;">
                    <?php if ($u['status'] === 'ACTIVE'): ?>
                        <span class="status-badge status-active" style="background: rgba(34, 197, 94, 0.1); color: var(--success); padding: 0.25rem 0.75rem; border-radius: 999px; font-size: 0.75rem; font-weight: 700;">Active</span>
                    <?php elseif ($u['status'] === 'SUSPENDED'): ?>
                        <span class="status-badge" style="background: rgba(245, 158, 11, 0.1); color: #d97706; padding: 0.25rem 0.75rem; border-radius: 999px; font-size: 0.75rem; font-weight: 700;">Suspended</span>
                    <?php else: ?>
                        <span class="status-badge status-disabled" style="background: rgba(100, 116, 139, 0.1); color: var(--text-secondary); padding: 0.25rem 0.75rem; border-radius: 999px; font-size: 0.75rem; font-weight: 700;">Disabled</span>
                    <?php endif; ?>
                </td>
                <td style="padding: 1.25rem 1.5rem; display: flex; gap: 0.75rem;">
                    <button onclick="openEditModal(<?= $u['user_id'] ?>, '<?= h(addslashes($u['full_name'])) ?>', '<?= h(addslashes($u['email'])) ?>', <?= $u['role_id'] ?>, '<?= h(addslashes($u['status'])) ?>')" style="padding: 0.4rem 1rem; border: 1px solid var(--border-color); background: #fff; border-radius: 6px; cursor: pointer; font-weight: 600; color: var(--secondary);">Edit</button>
                    
                    <?php if ($u['user_id'] != $_SESSION['user_id']): ?>
                    <form method="POST" style="display:inline;" onsubmit="return initCustomConfirm(this, 'Are you sure you want to delete this user?', event);">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                        <button type="submit" style="padding: 0.4rem 1rem; border: 1px solid rgba(220, 38, 38, 0.3); background: rgba(220, 38, 38, 0.05); color: var(--danger); border-radius: 6px; cursor: pointer; font-weight: 600;">Delete</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="5" style="padding: 2rem; text-align: center; color: var(--text-secondary);">No users found.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
      </div>
    </main>
  </div>

  <!-- Create Modal -->
  <div id="createModal" class="modal">
    <div class="modal-content">
      <button class="close-modal" onclick="closeModal('createModal')"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
      <h2 style="margin-bottom: 1.5rem; color: var(--secondary);">Add New User</h2>
      <form method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="create">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="full_name" class="form-input" required>
        </div>
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" class="form-input" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="form-input" required minlength="8">
        </div>
        <div class="form-group">
            <label>Role</label>
            <select name="role_id" class="form-select" required>
                <?php foreach($roles as $r): ?>
                    <option value="<?= $r['role_id'] ?>"><?= h(ucfirst(strtolower($r['role_name']))) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn-submit">Create User</button>
      </form>
    </div>
  </div>

  <!-- Edit Modal -->
  <div id="editModal" class="modal">
    <div class="modal-content">
      <button class="close-modal" onclick="closeModal('editModal')"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
      <h2 style="margin-bottom: 1.5rem; color: var(--secondary);">Edit User</h2>
      <form method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="user_id" id="edit_user_id" value="">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="full_name" id="edit_full_name" class="form-input" required>
        </div>
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" id="edit_email" class="form-input" required>
        </div>
        <div class="form-group">
            <label>New Password (Optional)</label>
            <input type="password" name="password" class="form-input" minlength="8" placeholder="Leave blank to keep current password">
        </div>
        <div class="form-group">
            <label>Role</label>
            <select name="role_id" id="edit_role_id" class="form-select" required>
                <?php foreach($roles as $r): ?>
                    <option value="<?= $r['role_id'] ?>"><?= h(ucfirst(strtolower($r['role_name']))) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Account Status</label>
            <select name="account_status" id="edit_account_status" class="form-select" required>
                <option value="ACTIVE">Active</option>
                <option value="DISABLED">Disabled</option>
                <option value="SUSPENDED">Suspended</option>
            </select>
        </div>
        <button type="submit" class="btn-submit">Update User</button>
      </form>
    </div>
  </div>

  <script>
    lucide.createIcons();

    function openModal(id) {
        document.getElementById(id).classList.add('active');
    }
    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
    }
    
    function openEditModal(id, name, email, roleId, accountStatus) {
        document.getElementById('edit_user_id').value = id;
        document.getElementById('edit_full_name').value = name;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_role_id').value = roleId;
        document.getElementById('edit_account_status').value = accountStatus;
        openModal('editModal');
    }
  </script>
<!-- Custom Confirm Modal Setup -->
<style>
.custom-confirm-overlay {
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(15, 23, 42, 0.5); backdrop-filter: blur(4px);
    display: none; align-items: center; justify-content: center; z-index: 999999;
}
.custom-confirm-modal {
    background: #fff; padding: 2rem; border-radius: 16px;
    box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
    max-width: 400px; width: 90%; text-align: center;
    animation: confirmPop 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes confirmPop { from { transform: scale(0.95) translateY(10px); opacity: 0; } to { transform: scale(1) translateY(0); opacity: 1; } }
.custom-confirm-modal h3 { margin: 0 0 1rem; color: #0f172a; font-size: 1.25rem; font-weight: 800; font-family: 'Inter', sans-serif; }
.custom-confirm-modal p { color: #64748b; margin-bottom: 2rem; font-size: 0.95rem; line-height: 1.5; font-family: 'Inter', sans-serif; }
.custom-confirm-actions { display: flex; gap: 1rem; justify-content: center; }
.custom-confirm-btn {
    padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; cursor: pointer; border: none; font-size: 0.95rem; font-family: 'Inter', sans-serif; transition: all 0.2s;
}
.custom-confirm-cancel { background: #f1f5f9; color: #475569; }
.custom-confirm-cancel:hover { background: #e2e8f0; }
.custom-confirm-danger { background: #ef4444; color: #fff; }
.custom-confirm-danger:hover { background: #dc2626; transform: translateY(-1px); }
</style>
<div class="custom-confirm-overlay" id="customConfirmOverlay">
    <div class="custom-confirm-modal">
        <h3 id="customConfirmTitle">Are you sure?</h3>
        <p id="customConfirmMessage">This action cannot be undone.</p>
        <div class="custom-confirm-actions">
            <button class="custom-confirm-btn custom-confirm-cancel" id="customConfirmCancel">Cancel</button>
            <button class="custom-confirm-btn custom-confirm-danger" id="customConfirmOk">Yes, I'm sure</button>
        </div>
    </div>
</div>
<script>
let pendingConfirmAction = null;
function initCustomConfirm(el, message, e) {
    if (e) e.preventDefault();
    document.getElementById('customConfirmMessage').innerText = message;
    
    // Auto title based on message
    let title = "Are you sure?";
    if(message.toLowerCase().includes('delete')) title = "Confirm Deletion";
    else if(message.toLowerCase().includes('remove')) title = "Confirm Removal";
    document.getElementById('customConfirmTitle').innerText = title;

    document.getElementById('customConfirmOverlay').style.display = 'flex';
    
    pendingConfirmAction = () => {
        if (el.tagName === 'FORM') {
            // Remove the onsubmit handler so it doesn't trigger again, then submit
            el.onsubmit = null;
            el.submit();
        } else if (el.tagName === 'BUTTON' || el.tagName === 'A') {
            if(el.form) {
                el.form.onsubmit = null;
                el.form.submit();
            } else {
                // If there's an href, navigate
                if(el.href && el.href !== '#' && !el.href.startsWith('javascript:')) {
                    window.location.href = el.href;
                }
            }
        }
    };
    return false;
}
document.getElementById('customConfirmCancel').addEventListener('click', () => {
    document.getElementById('customConfirmOverlay').style.display = 'none';
    pendingConfirmAction = null;
});
document.getElementById('customConfirmOk').addEventListener('click', () => {
    document.getElementById('customConfirmOverlay').style.display = 'none';
    if(pendingConfirmAction) pendingConfirmAction();
});
</script>
</body>
</html>
