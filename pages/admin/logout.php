<?php
require_once __DIR__ . '/../../includes/header.php';

// Logout the user
logout_user();

// Redirect to login page
redirect('../login.php');
?>