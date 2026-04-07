<?php

session_start();

// get form data
$email = filter_input(INPUT_POST, 'login_email', FILTER_SANITIZE_SPECIAL_CHARS);
$password = filter_input(INPUT_POST, 'login_password');

$errors = [];

// email validation
if (!$email) {
    $errors[] = "Email is required.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email.";
}

// password validation
if (!$password) {
    $errors[] = "Password is required.";
}

// store login feedback in session and redirect to login
if (!empty($errors)) {
    $_SESSION['login_feedback'] = [
        'type' => 'error',
        'message' => implode(' ', $errors)
    ];
    header('Location: login.php');
    exit;
}

// PDO values
$dbname = 'zychowj_db';
$db_username = 'zychowj_local';
$db_password = '10UT8Z{P';

// connect to db
try {
    $dbh = new PDO("mysql:host=localhost;dbname=$dbname;charset=utf8", $db_username, $db_password);
} catch (Exception $e) {
    die("ERROR: Couldn't connect. {$e->getMessage()}");
}

// check if account exists
$sql = 'SELECT * FROM users WHERE email = ?';
$stmt = $dbh->prepare($sql);
$stmt->execute([$email]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Stop if the account does not exist.
if (!$user) {
    $_SESSION['login_feedback'] = [
        'type' => 'error',
        'message' => 'No account found with that email.'
    ];
    header('Location: login.php');
    exit;
}

// check password
if (!password_verify($password, $user['password_hash'])) {
    $_SESSION['login_feedback'] = [
        'type' => 'error',
        'message' => 'Incorrect password.'
    ];
    header('Location: login.php');
    exit;
}

// regenerate session
session_regenerate_id(true);

// store user info in session
$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['email'] = $user['email'];

// redirect to posts
header('Location: ../posts/post.php');
exit;

?>
