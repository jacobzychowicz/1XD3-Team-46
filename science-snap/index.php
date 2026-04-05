<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body
  style="
    margin: 0;
    min-height: 100vh;
    position: relative;
  "
>
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
          href="posts/post.php"
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
          href="/authentication/login.php"
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
    
    <div
      id="title"
      style="
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        width: 100%;
      "
    >
        <h1>Science Snap</h1>
        <h2>Research in a Snap</h2>
        <h3>Zak Yarwood</h3>
    </div>
</body>
</html>