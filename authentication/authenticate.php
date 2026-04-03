<?php
$email = filter_input(INPUT_POST, "login_email", FILTER_SANITIZE_SPECIAL_CHARS);
$password = filter_input(INPUT_POST, "login_password");

$errors = [];

if (!$email) {
    $errors[] = "Email is required.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email.";
}

if (!$password) {
    $errors[] = "Password is required.";
}

if (!empty($errors)) {
    foreach ($errors as $e) echo "<p>$e</p>";
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

// get user info
$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $dbh->prepare($sql);
$stmt->execute([$email]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

// check if user exists
if (!$user) {
    echo "<p>No account found with that email.</p>";
    exit;
}

// check password
if (!password_verify($password, $user['password_hash'])) {
    echo "<p>Incorrect password.</p>";
    exit;
}

// start user's session
session_start();

$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['email'] = $user['email'];



?>
