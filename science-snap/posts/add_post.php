<?php

session_start();

// handle logout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    $_SESSION = [];
    session_destroy();
    header('Location: ../authentication/login.php');
    exit;
}

// get user info from session
$user_id = $_SESSION['user_id'] ?? null;
$user_name = $_SESSION['username'] ?? null;

// redirect to login if user is not logged in (just in case lol)
if (!$user_id) {
    header('Location: ../authentication/login.php');
    exit;
}

// PDO values
$dbname = 'zychowj_db';
$db_username = 'zychowj_local';
$db_password = '10UT8Z{P';

// create post feedback
$feedback_message = '';

// create post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['logout'])) {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($title === '') {
        $feedback_message = 'Post title is required.';
    } else {
        try {
            $pdo = new PDO("mysql:host=localhost;dbname=$dbname;charset=utf8", $username, $password);

            $stmt = $pdo->prepare('INSERT INTO posts (title, description, user_id) VALUES (:title, :description, :user_id)');
            $stmt->execute([
                ':title' => $title,
                ':description' => $description,
                ':user_id' => $user_id
            ]);

            // redirect to the new post
            header('Location: post.php');
            exit;
        } catch (PDOException $e) {
            $feedback_message = 'Unable to create post.';
        }
    }
}

// links
$posts_link = 'post.php';
$create_post_link = 'add_post.php';
$logout_action = 'add_post.php';
?>
<!doctype html>
<html>
    <head>
        <meta charset="utf-8" />
        <link rel="stylesheet" href="../css/common.css">
        <link rel="stylesheet" href="../css/posts.css">
        <title>Create Post</title>
    </head>
    <body>
        <header class="site-header">
            <h1 class="site-title">Science Snap</h1>
            <div class="site-nav">
                <span class="current-user">Current User: <?php echo htmlspecialchars($user_name); ?></span>
                <a
                    href="<?php echo htmlspecialchars($posts_link); ?>"
                    class="nav-link nav-link-secondary"
                    >Posts</a
                >
                <a
                    href="<?php echo htmlspecialchars($create_post_link); ?>"
                    class="nav-link nav-link-primary"
                    >Create Post</a
                >
                <form action="<?php echo htmlspecialchars($logout_action); ?>" method="post" class="logout-form">
                    <input type="hidden" name="logout" value="1" />
                    <button type="submit" class="logout-button">Logout</button>
                </form>
            </div>
        </header>

        <!-- create post form -->
        <main class="page-main page-main-narrow">
            <h2>Create Post</h2>
            <form action="<?php echo htmlspecialchars($create_post_link); ?>" method="post">
                <label for="postName">Post name:</label><br />
                <input id="postName" name="title" placeholder="Post Title" type="text" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" />
                <br /><br />

                <label for="postDesc">Post description:</label><br />
                <textarea id="postDesc" name="description" placeholder="Describe your post..." class="post-textarea"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                <br /><br />

                <div class="form-actions">
                    <button type="submit">Create Post</button>
                    <a href="<?php echo htmlspecialchars($posts_link); ?>" class="text-link">Cancel</a>
                </div>
            </form>

            <!-- show post feedback -->
            <?php if ($feedback_message !== ''): ?>
            <p class="page-feedback feedback-error"><?php echo htmlspecialchars($feedback_message); ?></p>
            <?php endif; ?>
        </main>
    </body>
</html>