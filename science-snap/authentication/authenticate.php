<?php

// get session name
$session_name = session_name();

// get session id
if (isset($_GET[$session_name]) && $_GET[$session_name] !== '') {
    session_id($_GET[$session_name]);
} elseif (isset($_POST[$session_name]) && $_POST[$session_name] !== '') {
    session_id($_POST[$session_name]);
}

session_start();

// build url params
$session_query = $session_name . '=' . urlencode(session_id());

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
    header('Location: login.php?' . $session_query);
    exit;
}

// PDO values
$dbname = 'science_snap';
$db_username = 'root';
$db_password = '';

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
    header('Location: login.php?' . $session_query);
    exit;
}

// check password
if (!password_verify($password, $user['password_hash'])) {
    $_SESSION['login_feedback'] = [
        'type' => 'error',
        'message' => 'Incorrect password.'
    ];
    header('Location: login.php?' . $session_query);
    exit;
}

// regenerate session
session_regenerate_id(true);

// new session id
$session_query = $session_name . '=' . urlencode(session_id());

// store user info in session
$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['email'] = $user['email'];

// redirect to posts
header('Location: ../posts/post.php?' . $session_query);
exit;

?>
