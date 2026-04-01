<?php 

$host = 'localhost';
$dbname = 'zychowj_db';
$username = 'zychowj_local';
$password = '10UT8Z{P';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!empty($data['title'])) {
        $sql = "INSERT INTO posts (title, description, attachment, user_id) VALUES (:title, :description, :attachment, :user_id)";
        $stmt = $pdo -> prepare($sql);

        $stmt -> execute([
            ':title' => $data['title'],
            ':description' => $data['description'],
            ':attachment' => $data['attachment'],
            ':user_id' => $data['user_id']
        ]);

        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e -> getMessage()]);
}

?>