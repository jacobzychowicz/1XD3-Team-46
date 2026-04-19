<?php

session_start();

// get form input
$username = filter_input(INPUT_POST, 'register_username', FILTER_SANITIZE_SPECIAL_CHARS);
$email = filter_input(INPUT_POST, 'register_email', FILTER_SANITIZE_SPECIAL_CHARS);
$password = filter_input(INPUT_POST, 'register_password', FILTER_SANITIZE_SPECIAL_CHARS);
$confirm_password = filter_input(INPUT_POST, 'register_confirm_password', FILTER_SANITIZE_SPECIAL_CHARS);

$errors = [];

// username validation
if (!$username) {
    $errors[] = "Username is required.";
} elseif (strlen($username) < 3 || strlen($username) > 50) {
    $errors[] = "Username must be between 3 and 50 characters.";
}

// email validation
if (!$email) {
    $errors[] = "Email is required.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format.";
}

// password validation.
if (!$password) {
    $errors[] = "Password is required.";
} elseif (strlen($password) < 8) {
    $errors[] = "Password must be at least 8 characters.";
} elseif (!preg_match('/[A-Z]/', $password)) {
    $errors[] = "Password must contain at least one uppercase letter.";
} elseif (!preg_match('/[a-z]/', $password)) {
    $errors[] = "Password must contain at least one lowercase letter.";
} elseif (!preg_match('/[0-9]/', $password)) {
    $errors[] = "Password must contain at least one number.";
}

// validate password confirmation
if (!$confirm_password) {
    $errors[] = "Please confirm your password.";
} elseif ($password !== $confirm_password) {
    $errors[] = "Passwords do not match.";
}

// Return the user to the login page if validation fails.
if (!empty($errors)) {
    $_SESSION['register_feedback'] = [
        'type' => 'error',
        // put array of errors into single string
        'message' => implode(' ', $errors)
    ];
    header('Location: login.php');
    exit;
}

// Include centralized database config
require_once __DIR__ . '/../config/db.php';

// connect to db
try {
    $dbh = getDBConnection();
} catch (Exception $e) {
    die("ERROR: Couldn't connect. {$e->getMessage()}");
}

// check if email in use
$check_email_sql = 'SELECT COUNT(*) FROM `users` WHERE `email`=?;';
$stmt = $dbh->prepare($check_email_sql);
$stmt->execute([$email]);
$email_count = $stmt->fetchColumn();

if ($email_count > 0) {
    $_SESSION['register_feedback'] = [
        'type' => 'error',
        'message' => 'An account with this email already exists.'
    ];
    header('Location: login.php');
    exit;
}

// check if username is in use
$check_username_sql = 'SELECT COUNT(*) FROM `users` WHERE `username`=?;';
$stmt = $dbh->prepare($check_username_sql);
$stmt->execute([$username]);
$username_count = $stmt->fetchColumn();

if ($username_count > 0) {
    $_SESSION['register_feedback'] = [
        'type' => 'error',
        'message' => 'An account with this username already exists.'
    ];
    header('Location: login.php');
    exit;
}

// hash password
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// insert new user into db
$create_user_sql = 'INSERT INTO `users` (`username`, `email`, `password_hash`) VALUES (?, ?, ?);';
$create_user_args = [$username, $email, $password_hash];
$stmt = $dbh->prepare($create_user_sql);
$success = $stmt->execute($create_user_args);

if ($success) {
    session_regenerate_id(true);

    // store user info in session
    $_SESSION['user_id'] = $dbh->lastInsertId();
    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;

    // redirect to posts page
    header('Location: ../posts/post.php');
    exit;
} else {
    $_SESSION['register_feedback'] = [
        'type' => 'error',
        'message' => 'Something went wrong. Please try again.'
    ];
    header('Location: login.php');
    exit;
}

?>