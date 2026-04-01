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

    if (isset($data['title'])) {
        $sql = "INSERT INTO posts (title) VALUES (:title)";
        $stmt = $pdo -> prepare($sql);

        $stmt -> execute(['title' => $data['title']]);

        echo json_encode(['status' => 'success', 'message' => 'Post added!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No title provided']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e -> getMessage()]);
}

?>