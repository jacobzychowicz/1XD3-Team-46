<?php
/**
 * Edgar, Jamie, Noah, Jacob
 * Date Created: 2026-03-28
 * Description: Handles post editing - updates post title and description in database
 */

// start session
session_start();

// Include centralized database config
require_once __DIR__ . '/../config/db.php';

// get user and post info
$user_id = $_SESSION['user_id'] ?? null;
$post_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

// redirect to the login page when the user is not signed in.
if (!$user_id) {
    $_SESSION['post_feedback'] = [
        'type' => 'error',
        'message' => 'You must be logged in to edit posts.'
    ];
    header('Location: ../authentication/login.php');
    exit;
}

// redirect back when the post id is missing.
if (!$post_id) {
    $_SESSION['post_feedback'] = [
        'type' => 'error',
        'message' => 'Post ID is required.'
    ];
    header('Location: post.php');
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
    header('Location: display_post.php?id=' . urlencode((string) $post_id));
    exit;
}

// update post
try {
    $pdo = getDBConnection();
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
header('Location: display_post.php?id=' . urlencode((string) $post_id));
exit;