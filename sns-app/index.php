<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// 投稿処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = trim($_POST['content']);
    $imagePath = null;

    if (!empty($_FILES['image']['name'])) {
        $uploadsDir = 'uploads/';
        if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0777, true);

        $imageName = time() . '_' . basename($_FILES['image']['name']);
        $imagePath = $uploadsDir . $imageName;

        move_uploaded_file($_FILES['image']['tmp_name'], $imagePath);
    }

    $stmt = $pdo->prepare("INSERT INTO posts (user_id, content, image) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $content, $imagePath]);

    header("Location: index.php");
    exit;
}

// 投稿取得
$stmt = $pdo->query("SELECT p.*, u.username FROM posts p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC");
$posts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="style.css">
<title>Simple SNS</title>
</head>
<body>
<div class="container">

    <div class="card">
        <h2>タイムライン</h2>
        <form method="post" enctype="multipart/form-data">
            <textarea name="content" placeholder="投稿内容" required></textarea>
            <input type="file" name="image" accept="image/*">
            <button type="submit">投稿</button>
        </form>
        <p class="link-text">
            <a href="login.php">ログアウト</a>
        </p>
    </div>

    <div class="post-list">
        <?php foreach($posts as $post): ?>
        <div class="post-item">
            <div class="post-username"><?= htmlspecialchars($post['username']) ?></div>
            <div class="post-content"><?= nl2br(htmlspecialchars($post['content'])) ?></div>
            <?php if (!empty($post['image'])): ?>
                <div class="post-image">
                    <img src="<?= htmlspecialchars($post['image']) ?>" alt="投稿画像">
                </div>
            <?php endif; ?>
            <div class="post-time"><?= $post['created_at'] ?></div>
        </div>
        <?php endforeach; ?>
    </div>

</div>
</body>
</html>