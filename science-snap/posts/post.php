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

// build query string for url param
$session_query = $session_name . '=' . urlencode(session_id());

// handle logout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    $_SESSION = [];
    session_destroy();
    header('Location: ../authentication/login.php');
    exit;
}

// get user info from session
$user_name = $_SESSION['username'] ?? null;
$current_user_id = $_SESSION['user_id'] ?? null;
$is_logged_in = $current_user_id !== null;

// pdo vals
$host = 'localhost';
$dbname = 'science_snap';
$username = 'root';
$password = '';

// get post feedback and remove it from session
$post_feedback = $_SESSION['post_feedback'] ?? null;
unset($_SESSION['post_feedback']);

$posts = [];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);

    // get all posts with author username and a flag indicating if the current user owns each post
    $stmt = $pdo->prepare(
        'SELECT posts.*, users.username,
                CASE WHEN posts.user_id = :current_user_id THEN 1 ELSE 0 END AS is_owner
         FROM posts
         LEFT JOIN users ON posts.user_id = users.id
         ORDER BY posts.created_at DESC'
    );
    $stmt->execute([':current_user_id' => $current_user_id ?? 0]);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $post_feedback = [
        'type' => 'error',
        'message' => 'Unable to load posts.'
    ];
}

// build links
$posts_link = 'post.php?' . $session_query;
$create_post_link = 'add_post.php?' . $session_query;
$login_link = '../authentication/login.php?' . $session_query;
$logout_action = 'post.php?' . $session_query;
?>

<!doctype html>

<html>
  <head>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="../css/common.css">
    <link rel="stylesheet" href="../css/posts.css">
    <title>Posts</title>
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
        <?php endif; ?>
        <?php if ($is_logged_in): ?>
          <form action="<?php echo htmlspecialchars($logout_action); ?>" method="post" class="logout-form">
            <input type="hidden" name="<?php echo htmlspecialchars($session_name); ?>" value="<?php echo htmlspecialchars(session_id()); ?>" />
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

    <!-- show create post link if user is logged in -->
    <?php if ($is_logged_in): ?>
    <div class="create-banner">
      <span>Create a new post</span>
      <a
        href="<?php echo htmlspecialchars($create_post_link); ?>"
        class="banner-action"
      >Create Post</a>
    </div>
    <?php else: ?>
    <div class="info-banner">
      Log in to create posts and edit your own posts.
    </div>
    <?php endif; ?>

    <!-- error messages from posts -->
    <?php if ($post_feedback): ?>
    <p class="post-feedback <?php echo $post_feedback['type'] === 'error' ? 'feedback-error' : 'feedback-success'; ?>">
      <?php echo htmlspecialchars($post_feedback['message']); ?>
    </p>
    <?php endif; ?>

    <!-- render all posts -->
    <div id="postContainer">
      <?php foreach ($posts as $post): ?>
      <?php $post_link = 'display_post.php?id=' . urlencode((string) $post['id']) . '&' . $session_query; ?>
      <div class="post-list-item">
        <h3 class="post-list-title">
          <a href="<?php echo htmlspecialchars($post_link); ?>">
            <?php echo htmlspecialchars($post['title']); ?>
          </a>
        </h3>
        <p><?php echo nl2br(htmlspecialchars($post['description'])); ?></p>
        <a href="<?php echo htmlspecialchars($post_link); ?>">View Post</a>
        <br>
        <small>
          ID: <?php echo htmlspecialchars((string) $post['id']); ?> |
          Posted by: <?php echo htmlspecialchars($post['username'] ?? 'Unknown'); ?> |
          Posted on: <?php echo htmlspecialchars($post['created_at']); ?>
        </small>
      </div>
      <?php endforeach; ?>
    </div>
  </body>
</html>
