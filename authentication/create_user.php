<?php
// retrieve inputs
$username = filter_input(INPUT_POST, "register_username", FILTER_SANITIZE_SPECIAL_CHARS);
$email = filter_input(INPUT_POST, "register_email", FILTER_SANITIZE_SPECIAL_CHARS);
$password = filter_input(INPUT_POST, "register_password", FILTER_SANITIZE_SPECIAL_CHARS);
$confirm_password = filter_input(INPUT_POST, "register_confirm_password", FILTER_SANITIZE_SPECIAL_CHARS);

// validation
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

// password validation
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

// ensure passwords match
if (!$confirm_password) {
    $errors[] = "Please confirm your password.";
} elseif ($password !== $confirm_password) {
    $errors[] = "Passwords do not match.";
}

// display errors
if (!empty($errors)) {
    foreach ($errors as $error) {
        echo "<p>$error</p>";
    }
    exit;
}

// db vars
$dbname = "science_snap";
$db_username = "root";
$db_password = "";

// connect to db
try {
    $dbh = new PDO("mysql:host=localhost;dbname=$dbname;charset=utf8", $db_username, $db_password);
} catch (Exception $e) {
    die("ERROR: Couldn't connect. {$e->getMessage()}");
}

// check if email is already used
$check_email_sql = "SELECT COUNT(*) FROM `users` WHERE `email`=?;";
$stmt = $dbh->prepare($check_email_sql);
$stmt->execute([$email]);
$email_count = $stmt->fetchColumn();

if ($email_count > 0) {
    echo "<p>An account with this email already exists</p>";
    exit;
}

// check if username is already users
$check_username_sql = "SELECT COUNT(*) FROM `users` WHERE `username`=?;";
$stmt = $dbh->prepare($check_username_sql);
$stmt->execute([$username]);
$username_count = $stmt->fetchColumn();

if ($username_count > 0) {
    echo "<p>An account with this username already exists</p>";
    exit;
}

// create new user row
$password_hash = password_hash($password, PASSWORD_DEFAULT);

$create_user_sql = "INSERT INTO `users` (`username`, `email`, `password_hash`) VALUES (?, ?, ?);";
$create_user_args = [$username, $email, $password_hash];
$stmt = $dbh->prepare($create_user_sql);
$success = $stmt->execute($create_user_args);

if ($success) {
    echo "<p>New user created</p>";
} else {
    echo "<p>Something went wrong. Please try again</p>";
}

?>