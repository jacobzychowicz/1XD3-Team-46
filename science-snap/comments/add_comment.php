<?php

session_start();

// wrong request - go BACK
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['add_comment'])) {
  header('Location: ../posts/post.php');
  exit;
}

// get form + session data
$user_id = $_SESSION['user_id'] ?? null;
$post_id = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);
$content = trim($_POST['comment_content'] ?? '');
$parent_raw = $_POST['parent_comment_id'] ?? '';
$parent_id = null;
if ($parent_raw !== '' && $parent_raw !== null) {
  $parent_id = filter_var($parent_raw, FILTER_VALIDATE_INT);
}

// PDO vals
$dbname = 'zychowj_db';
$db_username = 'zychowj_local';
$db_password = '10UT8Z{P';

// must be logged in
if (!$user_id) {
  $_SESSION['comment_feedback'] = [
    'type' => 'error',
    'message' => 'You must be logged in to comment.',
  ];
  if ($post_id) {
    header('Location: ../posts/display_post.php?id=' . urlencode((string) $post_id));
    exit;
  }
  header('Location: ../authentication/login.php');
  exit;
}

// basic validation
if (!$post_id || $content === '' || $parent_id === false) {
  $_SESSION['comment_feedback'] = [
    'type' => 'error',
    'message' => 'Comment could not be saved.',
  ];
  if ($post_id) {
    header('Location: ../posts/display_post.php?id=' . urlencode((string) $post_id));
    exit;
  }
  header('Location: ../posts/post.php');
  exit;
}

try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $db_username, $db_password);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // check post exists
  $stmt = $pdo->prepare('SELECT id FROM posts WHERE id = :id LIMIT 1');
  $stmt->execute([':id' => $post_id]);
  if (!$stmt->fetchColumn()) {
    $_SESSION['comment_feedback'] = [
      'type' => 'error',
      'message' => 'Post not found.',
    ];
    header('Location: ../posts/post.php');
    exit;
  }

  // if reply, check parent comment exists on this post
  if ($parent_id !== null) {
    $stmt = $pdo->prepare(
      'SELECT id FROM comments WHERE id = :cid AND post_id = :pid LIMIT 1'
    );
    $stmt->execute([':cid' => $parent_id, ':pid' => $post_id]);
    if (!$stmt->fetchColumn()) {
      $_SESSION['comment_feedback'] = [
        'type' => 'error',
        'message' => 'Comment could not be saved.',
      ];
      header('Location: ../posts/display_post.php?id=' . urlencode((string) $post_id));
      exit;
    }
  }

  $stmt = $pdo->prepare(
    'INSERT INTO comments (content, post_id, user_id, parent_comment_id)
     VALUES (:content, :post_id, :user_id, :parent_comment_id)'
  );
  $stmt->execute([
    ':content' => $content,
    ':post_id' => $post_id,
    ':user_id' => $user_id,
    ':parent_comment_id' => $parent_id,
  ]);

  $_SESSION['comment_feedback'] = [
    'type' => 'success',
    'message' => 'Comment added.',
  ];
  header('Location: ../posts/display_post.php?id=' . urlencode((string) $post_id));
  exit;
} catch (PDOException $e) {
  $_SESSION['comment_feedback'] = [
    'type' => 'error',
    'message' => 'Comment could not be saved.',
  ];
  header('Location: ../posts/display_post.php?id=' . urlencode((string) $post_id));
  exit;
}
