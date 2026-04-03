<?php

header('Content-Type: application/json');

$host = 'localhost';
$dbname = 'zychowj_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $postId = isset($_GET['post_id']) ? (int) $_GET['post_id'] : 0;
    if ($postId <= 0) {
        echo json_encode(['error' => 'Invalid post_id']);
        exit;
    }

    // Comments only (same DB as posts). If you store users in this DB, you can
    // LEFT JOIN users u ON c.user_id = u.id and add u.username AS author_name.
    $sql = 'SELECT id, content, post_id, user_id, parent_comment_id, created_at
            FROM comments
            WHERE post_id = :post_id
            ORDER BY created_at ASC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':post_id' => $postId]);

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

?>
