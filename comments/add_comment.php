<?php

header('Content-Type: application/json');

$host = 'localhost';
$dbname = 'zychowj_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    $content = isset($data['content']) ? trim($data['content']) : '';
    $postId = isset($data['post_id']) ? (int) $data['post_id'] : 0;
    $userId = isset($data['user_id']) ? (int) $data['user_id'] : 0;

    $parentRaw = isset($data['parent_comment_id']) ? $data['parent_comment_id'] : null;
    $parentId = null;
    if ($parentRaw !== null && $parentRaw !== '') {
        $parentId = (int) $parentRaw;
        if ($parentId <= 0) {
            $parentId = null;
        }
    }

    if ($content === '' || $postId <= 0 || $userId <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
        exit;
    }

    if ($parentId !== null) {
        $check = $pdo->prepare('SELECT id FROM comments WHERE id = :id AND post_id = :post_id');
        $check->execute([':id' => $parentId, ':post_id' => $postId]);
        if (!$check->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid parent comment']);
            exit;
        }
    }

    $sql = 'INSERT INTO comments (content, post_id, user_id, parent_comment_id)
            VALUES (:content, :post_id, :user_id, :parent_comment_id)';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':content' => $content,
        ':post_id' => $postId,
        ':user_id' => $userId,
        ':parent_comment_id' => $parentId
    ]);

    echo json_encode(['status' => 'success', 'id' => (int) $pdo->lastInsertId()]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

?>
