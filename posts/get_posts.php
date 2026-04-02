<?php 

header('Content-Type: application/json');

$host = 'localhost';
$dbname = 'zychowj_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);

    $stmt = $pdo -> query("SELECT * FROM posts ORDER BY created_at DESC");
    $posts = $stmt -> fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($posts);
} catch (PDOException $e) {
    echo json_encode(['error' => $e -> getMessage()]);
}

?>