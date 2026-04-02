<?php 

$host = 'localhost';
$dbname = 'zychowj_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);

    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!empty($data['id'])) {
        $sql = "UPDATE posts SET title = :title, description = :description WHERE id = :id";
        $stmt = $pdo -> prepare($sql);

        $stmt -> execute([
            ':title' => $data['title'],
            ':description' => $data['description'],
            ':id' => $data['id']
        ]);

        echo json_encode(['status' => 'success']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e -> getMessage()]);
}

?>