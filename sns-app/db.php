<?php
$host = "localhost";
$port = "8889";  // ← MAMPではMySQLのデフォルトポートが8889
$dbname = "sns_app";
$user = "root";
$pass = "root";

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "接続成功！"; // ← テスト用
} catch (PDOException $e) {
    die("DB接続失敗: " . $e->getMessage());
}
?>