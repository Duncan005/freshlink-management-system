<?php 
require_once __DIR__ . '/../../includes/admin_header.php';

// Process role update
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && isset($_POST['role'])) {
    $user_id = (int) $_POST['user_id'];
    $role = clean_input($_POST['role']);
    
    if (update_user_role($user_id, $role)) {
        $success = 'User role updated successfully';
    } else {
        $error = 'Failed to update user role';
    }
}

// Pagination
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 10;
$users = get_all_users($page, $limit);

// Get total users for pagination
$total_users = get_users_count();
$total_pages = ceil($total_users / $limit);
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-green-700">Manage Users</h1>
    </div>
    
    <?php if ($success): ?>
        <?= display_success($success) ?>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <?= display_error($error) ?>
    <?php endif; ?>
    
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-100">
                <tr>
                    <th class="py-2 px-4 text-left">ID</th>
                    <th class="py-2 px-4 text-left">Username</th>
                    <th class="py-2 px-4 text-left">Email</th>
                    <th class="py-2 px-4 text-left">Role</th>
                    <th class="py-2 px-4 text-left">Created</th>
                    <th class="py-2 px-4 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr class="border-t">
                        <td class="py-2 px-4"><?= $user['id'] ?></td>
                        <td class="py-2 px-4"><?= htmlspecialchars($user['username']) ?></td>
                        <td class="py-2 px-4"><?= htmlspecialchars($user['email']) ?></td>
                        <td class="py-2 px-4">
                            <span class="px-2 py-1 rounded text-xs 
                                <?php 
                                switch ($user['role']) {
                                    case 'admin': echo 'bg-red-100 text-red-800'; break;
                                    case 'seller': echo 'bg-blue-100 text-blue-800'; break;
                                    default: echo 'bg-gray-100 text-gray-800';
                                }
                                ?>">
                                <?= ucfirst($user['role']) ?>
                            </span>
                        </td>
                        <td class="py-2 px-4"><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                        <td class="py-2 px-4">
                            <div class="flex space-x-2">
                                <form method="POST" action="" class="flex items-center space-x-2">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <select name="role" class="border rounded px-2 py-1 text-sm">
                                        <option value="customer" <?= $user['role'] === 'customer' ? 'selected' : '' ?>>Customer</option>
                                        <option value="seller" <?= $user['role'] === 'seller' ? 'selected' : '' ?>>Seller</option>
                                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                    </select>
                                    <button type="submit" class="bg-blue-500 text-white px-2 py-1 rounded text-sm">Update</button>
                                </form>
                                <a href="<?= get_correct_path('admin/user_details.php') ?>?user_id=<?= $user['id'] ?>" class="bg-green-500 text-white px-2 py-1 rounded text-sm">Details</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <?php if ($total_pages > 1): ?>
        <div class="mt-6 flex justify-center">
            <div class="flex space-x-2">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">Previous</a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?= $i ?>" class="px-3 py-1 <?= $i === $page ? 'bg-green-500 text-white' : 'bg-gray-200 hover:bg-gray-300' ?> rounded"><?= $i ?></a>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1 ?>" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">Next</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>