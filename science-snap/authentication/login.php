<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
  $_SESSION = [];

  if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
      session_name(),
      '',
      time() - 42000,
      $params['path'],
      $params['domain'],
      $params['secure'],
      $params['httponly']
    );
  }

  session_destroy();
  header('Location: login.php');
  exit;
}

$user_name = $_SESSION['username'] ?? null;
$is_logged_in = isset($_SESSION['user_id']);
$login_feedback = $_SESSION['login_feedback'] ?? null;
$register_feedback = $_SESSION['register_feedback'] ?? null;

unset($_SESSION['login_feedback'], $_SESSION['register_feedback']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <header
      style="
        background-color: #e6e6e6;
        padding: 14px 16px;
        text-align: center;
        position: relative;
      "
    >
      <h1 style="margin: 0; font-size: 1.5rem">Science Snap</h1>
      <div
        style="
          position: absolute;
          right: 16px;
          top: 50%;
          transform: translateY(-50%);
          display: flex;
          align-items: center;
          gap: 10px;
        "
      >
        <?php if ($user_name): ?>
        <span
          style="
            color: #333;
            font-size: 0.95rem;
            font-weight: 600;
          "
          >Current User: <?php echo htmlspecialchars($user_name); ?></span
        >
        <?php endif; ?>
        <a
          href="../posts/post.php"
          style="
            display: inline-block;
            padding: 6px 12px;
            border: 1px solid #666;
            background-color: #dcdcdc;
            color: #000;
            text-decoration: none;
            border-radius: 4px;
          "
          >Posts</a
        >
        <?php if ($is_logged_in): ?>
        <form action="login.php" method="post" style="margin: 0;">
          <input type="hidden" name="logout" value="1" />
          <button
            type="submit"
            style="
              display: inline-block;
              padding: 6px 12px;
              border: 1px solid #666;
              background-color: #f3f3f3;
              color: #000;
              text-decoration: none;
              border-radius: 4px;
              cursor: pointer;
            "
          >Logout</button>
        </form>
        <?php else: ?>
        <a
          href="../authentication/login.php"
          style="
            display: inline-block;
            padding: 6px 12px;
            border: 1px solid #666;
            background-color: #f3f3f3;
            color: #000;
            text-decoration: none;
            border-radius: 4px;
          "
          >Login</a
        >
        <?php endif; ?>
      </div>
    </header>
    <main style="display: flex; gap: 32px; align-items: flex-start;">
        <section>
            <h2>Login</h2>
            <form action="authenticate.php" method="post">
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
              <p style="margin-top: 12px; color: <?php echo $login_feedback['type'] === 'error' ? '#b00020' : '#1f6f43'; ?>;">
                <?php echo htmlspecialchars($login_feedback['message']); ?>
              </p>
              <?php endif; ?>
        </section>

        <section>
            <h2>Create an Account</h2>
            <form action="create_user.php" method="post">
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
              <p style="margin-top: 12px; color: <?php echo $register_feedback['type'] === 'error' ? '#b00020' : '#1f6f43'; ?>;">
                <?php echo htmlspecialchars($register_feedback['message']); ?>
              </p>
              <?php endif; ?>
        </section>
    </main>
</body>
</html>