<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/utils.php';

$tagFilter = $_GET['tag'] ?? '';
$tagFilter = trim($tagFilter);

if ($tagFilter) {
    $stmt = $pdo->prepare('
        SELECT p.* FROM posts p
        WHERE p.id IN (
            SELECT pt.post_id FROM post_tags pt
            JOIN tags t ON t.id = pt.tag_id
            WHERE t.name = :tname
        )
        ORDER BY p.created_at DESC, p.id DESC
    ');
    $stmt->execute([':tname' => $tagFilter]);
} else {
    $stmt = $pdo->query('SELECT * FROM posts ORDER BY created_at DESC, id DESC');
}

$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
$tags = all_tags($pdo);
?>
<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Blog</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
  <div class="header">
    <h1>Blog</h1>
    <div>
      <a class="btn primary" href="form.php">+ 新規投稿</a>
    </div>
  </div>

  <div class="filter card">
    <form method="get" action="index.php">
      <label for="tag">タグで絞り込み</label>
      <select id="tag" name="tag">
        <option value="">-- 全て --</option>
        <?php foreach ($tags as $t): ?>
          <option value="<?= h($t['name']) ?>" <?= $tagFilter === $t['name'] ? 'selected' : '' ?>>
            <?= h($t['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <button class="btn" type="submit">絞り込み</button>
      <?php if ($tagFilter): ?>
        <a class="btn" href="index.php">クリア</a>
      <?php endif; ?>
    </form>
  </div>

  <?php if (empty($posts)): ?>
    <div class="card">投稿がありません。</div>
  <?php endif; ?>

  <?php foreach ($posts as $p): ?>
    <?php $postTags = tags_for_post($pdo, $p['id']); ?>
    <div class="card">
      <h2><?= h($p['title']) ?></h2>
      <div class="meta">
        作成: <?= h($p['created_at']) ?>
        <?php if (!empty($p['updated_at']) && $p['updated_at'] !== $p['created_at']): ?>
          ・更新: <?= h($p['updated_at']) ?>
        <?php endif; ?>
      </div>
      <p><?= nl2br(h($p['body'])) ?></p>
      <div class="tags-list">
        <?php foreach ($postTags as $t): ?>
          <a class="tag" href="index.php?tag=<?= h($t['name']) ?>"><?= h($t['name']) ?></a>
        <?php endforeach; ?>
        <?php if (count($postTags) === 0): ?>
          <span class="badge">タグなし</span>
        <?php endif; ?>
      </div>
      <hr class="sep" />
      <div>
        <a class="btn" href="form.php?id=<?= (int)$p['id'] ?>">編集</a>
        <form class="inline" method="post" action="delete_post.php" onsubmit="return confirm('削除しますか？');">
          <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
          <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
          <button class="btn" type="submit">削除</button>
        </form>
      </div>
    </div>
  <?php endforeach; ?>

  <div class="footer">© Simple Blog</div>
</div>
</body>
</html>