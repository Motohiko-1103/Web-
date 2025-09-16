<?php
require_once __DIR__ . '/utils.php';

$dbDir = __DIR__ . '/db';
if (!is_dir($dbDir)) {
    mkdir($dbDir, 0755, true);
}
$dbPath = $dbDir . '/blog.sqlite';
$dsn = 'sqlite:' . $dbPath;

try {
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = ON');
} catch (PDOException $e) {
    die('DB接続失敗: ' . h($e->getMessage()));
}

// 初回アクセス時にテーブル自動作成
if (!file_exists($dbPath) || filesize($dbPath) === 0) {
    $schemaFile = __DIR__ . '/schema.sql';
    if (!file_exists($schemaFile)) {
        die('schema.sql が見つかりません');
    }
    $schema = file_get_contents($schemaFile);
    $pdo->exec($schema);

    // デフォルトタグを登録
    $defaultTags = ['news','tech','life','study'];
    $stmt = $pdo->prepare('INSERT OR IGNORE INTO tags(name) VALUES(:n)');
    foreach ($defaultTags as $t) {
        $stmt->execute([':n' => $t]);
    }
}

// 全タグ取得
function all_tags(PDO $pdo) {
    $res = $pdo->query('SELECT id, name FROM tags ORDER BY name');
    return $res->fetchAll(PDO::FETCH_ASSOC);
}

// 投稿のタグ取得
function tags_for_post(PDO $pdo, $post_id) {
    $stmt = $pdo->prepare('
        SELECT t.id, t.name
        FROM tags t
        JOIN post_tags pt ON pt.tag_id = t.id
        WHERE pt.post_id = :pid
        ORDER BY t.name
    ');
    $stmt->execute([':pid' => $post_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>