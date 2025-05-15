<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

// Register a new user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'register') {
    // Get JSON data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['username']) || !isset($data['email']) || !isset($data['password'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
        exit;
    }
    
    $username = clean_input($data['username']);
    $email = clean_input($data['email']);
    $password = $data['password'];
    $role = isset($data['role']) && in_array($data['role'], ['customer', 'seller', 'admin']) ? $data['role'] : 'customer';
    
    try {
        if (register_user($username, $email, $password, $role)) {
            echo json_encode([
                'status' => 'success', 
                'message' => 'User registered successfully'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to register user']);
        }
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            http_response_code(409);
            echo json_encode(['status' => 'error', 'message' => 'Email or username already exists']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Registration failed']);
        }
    }
}

// Login a user
else if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'login') {
    // Get JSON data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['email']) || !isset($data['password'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
        exit;
    }
    
    $email = clean_input($data['email']);
    $password = $data['password'];
    
    session_start();
    
    if (login_user($email, $password)) {
        echo json_encode([
            'status' => 'success', 
            'message' => 'Login successful',
            'user' => [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'email' => $_SESSION['email'],
                'role' => $_SESSION['role']
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Invalid email or password']);
    }
}

// Get current user info (requires authentication)
else if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'current') {
    session_start();
    
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
        exit;
    }
    
    echo json_encode([
        'status' => 'success',
        'user' => [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'email' => $_SESSION['email'],
            'role' => $_SESSION['role']
        ]
    ]);
}

// Logout a user
else if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_start();
    logout_user();
    
    echo json_encode([
        'status' => 'success', 
        'message' => 'Logout successful'
    ]);
}

// Invalid request
else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed or invalid action']);
}
?>