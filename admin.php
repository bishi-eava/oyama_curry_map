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
    $imageRes = $db->query("SELECT filename FROM facility_images WHERE facility_id = $id");
    while ($imageRow = $imageRes->fetchArray(SQLITE3_ASSOC)) {
        $filePath = __DIR__ . '/' . $config['storage']['images_dir'] . '/' . $imageRow['filename'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
    
    // データベースから削除（外部キー制約により画像も自動削除）
    $db->exec("DELETE FROM facilities WHERE id = $id");
    header('Location: admin.php');
    exit;
}


$res = $db->query('SELECT * FROM facilities ORDER BY id DESC');
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
                <th class="col-name"><?= htmlspecialchars($config['app']['field_labels']['name']) ?></th>
                <th class="col-category"><?= htmlspecialchars($config['app']['field_labels']['category']) ?></th>
                <th class="col-description"><?= htmlspecialchars($config['app']['field_labels']['description']) ?></th>
                <th class="col-address"><?= htmlspecialchars($config['app']['field_labels']['address']) ?></th>
                <th class="col-phone"><?= htmlspecialchars($config['app']['field_labels']['phone']) ?></th>
                <th class="col-hours"><?= htmlspecialchars($config['app']['field_labels']['business_hours']) ?></th>
                <th class="col-website"><?= htmlspecialchars($config['app']['field_labels']['website']) ?></th>
                <th class="col-sns"><?= htmlspecialchars($config['app']['field_labels']['sns_account']) ?></th>
                <th class="col-review"><?= htmlspecialchars($config['app']['field_labels']['review']) ?></th>
                <th class="col-updated">更新日時</th>
                <th class="col-images"><?= htmlspecialchars($config['app']['field_labels']['images']) ?></th>
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
                $imageStmt = $db->prepare('SELECT id, filename, original_name FROM facility_images WHERE facility_id = :facility_id ORDER BY id');
                $imageStmt->bindValue(':facility_id', $row['id'], SQLITE3_INTEGER);
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
