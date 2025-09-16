<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/utils.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$post = ['title' => '', 'body' => '', 'id' => 0];
$selectedTags = [];

if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM posts WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$post) { die('Post not found'); }
    $selectedTags = array_column(tags_for_post($pdo, $id), 'id');
}

$tags = all_tags($pdo);
?>
<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $id ? '投稿を編集' : '新規投稿' ?></title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
  <div class="header">
    <h1><?= $id ? '投稿を編集' : '新規投稿' ?></h1>
    <div>
      <a class="btn" href="index.php">← 一覧へ</a>
    </div>
  </div>

  <div class="card">
    <form method="post" action="save_post.php">
      <input type="hidden" name="id" value="<?= (int)$id ?>">
      <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

      <label>タイトル</label>
      <input type="text" name="title" required value="<?= h($post['title']) ?>">

      <label>本文</label>
      <textarea name="body" rows="10" required><?= h($post['body']) ?></textarea>

      <label>タグ（既存タグから選択）</label>
      <div class="tags-list">
        <?php foreach ($tags as $t): ?>
          <label class="tag">
            <input type="checkbox" name="tags[]" value="<?= (int)$t['id'] ?>" 
              <?= in_array($t['id'], $selectedTags, true) ? 'checked' : '' ?>>
            <?= h($t['name']) ?>
          </label>
        <?php endforeach; ?>
      </div>

      <label>新しいタグを追加（カンマ区切り）</label>
      <input type="text" name="new_tags" placeholder="例）diary, php, memo">

      <div style="margin-top:12px;">
        <button class="btn primary" type="submit">保存</button>
        <a class="btn" href="index.php">キャンセル</a>
      </div>
    </form>
  </div>
</div>
</body>
</html>