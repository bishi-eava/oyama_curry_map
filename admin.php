<?php
// 施設一覧・削除用管理ページ
require_once 'auth_check.php';

// 認証チェック
checkAuth();

// ログアウト処理
if (isset($_GET['logout'])) {
    doLogout();
}

$db = getDatabase();

// 削除処理
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // 関連する画像ファイルを削除
    $imageRes = $db->query("SELECT filename FROM shop_images WHERE shop_id = $id");
    while ($imageRow = $imageRes->fetchArray(SQLITE3_ASSOC)) {
        $filePath = __DIR__ . '/' . $config['storage']['images_dir'] . '/' . $imageRow['filename'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
    
    // データベースから削除（外部キー制約により画像も自動削除）
    $db->exec("DELETE FROM shops WHERE id = $id");
    header('Location: admin.php');
    exit;
}


$res = $db->query('SELECT * FROM shops ORDER BY id DESC');
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($config['app']['facility_name']) ?>管理</title>
    <link rel="stylesheet" href="css/common.css" />
    <link rel="stylesheet" href="css/admin.css" />
</head>
<body>
    <div class="header">
        <h1><?= htmlspecialchars($config['app']['facility_name']) ?>管理</h1>
        <div>
            <a href="admin_add.php">新規登録</a>
            <a href="admin_password.php">パスワード変更</a>
            <a href="index.php">地図に戻る</a>
            <a href="?logout=1">ログアウト</a>
        </div>
    </div>
    <div style="overflow-x: auto;">
        <table>
            <tr>
                <th class="col-id">ID</th>
                <th class="col-name"><?= htmlspecialchars($config['app']['facility_name']) ?>名</th>
                <th class="col-category">カテゴリー</th>
                <th class="col-description">説明</th>
                <th class="col-address">住所</th>
                <th class="col-phone">電話番号</th>
                <th class="col-hours">営業時間</th>
                <th class="col-website">ウェブサイト</th>
                <th class="col-sns">SNS</th>
                <th class="col-review">レビュー</th>
                <th class="col-updated">更新日時</th>
                <th class="col-images">画像</th>
                <th class="col-actions">操作</th>
            </tr>
        <?php while ($row = $res->fetchArray(SQLITE3_ASSOC)): ?>
        <tr>
            <td class="col-id"><?= htmlspecialchars($row['id']) ?></td>
            <td class="col-name"><?= htmlspecialchars($row['name']) ?></td>
            <td class="col-category">
                <?php if (!empty($row['category'])): ?>
                    <?= htmlspecialchars($row['category']) ?>
                <?php else: ?>
                    <span style="color:#999;">なし</span>
                <?php endif; ?>
            </td>
            <td class="col-description">
                <?php if (!empty($row['description'])): ?>
                    <?= htmlspecialchars(mb_strlen($row['description']) > 50 ? mb_substr($row['description'], 0, 50) . '...' : $row['description']) ?>
                <?php else: ?>
                    <span style="color:#999;">なし</span>
                <?php endif; ?>
            </td>
            <td class="col-address">
                <?php if (!empty($row['address'])): ?>
                    <?= htmlspecialchars($row['address']) ?>
                <?php else: ?>
                    <span style="color:#999;">なし</span>
                <?php endif; ?>
            </td>
            <td class="col-phone">
                <?php if (!empty($row['phone'])): ?>
                    <a href="tel:<?= htmlspecialchars($row['phone']) ?>" style="color:#007bff; text-decoration:none;">
                        <?= htmlspecialchars($row['phone']) ?>
                    </a>
                <?php else: ?>
                    <span style="color:#999;">なし</span>
                <?php endif; ?>
            </td>
            <td class="col-hours">
                <?php if (!empty($row['business_hours'])): ?>
                    <?= htmlspecialchars($row['business_hours']) ?>
                <?php else: ?>
                    <span style="color:#999;">なし</span>
                <?php endif; ?>
            </td>
            <td class="col-website">
                <?php if (!empty($row['website'])): ?>
                    <a href="<?= htmlspecialchars($row['website']) ?>" target="_blank" style="color:#007bff; text-decoration:none;">
                        <?= htmlspecialchars($row['website']) ?>
                    </a>
                <?php else: ?>
                    <span style="color:#999;">なし</span>
                <?php endif; ?>
            </td>
            <td class="col-sns">
                <?php if (!empty($row['sns_account'])): ?>
                    <?= htmlspecialchars($row['sns_account']) ?>
                <?php else: ?>
                    <span style="color:#999;">なし</span>
                <?php endif; ?>
            </td>
            <td class="col-review">
                <?php if (!empty($row['review'])): ?>
                    <?= htmlspecialchars(mb_strlen($row['review']) > 50 ? mb_substr($row['review'], 0, 50) . '...' : $row['review']) ?>
                <?php else: ?>
                    <span style="color:#999;">なし</span>
                <?php endif; ?>
            </td>
            <td class="col-updated" style="font-size:0.8em;">
                <?php if (!empty($row['updated_at'])): ?>
                    <?= htmlspecialchars(date('Y/m/d H:i', strtotime($row['updated_at']))) ?>
                <?php else: ?>
                    <span style="color:#999;">なし</span>
                <?php endif; ?>
            </td>
            <td class="col-images">
                <?php
                // 施設の画像を取得
                $imageStmt = $db->prepare('SELECT id, filename, original_name FROM shop_images WHERE shop_id = :shop_id ORDER BY id');
                $imageStmt->bindValue(':shop_id', $row['id'], SQLITE3_INTEGER);
                $imageRes = $imageStmt->execute();
                
                $imageCount = 0;
                while ($imageRow = $imageRes->fetchArray(SQLITE3_ASSOC)):
                    $imageCount++;
                ?>
                    <div style="margin:2px; display:inline-block;">
                        <img src="<?= htmlspecialchars($config['storage']['images_dir']) ?>/<?= htmlspecialchars($imageRow['filename']) ?>" 
                             style="width:50px;height:50px;object-fit:cover;border:1px solid #ccc;" 
                             title="<?= htmlspecialchars($imageRow['original_name']) ?>">
                    </div>
                <?php endwhile; ?>
                <?php if ($imageCount === 0): ?>
                    <span style="color:#999;">なし</span>
                <?php endif; ?>
            </td>
            <td class="col-actions">
                <a href="admin_edit.php?id=<?= $row['id'] ?>" style="color:#007bff; text-decoration:none; margin-right:1em;">編集</a>
                <a href="?delete=<?= $row['id'] ?>" class="del" onclick="return confirm('本当に削除しますか？（画像も全て削除されます）');">削除</a>
            </td>
        </tr>
        <?php endwhile; ?>
        </table>
    </div>
</body>
</html>
