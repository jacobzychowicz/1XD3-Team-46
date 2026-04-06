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

//build url params
$session_query = $session_name . '=' . urlencode(session_id());

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
$posts_link = 'posts/post.php?' . $session_query;
$login_link = 'authentication/login.php?' . $session_query;
$logout_action = 'index.php?' . $session_query;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/home.css">
  <title>Science Snap</title>
</head>
<body class="home-body">
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
    
    <div id="title" class="home-hero">
        <h1>Science Snap</h1>
        <h2>Research in a Snap</h2>
        <h3>Zak Yarwood</h3>
    </div>
</body>
</html>