<?php
// admin/categories.php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$user = $_SESSION['user'];
$action = $_GET['action'] ?? 'list';
$message = '';
$error = '';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action_type = $_POST['action_type'] ?? '';
    
    if ($action_type === 'add') {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        if (!$title) {
            $error = 'Category title is required.';
        } else {
            try {
                $stmt = $pdo->prepare('INSERT INTO categories (title, description, is_active, created_by) VALUES (?, ?, ?, ?)');
                $stmt->execute([$title, $description, $is_active, $user['id']]);
                $message = 'Category added successfully.';
                $action = 'list';
            } catch (Exception $e) {
                $error = 'Error adding category: ' . $e->getMessage();
            }
        }
    } elseif ($action_type === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        if (!$id) {
            $error = 'Invalid category ID.';
        } elseif (!$title) {
            $error = 'Category title is required.';
        } else {
            try {
                $stmt = $pdo->prepare('UPDATE categories SET title=?, description=?, is_active=?, modified_by=?, modified_on=NOW() WHERE id=?');
                $stmt->execute([$title, $description, $is_active, $user['id'], $id]);
                $message = 'Category updated successfully.';
                $action = 'list';
            } catch (Exception $e) {
                $error = 'Error updating category: ' . $e->getMessage();
            }
        }
    } elseif ($action_type === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        
        if (!$id) {
            $error = 'Invalid category ID.';
        } else {
            try {
                // Check if category is used by any webinars
                $check = $pdo->prepare('SELECT COUNT(*) FROM webinars WHERE category_id=?');
                $check->execute([$id]);
                if ((int)$check->fetchColumn() > 0) {
                    $error = 'Cannot delete category that is in use by webinars.';
                } else {
                    $stmt = $pdo->prepare('DELETE FROM categories WHERE id=?');
                    $stmt->execute([$id]);
                    $message = 'Category deleted successfully.';
                    $action = 'list';
                }
            } catch (Exception $e) {
                $error = 'Error deleting category: ' . $e->getMessage();
            }
        }
    }
}

// Fetch data based on action
$category = null;
if ($action === 'edit') {
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $pdo->prepare('SELECT * FROM categories WHERE id=?');
    $stmt->execute([$id]);
    $category = $stmt->fetch();
    if (!$category) {
        $error = 'Category not found.';
        $action = 'list';
    }
}

$categories = [];
if ($action === 'list') {
    $stmt = $pdo->query('SELECT * FROM categories ORDER BY created_on DESC');
    $categories = $stmt->fetchAll();
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Categories Management - PIDE Webinar Portal</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="../assets/css/PIDETheme.css" rel="stylesheet">
  <link href="../assets/css/styles.css" rel="stylesheet">
  <style>
    .cat-shell { max-width: 1000px; margin: 20px auto; }
    .action-btn { padding: 4px 8px; font-size: 12px; }
  </style>
</head>
<body>
<?php include __DIR__ . '/../public/navbar.php'; ?>

<div class="cat-shell">
  <?php if ($message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?= htmlspecialchars($message) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
  
  <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?= htmlspecialchars($error) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php if ($action === 'list'): ?>
    <!-- List View -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4><i class="bi bi-list"></i> Categories Management</h4>
      <a href="categories.php?action=add" class="btn btn-success"><i class="bi bi-plus-circle"></i> Add Category</a>
    </div>

    <div class="table-responsive">
      <table class="table table-hover">
        <thead class="table-light">
          <tr>
            <th>Title</th>
            <th>Description</th>
            <th>Status</th>
            <th>Created By</th>
            <th>Created On</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($categories)): ?>
            <tr>
              <td colspan="6" class="text-center text-muted">No categories found.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($categories as $cat): ?>
              <tr>
                <td><?= htmlspecialchars($cat['title']) ?></td>
                <td><?= htmlspecialchars(substr($cat['description'] ?? '', 0, 50)) ?><?= strlen($cat['description'] ?? '') > 50 ? '...' : '' ?></td>
                <td>
                  <span class="badge <?= $cat['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                    <?= $cat['is_active'] ? 'Active' : 'Inactive' ?>
                  </span>
                </td>
                <td><?= htmlspecialchars($cat['created_by']) ?></td>
                <td><?= date('M d, Y', strtotime($cat['created_on'])) ?></td>
                <td>
                  <a href="categories.php?action=edit&id=<?= $cat['id'] ?>" class="btn btn-primary action-btn"><i class="bi bi-pencil"></i> Edit</a>
                  <button class="btn btn-danger action-btn" onclick="confirmDelete(<?= $cat['id'] ?>)"><i class="bi bi-trash"></i> Delete</button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  <?php elseif ($action === 'add' || $action === 'edit'): ?>
    <!-- Add/Edit Form -->
    <div class="card">
      <div class="card-header">
        <h5><?= $action === 'add' ? 'Add New Category' : 'Edit Category' ?></h5>
      </div>
      <div class="card-body">
        <form method="POST">
          <input type="hidden" name="action_type" value="<?= $action === 'add' ? 'add' : 'edit' ?>">
          <?php if ($action === 'edit'): ?>
            <input type="hidden" name="id" value="<?= $category['id'] ?>">
          <?php endif; ?>

          <div class="mb-3">
            <label class="form-label">Category Title <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($category['title'] ?? '') ?>" placeholder="Enter category title" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" rows="4" placeholder="Enter category description"><?= htmlspecialchars($category['description'] ?? '') ?></textarea>
          </div>

          <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" name="is_active" id="is_active" <?= (isset($category) && $category['is_active']) || !isset($category) ? 'checked' : '' ?>>
            <label class="form-check-label" for="is_active">Active</label>
          </div>

          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> <?= $action === 'add' ? 'Add' : 'Update' ?> Category</button>
            <a href="categories.php" class="btn btn-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>

  <?php endif; ?>

  <div class="footer-text mt-5">
    © <?= date('Y') ?> Pakistan Institute of Development Economics (PIDE)
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Confirm Delete</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to delete this category? This action cannot be undone.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <form id="deleteForm" method="POST" style="display:inline;">
          <input type="hidden" name="action_type" value="delete">
          <input type="hidden" name="id" id="deleteId">
          <button type="submit" class="btn btn-danger">Delete</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function confirmDelete(id) {
  document.getElementById('deleteId').value = id;
  const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
  modal.show();
}
</script>
</body>
</html>
