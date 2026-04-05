<?php 

$host = 'localhost';
$dbname = 'science_snap';
$username = 'root';
$password = '';

header('Content-Type: application/json');

session_start();
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to edit posts.']);
    exit;
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!empty($data['id'])) {
        $sql = "UPDATE posts SET title = :title, description = :description WHERE id = :id AND user_id = :user_id";
        $stmt = $pdo -> prepare($sql);

        $stmt -> execute([
            ':title' => $data['title'],
            ':description' => $data['description'],
            ':id' => $data['id'],
            ':user_id' => $user_id
        ]);

        if ($stmt -> rowCount() === 0) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'You can only edit your own posts.']);
            exit;
        }

        echo json_encode(['status' => 'success']);
    } else {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Post ID is required.']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e -> getMessage()]);
}

?>