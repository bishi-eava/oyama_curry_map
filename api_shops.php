<?php
// 施設一覧をJSONで返すAPI
require_once 'auth_check.php';

header('Content-Type: application/json; charset=UTF-8');
$config = getConfig();
$db = getDatabase();
$res = $db->query('SELECT * FROM shops');
$shops = [];
while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    // 各施設の画像を取得
    $imageStmt = $db->prepare('SELECT filename, original_name FROM shop_images WHERE shop_id = :shop_id ORDER BY id');
    $imageStmt->bindValue(':shop_id', $row['id'], SQLITE3_INTEGER);
    $imageRes = $imageStmt->execute();
    
    $images = [];
    while ($imageRow = $imageRes->fetchArray(SQLITE3_ASSOC)) {
        $images[] = [
            'filename' => $imageRow['filename'],
            'original_name' => $imageRow['original_name'],
            'url' => $config['storage']['images_dir'] . '/' . $imageRow['filename']
        ];
    }
    
    $row['images'] = $images;
    $shops[] = $row;
}
echo json_encode($shops, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
