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
  header('Location: ../authentication/login.php');
  exit;
}

$user_name = $_SESSION['username'] ?? null;
$current_user_id = $_SESSION['user_id'] ?? null;
$is_logged_in = $current_user_id !== null;

?>

<!doctype html>

<html>
  <head>
    <meta charset="utf-8" />
    <title>Posts</title>
    <script>
      window.postPageContext = {
        isLoggedIn: <?php echo json_encode($is_logged_in); ?>,
        currentUserId: <?php echo json_encode($current_user_id); ?>,
        currentUserName: <?php echo json_encode($user_name); ?>
      };
    </script>
    <script src="post.js"></script>
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
          href="post.php"
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
        <a
          href="add_post.php"
          style="
            display: inline-block;
            padding: 6px 12px;
            border: 1px solid #666;
            background-color: #f3f3f3;
            color: #000;
            text-decoration: none;
            border-radius: 4px;
          "
          >Create Post</a
        >
        <?php endif; ?>
        <?php if ($is_logged_in): ?>
        <form action="post.php" method="post" style="margin: 0;">
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
    <?php if ($is_logged_in): ?>
    <div
      style="
        margin: 20px 0;
        padding: 12px 16px;
        border: 1px solid #c8c8c8;
        background-color: #f8f8f8;
        display: flex;
        justify-content: space-between;
        align-items: center;
      "
    >
      <span>Create a new post</span>
      <a
        href="add_post.php"
        style="
          display: inline-block;
          padding: 8px 14px;
          border: 1px solid #666;
          background-color: #e6e6e6;
          color: #000;
          text-decoration: none;
          border-radius: 4px;
        "
      >Create Post</a>
    </div>
    <?php else: ?>
    <div
      style="
        margin: 20px 0;
        padding: 12px 16px;
        border: 1px solid #c8c8c8;
        background-color: #f8f8f8;
      "
    >
      Log in to create posts and edit your own posts.
    </div>
    <?php endif; ?>

    <div id="postContainer"></div>

    <?php if ($is_logged_in): ?>
    <form
      id="editPost"
      style="
        display: none;
        border: 1px solid black;
        padding: 10px;
        margin-top: 20px;
      "
    >
      <h2>Edit Post</h2>
      <input type="hidden" id="editId" />
      <input id="editName" type="text" placeholder="New Title" />
      <textarea id="editDesc" placeholder="New Description"></textarea>
      <button type="submit">Save Changes</button>
      <button
        type="button"
        onclick="document.getElementById('editPost').style.display = 'none'"
      >
        Cancel
      </button>
    </form>
    <?php endif; ?>

    <form id="deletePost">
      <h2>Delete Post</h2>
    </form>
  </body>
</html>
