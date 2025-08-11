<?php
// 新規施設登録API
require_once 'auth_check.php';

header('Content-Type: application/json; charset=UTF-8');
$config = getConfig();

// 認証チェック
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => '認証が必要です']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'POSTメソッドで送信してください']);
    exit;
}

// フォームデータかJSONデータかを判定
$isFormData = isset($_POST['name']);
if ($isFormData) {
    $data = $_POST;
} else {
    $data = json_decode(file_get_contents('php://input'), true);
}

if (!isset($data['name'], $data['lat'], $data['lng'])) {
    http_response_code(400);
    echo json_encode(['error' => 'name, lat, lngは必須です']);
    exit;
}

$db = getDatabase();
// 同じ名前の施設が既に存在するかチェック
$stmt = $db->prepare('SELECT COUNT(*) as cnt FROM facilities WHERE name = :name');
$stmt->bindValue(':name', $data['name'], SQLITE3_TEXT);
$res = $stmt->execute();
$row = $res->fetchArray(SQLITE3_ASSOC);
if ($row['cnt'] > 0) {
    http_response_code(409);
    echo json_encode(['error' => "同じ名前の{$config['app']['facility_name']}が既に登録されています"]);
    exit;
}

// 日本時間でupdated_atを設定
$japanTime = date('Y-m-d H:i:s', time());
$stmt = $db->prepare('INSERT INTO facilities (name, name_kana, lat, lng, address, address_detail, installation_position, phone, phone_extension, corporate_number, organization_name, available_days, start_time, end_time, available_hours_note, pediatric_support, website, note, category, updated_at) VALUES (:name, :name_kana, :lat, :lng, :address, :address_detail, :installation_position, :phone, :phone_extension, :corporate_number, :organization_name, :available_days, :start_time, :end_time, :available_hours_note, :pediatric_support, :website, :note, :category, :updated_at)');
$stmt->bindValue(':name', $data['name'], SQLITE3_TEXT);
$stmt->bindValue(':name_kana', $data['name_kana'] ?? '', SQLITE3_TEXT);
$stmt->bindValue(':lat', $data['lat'], SQLITE3_FLOAT);
$stmt->bindValue(':lng', $data['lng'], SQLITE3_FLOAT);
$stmt->bindValue(':address', $data['address'] ?? '', SQLITE3_TEXT);
$stmt->bindValue(':address_detail', $data['address_detail'] ?? '', SQLITE3_TEXT);
$stmt->bindValue(':installation_position', $data['installation_position'] ?? '', SQLITE3_TEXT);
$stmt->bindValue(':phone', $data['phone'] ?? '', SQLITE3_TEXT);
$stmt->bindValue(':phone_extension', $data['phone_extension'] ?? '', SQLITE3_TEXT);
$stmt->bindValue(':corporate_number', $data['corporate_number'] ?? '', SQLITE3_TEXT);
$stmt->bindValue(':organization_name', $data['organization_name'] ?? '', SQLITE3_TEXT);
$stmt->bindValue(':available_days', $data['available_days'] ?? '', SQLITE3_TEXT);
$stmt->bindValue(':start_time', $data['start_time'] ?? '', SQLITE3_TEXT);
$stmt->bindValue(':end_time', $data['end_time'] ?? '', SQLITE3_TEXT);
$stmt->bindValue(':available_hours_note', $data['available_hours_note'] ?? '', SQLITE3_TEXT);
$stmt->bindValue(':pediatric_support', $data['pediatric_support'] ?? '', SQLITE3_TEXT);
$stmt->bindValue(':website', $data['website'] ?? '', SQLITE3_TEXT);
$stmt->bindValue(':note', $data['note'] ?? '', SQLITE3_TEXT);
$stmt->bindValue(':category', $data['category'] ?? '', SQLITE3_TEXT);
$stmt->bindValue(':updated_at', $japanTime, SQLITE3_TEXT);
$result = $stmt->execute();

if ($result) {
    $facilityId = $db->lastInsertRowID();
    
    // 画像ファイルの処理
    if ($isFormData && isset($_FILES['images'])) {
        $uploadDir = __DIR__ . '/' . $config['storage']['images_dir'] . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $files = $_FILES['images'];
        $fileCount = is_array($files['name']) ? count($files['name']) : 1;
        
        // 最大10枚チェック
        if ($fileCount > 10) {
            http_response_code(400);
            echo json_encode(['error' => "{$config['app']['field_labels']['images']}は最大10枚まで登録可能です"]);
            exit;
        }
        
        for ($i = 0; $i < $fileCount; $i++) {
            $fileName = is_array($files['name']) ? $files['name'][$i] : $files['name'];
            $fileTmpName = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
            $fileSize = is_array($files['size']) ? $files['size'][$i] : $files['size'];
            $fileError = is_array($files['error']) ? $files['error'][$i] : $files['error'];
            
            if ($fileError === UPLOAD_ERR_OK) {
                // ファイルサイズチェック（5MB）
                if ($fileSize > 5 * 1024 * 1024) {
                    http_response_code(400);
                    echo json_encode(['error' => "{$config['app']['field_labels']['images']} {$fileName} のサイズが5MBを超えています"]);
                    exit;
                }
                
                // ファイル形式チェック
                $imageInfo = getimagesize($fileTmpName);
                if ($imageInfo === false) {
                    http_response_code(400);
                    echo json_encode(['error' => "{$config['app']['field_labels']['images']} {$fileName} は有効な{$config['app']['field_labels']['images']}ファイルではありません"]);
                    exit;
                }
                
                // ファイル名生成（重複防止）
                $extension = pathinfo($fileName, PATHINFO_EXTENSION);
                $newFileName = $facilityId . '_' . uniqid() . '.' . $extension;
                $filePath = $uploadDir . $newFileName;
                
                if (move_uploaded_file($fileTmpName, $filePath)) {
                    // データベースに画像情報を保存
                    $stmt = $db->prepare('INSERT INTO facility_images (facility_id, filename, original_name) VALUES (:facility_id, :filename, :original_name)');
                    $stmt->bindValue(':facility_id', $facilityId, SQLITE3_INTEGER);
                    $stmt->bindValue(':filename', $newFileName, SQLITE3_TEXT);
                    $stmt->bindValue(':original_name', $fileName, SQLITE3_TEXT);
                    $stmt->execute();
                }
            }
        }
    }
    
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => '登録に失敗しました']);
}
