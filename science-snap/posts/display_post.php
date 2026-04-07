<?php

session_start();

// handle logout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
  $_SESSION = [];
  session_destroy();
  header('Location: ../authentication/login.php');
  exit;
}

// PDO values
$dbname = 'zychowj_db';
$db_username = 'zychowj_local';
$db_password = '10UT8Z{P';

// get user info
$user_name = $_SESSION['username'] ?? null;
$current_user_id = $_SESSION['user_id'] ?? null;
$is_logged_in = $current_user_id !== null;

// get post id from URL
$post_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// store post and error info
$post = null;
$error_message = null;

// get post feedback from session and remove it
$post_feedback = $_SESSION['post_feedback'] ?? null;
unset($_SESSION['post_feedback']);

// get comment feedback from session and remove it
$comment_feedback = $_SESSION['comment_feedback'] ?? null;
unset($_SESSION['comment_feedback']);

// will hold the comments tree for this post
$comments_by_parent = [];

try {
  // connect to database using PDO
  $pdo = new PDO("mysql:host=localhost;dbname=$dbname;charset=utf8", $username, $password);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // fetch the single post by id, join with users to get the author username
  // also flag if the current logged in user owns this post
  $stmt = $pdo->prepare(
    'SELECT posts.*, users.username,
        CASE WHEN posts.user_id = :current_user_id THEN 1 ELSE 0 END AS is_owner
     FROM posts
     LEFT JOIN users ON posts.user_id = users.id
     WHERE posts.id = :post_id'
  );

  //fetch the isngle post row
  $stmt->execute([
    ':current_user_id' => $current_user_id ?? 0,
    ':post_id' => $post_id,
  ]);

  $post = $stmt->fetch(PDO::FETCH_ASSOC);

  // if no post was found set error message
  if (!$post) {
    $error_message = 'Post not found.';
  } else {
    require_once __DIR__ . '/../comments/comments.php';
    $comments_by_parent = comments_load_tree($pdo, (int) $post_id);
  }
} catch (PDOException $e) {
  $error_message = 'Unable to load the post.';
}

// build links
$posts_link = 'post.php';
$create_post_link = 'add_post.php';
$login_link = '../authentication/login.php';
$logout_action = 'display_post.php?id=' . urlencode((string) $post_id);
$edit_action = 'edit_post.php';
$delete_action = 'delete_post.php';
?>

<!doctype html>
<html>
  <head>
  <meta charset="utf-8" />
  <link rel="stylesheet" href="../css/common.css">
  <link rel="stylesheet" href="../css/posts.css">
  <link rel="stylesheet" href="../css/comments.css">
   <!-- comments.js handles loading comments dynamically via AJAX -->
  <script src="../comments/comments.js" defer></script>
  <title>View Post</title>
  </head>
  <body>
    <header class="site-header">
      <h1 class="site-title">Science Snap</h1>
      <div class="site-nav">
        <?php if ($user_name): ?>
        <span class="current-user">Current User: <?php echo htmlspecialchars($user_name); ?></span>
        <?php endif; ?>
        <a
          href="<?php echo htmlspecialchars($posts_link); ?>"
          class="nav-link nav-link-primary"
          >Posts</a
        >
        <?php if ($is_logged_in): ?>
        <a
          href="<?php echo htmlspecialchars($create_post_link); ?>"
          class="nav-link nav-link-secondary"
          >Create Post</a
        >
        <form action="<?php echo htmlspecialchars($logout_action); ?>" method="post" class="logout-form">
          <input type="hidden" name="logout" value="1" />
          <button type="submit" class="logout-button">Logout</button>
        </form>
        <?php else: ?>
        <a
          href="<?php echo htmlspecialchars($login_link); ?>"
          class="nav-link nav-link-secondary"
          >Login</a
        >
        <?php endif; ?>
      </div>
    </header>

    <main class="page-main">
      <!-- go back to view all posts -->
      <a href="<?php echo htmlspecialchars($posts_link); ?>" class="post-back-link">&larr; Back to Posts</a>

      <!-- show edit or delete feedback -->
      <?php if ($post_feedback): ?>
      <p class="page-feedback <?php echo $post_feedback['type'] === 'error' ? 'feedback-error' : 'feedback-success'; ?>">
        <?php echo htmlspecialchars($post_feedback['message']); ?>
      </p>
      <?php endif; ?>

      <!-- catch non existent post -->
      <?php if ($error_message): ?>
      <section class="notice-card">
        <h2 class="section-title">Unable to Display Post</h2>
        <p class="notice-card-text"><?php echo htmlspecialchars($error_message); ?></p>
      </section>
      <?php else: ?>
      <article class="post-article">
        <h2 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h2>
        <p class="post-description"><?php echo htmlspecialchars($post['description']); ?></p>

        <small>
          ID: <?php echo htmlspecialchars((string) $post['id']); ?> |
          Posted by: <?php echo htmlspecialchars($post['username'] ?? 'Unknown'); ?> |
          Posted on: <?php echo htmlspecialchars($post['created_at']); ?>
        </small>

        <!-- only show edit and delete if the current user owns this post -->
        <?php if (!empty($post['is_owner'])): ?>
        <!-- edit post from -->
        <form action="<?php echo htmlspecialchars($edit_action); ?>" method="post" class="post-edit-form">
          <input type="hidden" name="id" value="<?php echo htmlspecialchars((string) $post['id']); ?>" />
          <h3 class="section-title">Edit Post</h3>
          <label for="editName">Title</label><br />
          <input id="editName" name="title" type="text" value="<?php echo htmlspecialchars($post['title']); ?>" class="post-edit-input" />
          <br /><br />
          <label for="editDesc">Description</label><br />
          <textarea id="editDesc" name="description" class="post-textarea"><?php echo htmlspecialchars($post['description']); ?></textarea>
          <br /><br />
          <div class="form-actions">
            <button type="submit">Save Changes</button>
            <a href="<?php echo htmlspecialchars($posts_link); ?>" class="post-cancel-link">Cancel</a>
          </div>
        </form>

        <!-- delete post form -->
        <form action="<?php echo htmlspecialchars($delete_action); ?>" method="post" class="delete-form">
          <input type="hidden" name="id" value="<?php echo htmlspecialchars((string) $post['id']); ?>" />
          <button type="submit" class="delete-button">Delete Post</button>
        </form>
        <?php endif; ?>
      </article>

      <!-- load the comments section template -->
      <?php include __DIR__ . '/../comments/comments_section.php'; ?>
      <?php endif; ?>
    </main>
  </body>
</html>