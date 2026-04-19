<?php

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
$home_link = 'index.php';
$posts_link = 'posts/post.php';
$login_link = 'authentication/login.php';
$logout_action = 'about.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/about.css">
    <title>About Me - Science Snap</title>
    <script src="validation/form-validation.js" defer></script>
</head>
<body>
    <header class="site-header">
      <div class="header-container">
        <h1 class="site-title">Science Snap</h1>
        <div class="site-nav">
          <a href="<?php echo htmlspecialchars($home_link); ?>" class="nav-link">Home</a>
          <a href="<?php echo htmlspecialchars($posts_link); ?>" class="nav-link">Posts</a>
          <a href="about.php" class="nav-link">About</a>
          <?php if ($is_logged_in): ?>
          <span class="current-user">Current User: <?php echo htmlspecialchars($user_name); ?></span>
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
    
    <main class="about-main">
        <h1>About Zak Yarwood</h1>
        <div class="about-content">
            <img src="images/zak.jpg" alt="Zak Yarwood" class="about-image">
            <div class="about-text">
                <p>Hello! I'm Zak Yarwood, a first-year undergraduate student majoring in Chemical Physics at McMaster University.</p>
                <p>This website, Science Snap, is my platform to share insights from my research journey. Whether you're a high school student exploring your interests or an undergrad navigating similar paths, I hope my experiences can help you see potential pitfalls, learn from my mistakes, and find your own way in the world of science.</p>
                <p>Feel free to explore the posts where I discuss various topics in research and science.</p>
            </div>
        </div>
    </main>
</body>
</html>