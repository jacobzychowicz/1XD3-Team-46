<?php

// get session name
$session_name = session_name();

// get session id based on method used
if (isset($_GET[$session_name]) && $_GET[$session_name] !== '') {
  session_id($_GET[$session_name]);
} elseif (isset($_POST[$session_name]) && $_POST[$session_name] !== '') {
  session_id($_POST[$session_name]);
}

// start session
session_start();

// build url params
$session_query = $session_name . '=' . urlencode(session_id());

// handle logout, redirect to login page
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
  $_SESSION = [];
  session_destroy();
  header('Location: login.php');
  exit;
}

// read all values needed for the page from session
$user_name = $_SESSION['username'] ?? null;
$is_logged_in = isset($_SESSION['user_id']);
$login_feedback = $_SESSION['login_feedback'] ?? null;
$register_feedback = $_SESSION['register_feedback'] ?? null;

// clear feedback
unset($_SESSION['login_feedback'], $_SESSION['register_feedback']);

// build links
$posts_link = '../posts/post.php?' . $session_query;
$login_link = '../authentication/login.php?' . $session_query;
$login_action = 'authenticate.php?' . $session_query;
$register_action = 'create_user.php?' . $session_query;
$logout_action = 'login.php?' . $session_query;
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <link rel="stylesheet" href="../css/common.css">
  <link rel="stylesheet" href="../css/auth.css">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
</head>

<body>
  <header class="site-header">
    <h1 class="site-title">Science Snap</h1>
    <div class="site-nav">
      <!-- display username in header if logged in -->
      <?php if ($user_name): ?>
        <span class="current-user">Current User: <?php echo htmlspecialchars($user_name); ?></span>
      <?php endif; ?>
      <a
        href="<?php echo htmlspecialchars($posts_link); ?>"
        class="nav-link nav-link-primary">Posts</a>
      <!-- if user logged in show logout button -->
      <?php if ($is_logged_in): ?>
        <form action="<?php echo htmlspecialchars($logout_action); ?>" method="post" class="logout-form">
        <!-- hidden input to send session info to logout -->
          <input type="hidden" name="<?php echo htmlspecialchars($session_name); ?>" value="<?php echo htmlspecialchars(session_id()); ?>" />
          <input type="hidden" name="logout" value="1" />
          <button type="submit" class="logout-button">Logout</button>
        </form>
        <!-- else show login button -->
      <?php else: ?>
        <a
          href="<?php echo htmlspecialchars($login_link); ?>"
          class="nav-link nav-link-secondary">Login</a>
      <?php endif; ?>
    </div>
  </header>

  <!-- login form -->
  <main class="auth-main">
    <section class="auth-section">
      <h2>Login</h2>
      <form action="<?php echo htmlspecialchars($login_action); ?>" method="post">
        <!--hidden session info input -->
        <input type="hidden" name="<?php echo htmlspecialchars($session_name); ?>" value="<?php echo htmlspecialchars(session_id()); ?>" />
        <div>
          <label for="login-email">Email</label><br>
          <input type="email" id="login-email" name="login_email" maxlength="255" autocomplete="email" required>
        </div>
        <div>
          <label for="login-password">Password</label><br>
          <input type="password" id="login-password" name="login_password" minlength="8" maxlength="128" autocomplete="current-password" required>
        </div>
        <button type="submit">Login</button>
      </form>
      <?php if ($login_feedback): ?>
        <p class="page-feedback <?php echo $login_feedback['type'] === 'error' ? 'feedback-error' : 'feedback-success'; ?>">
          <?php echo htmlspecialchars($login_feedback['message']); ?>
        </p>
      <?php endif; ?>
    </section>

    <!-- New user form -->
    <section class="auth-section">
      <h2>Create an Account</h2>
      <form action="<?php echo htmlspecialchars($register_action); ?>" method="post">
        <input type="hidden" name="<?php echo htmlspecialchars($session_name); ?>" value="<?php echo htmlspecialchars(session_id()); ?>" />
        <div>
          <label for="register-username">Username</label><br>
          <input type="text" id="register-username" name="register_username" minlength="3" maxlength="50" pattern="[A-Za-z0-9_]{3,50}" title="Use 3 to 50 characters: letters, numbers, and underscores only." autocomplete="username" required>
        </div>
        <div>
          <label for="register-email">Email</label><br>
          <input type="email" id="register-email" name="register_email" maxlength="255" autocomplete="email" required>
        </div>
        <div>
          <label for="register-password">Password</label><br>
          <input type="password" id="register-password" name="register_password" minlength="8" maxlength="128" autocomplete="new-password" required>
        </div>
        <div>
          <label for="register-confirm-password">Confirm Password</label><br>
          <input type="password" id="register-confirm-password" name="register_confirm_password" minlength="8" maxlength="128" autocomplete="new-password" required>
        </div>
        <button type="submit">Create Account</button>
      </form>
      <?php if ($register_feedback): ?>
        <p class="page-feedback <?php echo $register_feedback['type'] === 'error' ? 'feedback-error' : 'feedback-success'; ?>">
          <?php echo htmlspecialchars($register_feedback['message']); ?>
        </p>
      <?php endif; ?>
    </section>
  </main>
</body>

</html>