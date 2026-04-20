<?php
/**
 * Edgar, Jamie, Noah, Jacob
 * Date Created: 2026-03-20
 * Description: Homepage of Science Snap - displays the main landing page with navigation and current user info
 */

session_start();

// handle logout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
  $_SESSION = [];
  session_destroy();
  header('Location: authentication/login.php');
  exit;
}

// read user info
$user_name = $_SESSION['username'] ?? null;
$is_logged_in = isset($_SESSION['user_id']);

// build links
$posts_link = 'posts/post.php';
$login_link = 'authentication/login.php';
$logout_action = 'index.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/home.css">
  <title>Science Snap</title>
  <script src="validation/form-validation.js" defer></script>
</head>
<body class="home-body">
    <header class="site-header">
      <div class="header-container">
        <h1 class="site-title">Science Snap</h1>
        <div class="site-nav">
          <?php if ($user_name): ?>
          <span class="current-user">Current User: <?php echo htmlspecialchars($user_name); ?></span>
          <?php endif; ?>
          <a href="index.php" class="nav-link">Home</a>
          <a href="about.php" class="nav-link">About</a>
          <a
            href="<?php echo htmlspecialchars($posts_link); ?>"
            class="nav-link"
            >Posts</a
          >
          <?php if ($is_logged_in): ?>
          <form action="<?php echo htmlspecialchars($logout_action); ?>" method="post" class="logout-form">
            <input type="hidden" name="logout" value="1" />
            <button type="submit" class="logout-button">Logout</button>
          </form>
          <?php else: ?>
          <a
            href="<?php echo htmlspecialchars($login_link); ?>"
            class="nav-link"
            >Login</a
          >
          <?php endif; ?>
        </div>
      </div>
    </header>
    
    <div id="title" class="home-hero">
        <h1>Science Snap</h1>
        <h2>Research in a Snap</h2>
        <h3>Zak Yarwood</h3>
    </div>
</body>
</html>