<?php
// 施設一覧をJSONで返すAPI
require_once 'auth_check.php';

header('Content-Type: application/json; charset=UTF-8');
$config = getConfig();
$db = getDatabase();
$res = $db->query('SELECT * FROM facilities');
$facilities = [];
while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    // 各施設の画像を取得
    $imageStmt = $db->prepare('SELECT filename, original_name FROM facility_images WHERE facility_id = :facility_id ORDER BY id');
    $imageStmt->bindValue(':facility_id', $row['id'], SQLITE3_INTEGER);
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
    $facilities[] = $row;
}
echo json_encode($facilities, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
