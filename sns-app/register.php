<?php
session_start();
require 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username=?");
    $stmt->execute([$username]);
    $exists = $stmt->fetchColumn();

    if ($exists) {
        $error = "ユーザー名は既に使われています。";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $password]);
        $user_id = $pdo->lastInsertId();

        $_SESSION['user'] = $username;
        $_SESSION['user_id'] = $user_id;

        header("Location: index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="style.css">
<title>新規登録</title>
</head>
<body>
<div class="container">
    <div class="card">
        <h2>新規登録</h2>
        <form method="post">
            <input type="text" name="username" placeholder="ユーザー名" required>
            <input type="password" name="password" placeholder="パスワード" required>
            <button type="submit">登録</button>
        </form>
        <p class="link-text">
            <a href="login.php">ログインはこちら</a>
        </p>
        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
    </div>
</div>
</body>
</html>