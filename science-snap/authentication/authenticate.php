<?php
session_start();

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
    $_SESSION['login_feedback'] = [
        'type' => 'error',
        'message' => implode(' ', $errors)
    ];
    header("Location: login.php");
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
    $_SESSION['login_feedback'] = [
        'type' => 'error',
        'message' => 'No account found with that email.'
    ];
    header("Location: login.php");
    exit;
}

// check password
if (!password_verify($password, $user['password_hash'])) {
    $_SESSION['login_feedback'] = [
        'type' => 'error',
        'message' => 'Incorrect password.'
    ];
    header("Location: login.php");
    exit;
}

$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['email'] = $user['email'];

header("Location: ../posts/post.php");
exit;

?>
