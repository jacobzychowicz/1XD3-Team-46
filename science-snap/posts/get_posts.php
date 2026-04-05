<?php 

header('Content-Type: application/json');

$host = 'localhost';
$dbname = 'science_snap';
$username = 'root';
$password = '';

session_start();
$current_user_id = $_SESSION['user_id'] ?? 0;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo -> prepare(
        "SELECT posts.*, users.username,
                CASE WHEN posts.user_id = :current_user_id THEN 1 ELSE 0 END AS is_owner
         FROM posts
         LEFT JOIN users ON posts.user_id = users.id
         ORDER BY posts.created_at DESC"
    );
    $stmt -> execute([':current_user_id' => $current_user_id]);
    $posts = $stmt -> fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($posts);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e -> getMessage()]);
}

?>