<?php

$host = 'localhost';
$dbname = 'science_snap';
$username = 'root';
$password = '';

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

$user_id = $_SESSION['user_id'] ?? null;
$user_name = $_SESSION['username'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (!$user_id) {
                header('Location: ../authentication/login.php');
                exit;
        }
        ?>
<!doctype html>
<html>
    <head>
        <meta charset="utf-8" />
        <title>Create Post</title>
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
                <span
                    style="
                        color: #333;
                        font-size: 0.95rem;
                        font-weight: 600;
                    "
                    >Current User: <?php echo htmlspecialchars($user_name); ?></span
                >
                <a
                    href="post.php"
                    style="
                        display: inline-block;
                        padding: 6px 12px;
                        border: 1px solid #666;
                        background-color: #f3f3f3;
                        color: #000;
                        text-decoration: none;
                        border-radius: 4px;
                    "
                    >Posts</a
                >
                <a
                    href="add_post.php"
                    style="
                        display: inline-block;
                        padding: 6px 12px;
                        border: 1px solid #666;
                        background-color: #dcdcdc;
                        color: #000;
                        text-decoration: none;
                        border-radius: 4px;
                    "
                    >Create Post</a
                >
                <form action="add_post.php" method="post" style="margin: 0;">
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
            </div>
        </header>

        <main style="max-width: 720px; margin: 32px auto; padding: 0 16px;">
            <h2>Create Post</h2>
            <form id="addPost">
                <label for="postName">Post name: </label><br />
                <input id="postName" placeholder="Post Title" type="text" />
                <br /><br />

                <label for="postDesc">Post description: </label><br />
                <textarea id="postDesc" placeholder="Describe your post..." style="width: 100%; min-height: 140px;"></textarea>
                <br /><br />

                <label for="postAttach">Attachments</label><br />
                <input id="postAttach" placeholder="Image URL (optional)" type="file" />
                <br /><br />

                <div style="display: flex; gap: 12px; align-items: center;">
                    <button type="submit">Create Post</button>
                    <a href="post.php" style="color: #000;">Cancel</a>
                </div>
            </form>
            <p id="createPostFeedback" style="margin-top: 12px;"></p>
        </main>

        <script>
            window.addEventListener('load', function () {
                const addPostForm = document.getElementById('addPost');
                const feedback = document.getElementById('createPostFeedback');

                addPostForm.addEventListener('submit', async function (event) {
                    event.preventDefault();

                    const postData = {
                        title: document.getElementById('postName').value,
                        description: document.getElementById('postDesc').value,
                        attachment: document.getElementById('postAttach').value
                    };

                    try {
                        const response = await fetch('add_post.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(postData)
                        });

                        const result = await response.json();

                        if (result.status === 'success') {
                            window.location.href = 'post.php';
                            return;
                        }

                        feedback.textContent = result.message || 'Unable to create post.';
                        feedback.style.color = '#b00020';
                    } catch (error) {
                        console.error('Create failed:', error);
                        feedback.textContent = 'Unable to create post.';
                        feedback.style.color = '#b00020';
                    }
                });
            });
        </script>
    </body>
</html>
<?php
        exit;
}

header('Content-Type: application/json');

if (!$user_id) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'You must be logged in to create a post.']);
        exit;
}

try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!empty($data['title'])) {
                $sql = "INSERT INTO posts (title, description, attachment, user_id) VALUES (:title, :description, :attachment, :user_id)";
                $stmt = $pdo->prepare($sql);

                $stmt->execute([
                        ':title' => $data['title'],
                        ':description' => $data['description'] ?? '',
                        ':attachment' => $data['attachment'] ?? '',
                        ':user_id' => $user_id
                ]);

                echo json_encode(['status' => 'success']);
        } else {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Post title is required.']);
        }
} catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

?>