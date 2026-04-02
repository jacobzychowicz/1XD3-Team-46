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
        $stmt = $pdo -> prepare("DELETE FROM posts WHERE id = :id");
        $stmt -> execute([':id' => $data['id']]);

        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No ID provided']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e -> getMessage()]);
}

?>