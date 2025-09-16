<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/utils.php';

verify_csrf();
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) { die('invalid id'); }

$stmt = $pdo->prepare('DELETE FROM posts WHERE id = :id');
$stmt->execute([':id' => $id]);
redirect('index.php');