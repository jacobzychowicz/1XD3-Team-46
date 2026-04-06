<?php 

// get session name
$session_name = session_name();

// get session id
if (isset($_GET[$session_name]) && $_GET[$session_name] !== '') {
    session_id($_GET[$session_name]);
} elseif (isset($_POST[$session_name]) && $_POST[$session_name] !== '') {
    session_id($_POST[$session_name]);
}

// start session
session_start();

// build url params
$session_query = $session_name . '=' . urlencode(session_id());

// PDO data
$host = 'localhost';
$dbname = 'science_snap';
$username = 'root';
$password = '';

// get user and post info
$user_id = $_SESSION['user_id'] ?? null;
$post_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

// redirect to the login page when the user is not signed in.
if (!$user_id) {
    $_SESSION['post_feedback'] = [
        'type' => 'error',
        'message' => 'You must be logged in to edit posts.'
    ];
    header('Location: ../authentication/login.php?' . $session_query);
    exit;
}

// redirect back when the post id is missing.
if (!$post_id) {
    $_SESSION['post_feedback'] = [
        'type' => 'error',
        'message' => 'Post ID is required.'
    ];
    header('Location: post.php?' . $session_query);
    exit;
}

// read from values
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');

// validate title
if ($title === '') {
    $_SESSION['post_feedback'] = [
        'type' => 'error',
        'message' => 'Post title is required.'
    ];
    header('Location: display_post.php?id=' . urlencode((string) $post_id) . '&' . $session_query);
    exit;
}

// update post
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare('UPDATE posts SET title = :title, description = :description WHERE id = :id AND user_id = :user_id');
    $stmt->execute([
        ':title' => $title,
        ':description' => $description,
        ':id' => $post_id,
        ':user_id' => $user_id
    ]);

    // catch user editing another user's post
    if ($stmt->rowCount() === 0) {
        $_SESSION['post_feedback'] = [
            'type' => 'error',
            'message' => 'You can only edit your own posts.'
        ];
    } else {
        $_SESSION['post_feedback'] = [
            'type' => 'success',
            'message' => 'Post updated.'
        ];
    }
} catch (PDOException $e) {
    $_SESSION['post_feedback'] = [
        'type' => 'error',
        'message' => 'Unable to update post.'
    ];
}

// redirect to post page
header('Location: display_post.php?id=' . urlencode((string) $post_id) . '&' . $session_query);
exit;