<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/utils.php';

verify_csrf();

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$title = trim($_POST['title'] ?? '');
$body = trim($_POST['body'] ?? '');
$chosenTagIds = array_map('intval', $_POST['tags'] ?? []);
$newTagNames = parse_new_tags($_POST['new_tags'] ?? '');

if ($title === '' || $body === '') {
    http_response_code(400);
    die('タイトルと本文は必須です。');
}

$pdo->beginTransaction();
try {
    if (!empty($newTagNames)) {
        $stmt = $pdo->prepare('INSERT OR IGNORE INTO tags(name) VALUES(:name)');
        foreach ($newTagNames as $name) {
            $stmt->execute([':name' => $name]);
        }
    }

    if (!empty($newTagNames)) {
        $in = implode(',', array_fill(0, count($newTagNames), '?'));
        $q = $pdo->prepare("SELECT id FROM tags WHERE name IN ($in)");
        $q->execute(array_values($newTagNames));
        $newTagIds = array_map('intval', $q->fetchAll(PDO::FETCH_COLUMN));
        $chosenTagIds = array_values(array_unique(array_merge($chosenTagIds, $newTagIds)));
    }

    if ($id > 0) {
        $stmt = $pdo->prepare('UPDATE posts SET title = :t, body = :b, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        $stmt->execute([':t' => $title, ':b' => $body, ':id' => $id]);

        $pdo->prepare('DELETE FROM post_tags WHERE post_id = :pid')->execute([':pid' => $id]);
        if (!empty($chosenTagIds)) {
            $pt = $pdo->prepare('INSERT INTO post_tags(post_id, tag_id) VALUES(:pid, :tid)');
            foreach ($chosenTagIds as $tid) {
                $pt->execute([':pid' => $id, ':tid' => $tid]);
            }
        }
    } else {
        $stmt = $pdo->prepare('INSERT INTO posts(title, body) VALUES(:t, :b)');
        $stmt->execute([':t' => $title, ':b' => $body]);
        $id = (int)$pdo->lastInsertId();

        if (!empty($chosenTagIds)) {
            $pt = $pdo->prepare('INSERT INTO post_tags(post_id, tag_id) VALUES(:pid, :tid)');
            foreach ($chosenTagIds as $tid) {
                $pt->execute([':pid' => $id, ':tid' => $tid]);
            }
        }
    }

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    http_response_code(500);
    die('エラー: ' . h($e->getMessage()));
}

redirect('index.php');