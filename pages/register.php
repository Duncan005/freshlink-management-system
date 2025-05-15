<?php 
require_once __DIR__ . '/../includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean_input($_POST['username']);
    $email = clean_input($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = isset($_POST['role']) ? clean_input($_POST['role']) : 'customer';
    
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        try {
            if (register_user($username, $email, $password, $role)) {
                $success = 'Registration successful! You can now login.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = 'Email or username already exists';
            } else {
                $error = 'Registration failed: ' . $e->getMessage();
            }
        }
    }
}
?>

<div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-md">
    <h1 class="text-2xl font-bold text-green-700 mb-6">Register</h1>
    
    <?php if ($error): ?>
        <?= display_error($error) ?>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <?= display_success($success) ?>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div class="mb-4">
            <label for="username" class="block text-gray-700 mb-2">Username</label>
            <input type="text" id="username" name="username" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-green-500" required>
        </div>
        
        <div class="mb-4">
            <label for="email" class="block text-gray-700 mb-2">Email</label>
            <input type="email" id="email" name="email" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-green-500" required>
        </div>
        
        <div class="mb-4">
            <label for="password" class="block text-gray-700 mb-2">Password</label>
            <input type="password" id="password" name="password" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-green-500" required>
        </div>
        
        <div class="mb-4">
            <label for="confirm_password" class="block text-gray-700 mb-2">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-green-500" required>
        </div>
        
        <div class="mb-6">
            <label class="block text-gray-700 mb-2">Register as</label>
            <div class="flex space-x-4">
                <label class="inline-flex items-center">
                    <input type="radio" name="role" value="customer" class="form-radio text-green-600" checked>
                    <span class="ml-2">Customer</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="radio" name="role" value="seller" class="form-radio text-green-600">
                    <span class="ml-2">Seller</span>
                </label>
            </div>
        </div>
        
        <div class="flex items-center justify-between">
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Register</button>
            <a href="login.php" class="text-green-600 hover:underline">Already have an account?</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>