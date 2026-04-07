<?php 

session_start();

// PDO values
$dbname = 'zychowj_db';
$db_username = 'zychowj_local';
$db_password = '10UT8Z{P';

// get user and post id
$user_id = $_SESSION['user_id'] ?? null;
$post_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

// redirect to login if use not logged in (shouldn't happen but just in case)
if (!$user_id) {
    $_SESSION['post_feedback'] = [
        'type' => 'error',
        'message' => 'You must be logged in to delete posts.'
    ];
    header('Location: ../authentication/login.php');
    exit;
}

// redirect if no post id
if (!$post_id) {
    $_SESSION['post_feedback'] = [
        'type' => 'error',
        'message' => 'Post ID is required.'
    ];
    header('Location: post.php');
    exit;
}

// delete the post
try {
    $pdo = new PDO("mysql:host=localhost;dbname=$dbname;charset=utf8", $username, $password);

    $stmt = $pdo->prepare('DELETE FROM posts WHERE id = :id AND user_id = :user_id');
    $stmt->execute([
        ':id' => $post_id,
        ':user_id' => $user_id
    ]);

    // catch user deleting another users posts just in case
    if ($stmt->rowCount() === 0) {
        $_SESSION['post_feedback'] = [
            'type' => 'error',
            'message' => 'You can only delete your own posts.'
        ];
        header('Location: display_post.php?id=' . urlencode((string) $post_id));
        exit;
    }

    $_SESSION['post_feedback'] = [
        'type' => 'success',
        'message' => 'Post deleted.'
    ];
} catch (PDOException $e) {
    $_SESSION['post_feedback'] = [
        'type' => 'error',
        'message' => 'Unable to delete post.'
    ];
    header('Location: display_post.php?id=' . urlencode((string) $post_id));
    exit;
}

// return back to posts index if post deletes
header('Location: post.php');
exit;