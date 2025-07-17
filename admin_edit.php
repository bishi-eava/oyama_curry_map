<?php
// 管理者用施設編集画面
require_once 'auth_check.php';

// 認証チェック
checkAuth();

// ログアウト処理
if (isset($_GET['logout'])) {
    doLogout();
}

$message = '';
$messageType = '';
$facility = null;
$images = [];

// 施設IDの取得
$facilityId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$facilityId) {
    header('Location: admin.php');
    exit;
}

$db = getDatabase();

// 施設情報の取得
$stmt = $db->prepare('SELECT * FROM facilities WHERE id = :id');
$stmt->bindValue(':id', $facilityId, SQLITE3_INTEGER);
$res = $stmt->execute();
$facility = $res->fetchArray(SQLITE3_ASSOC);

if (!$facility) {
    header('Location: admin.php');
    exit;
}

// 既存画像の取得
$stmt = $db->prepare('SELECT id, filename, original_name FROM facility_images WHERE facility_id = :facility_id ORDER BY id');
$stmt->bindValue(':facility_id', $facilityId, SQLITE3_INTEGER);
$res = $stmt->execute();
while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    $images[] = $row;
}

// 更新処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    // CSRF対策
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $message = 'セキュリティエラーが発生しました。再度お試しください。';
        $messageType = 'error';
    } else {
        $name = mb_substr($_POST['name'], 0, 50);
        $address = mb_substr($_POST['address'] ?? '', 0, 50);
        $description = $_POST['description'] ?? '';
        $phone = mb_substr($_POST['phone'] ?? '', 0, 50);
        $website = mb_substr($_POST['website'] ?? '', 0, 50);
        $business_hours = mb_substr($_POST['business_hours'] ?? '', 0, 50);
        $sns_account = mb_substr($_POST['sns_account'] ?? '', 0, 50);
        $category = $_POST['category'] ?? '';
        $review = $_POST['review'] ?? '';
        $lat = floatval($_POST['lat']);
        $lng = floatval($_POST['lng']);
        
        // 説明の文字数チェック
        if (mb_strlen($description) > 2000) {
            $message = "{$config['app']['field_labels']['description']}は2000文字以内で入力してください";
            $messageType = 'error';
        } elseif (mb_strlen($review) > 2000) {
            $message = "{$config['app']['field_labels']['review']}は2000文字以内で入力してください";
            $messageType = 'error';
        } else {
        
        if (!empty($name) && $lat && $lng && !empty($category)) {
            // 同じ名前の施設が既に存在するかチェック（自分以外）
            $stmt = $db->prepare('SELECT COUNT(*) as cnt FROM facilities WHERE name = :name AND id != :id');
            $stmt->bindValue(':name', $name, SQLITE3_TEXT);
            $stmt->bindValue(':id', $facilityId, SQLITE3_INTEGER);
            $res = $stmt->execute();
            $row = $res->fetchArray(SQLITE3_ASSOC);
            
            if ($row['cnt'] > 0) {
                $message = "同じ名前の{$config['app']['facility_name']}が既に登録されています";
                $messageType = 'error';
            } else {
                // 施設情報を更新（日本時間でupdated_atを設定）
                $japanTime = date('Y-m-d H:i:s', time());
                $stmt = $db->prepare('UPDATE facilities SET name = :name, lat = :lat, lng = :lng, address = :address, description = :description, phone = :phone, website = :website, business_hours = :business_hours, sns_account = :sns_account, category = :category, review = :review, updated_at = :updated_at WHERE id = :id');
                $stmt->bindValue(':name', $name, SQLITE3_TEXT);
                $stmt->bindValue(':lat', $lat, SQLITE3_FLOAT);
                $stmt->bindValue(':lng', $lng, SQLITE3_FLOAT);
                $stmt->bindValue(':address', $address, SQLITE3_TEXT);
                $stmt->bindValue(':description', $description, SQLITE3_TEXT);
                $stmt->bindValue(':phone', $phone, SQLITE3_TEXT);
                $stmt->bindValue(':website', $website, SQLITE3_TEXT);
                $stmt->bindValue(':business_hours', $business_hours, SQLITE3_TEXT);
                $stmt->bindValue(':sns_account', $sns_account, SQLITE3_TEXT);
                $stmt->bindValue(':category', $category, SQLITE3_TEXT);
                $stmt->bindValue(':review', $review, SQLITE3_TEXT);
                $stmt->bindValue(':updated_at', $japanTime, SQLITE3_TEXT);
                $stmt->bindValue(':id', $facilityId, SQLITE3_INTEGER);
                $result = $stmt->execute();
                
                if ($result) {
                    // 新しい画像の処理
                    if (isset($_FILES['new_images']) && $_FILES['new_images']['error'][0] !== UPLOAD_ERR_NO_FILE) {
                        $uploadDir = __DIR__ . '/' . $config['storage']['images_dir'] . '/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        }
                        
                        $files = $_FILES['new_images'];
                        $fileCount = count($files['name']);
                        
                        // 既存画像数と合わせて10枚以下かチェック
                        $currentImageCount = count($images);
                        if (($currentImageCount + $fileCount) <= 10) {
                            for ($i = 0; $i < $fileCount; $i++) {
                                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                                    $fileName = $files['name'][$i];
                                    $fileTmpName = $files['tmp_name'][$i];
                                    $fileSize = $files['size'][$i];
                                    
                                    // ファイルサイズチェック（5MB）
                                    if ($fileSize <= 5 * 1024 * 1024) {
                                        // ファイル形式チェック
                                        $imageInfo = getimagesize($fileTmpName);
                                        if ($imageInfo !== false) {
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
                            }
                        } else {
                            $message = "{$config['app']['field_labels']['images']}は合計で最大10枚まで登録可能です";
                            $messageType = 'error';
                        }
                    }
                    
                    if ($messageType !== 'error') {
                        $message = "{$config['app']['facility_name']}情報を正常に更新しました";
                        $messageType = 'success';
                        
                        // 更新後のデータを再取得
                        $stmt = $db->prepare('SELECT * FROM facilities WHERE id = :id');
                        $stmt->bindValue(':id', $facilityId, SQLITE3_INTEGER);
                        $res = $stmt->execute();
                        $facility = $res->fetchArray(SQLITE3_ASSOC);
                        
                        // 画像も再取得
                        $images = [];
                        $stmt = $db->prepare('SELECT id, filename, original_name FROM facility_images WHERE facility_id = :facility_id ORDER BY id');
                        $stmt->bindValue(':facility_id', $facilityId, SQLITE3_INTEGER);
                        $res = $stmt->execute();
                        while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
                            $images[] = $row;
                        }
                    }
                } else {
                    $message = "{$config['app']['facility_name']}情報の更新に失敗しました";
                    $messageType = 'error';
                }
            }
        } else {
            $message = '必要な項目が入力されていません';
            $messageType = 'error';
        }
        } // レビュー文字数チェックのelse文終了
    }
}

// 画像削除処理
if (isset($_GET['delete_image'])) {
    $imageId = intval($_GET['delete_image']);
    
    $stmt = $db->prepare('SELECT filename FROM facility_images WHERE id = :id AND facility_id = :facility_id');
    $stmt->bindValue(':id', $imageId, SQLITE3_INTEGER);
    $stmt->bindValue(':facility_id', $facilityId, SQLITE3_INTEGER);
    $res = $stmt->execute();
    $imageRow = $res->fetchArray(SQLITE3_ASSOC);
    
    if ($imageRow) {
        // ファイルを削除
        $filePath = __DIR__ . '/' . $config['storage']['images_dir'] . '/' . $imageRow['filename'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        // データベースから削除
        $db->exec("DELETE FROM facility_images WHERE id = $imageId");
        
        // 画像リストを再取得
        $images = [];
        $stmt = $db->prepare('SELECT id, filename, original_name FROM facility_images WHERE facility_id = :facility_id ORDER BY id');
        $stmt->bindValue(':facility_id', $facilityId, SQLITE3_INTEGER);
        $res = $stmt->execute();
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
            $images[] = $row;
        }
        
        $message = "{$config['app']['field_labels']['images']}を削除しました";
        $messageType = 'success';
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($config['app']['facility_name']) ?>編集 - <?= htmlspecialchars($config['app']['name']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="css/common.css" />
    <link rel="stylesheet" href="css/admin.css" />
</head>
<body>
    <div class="header">
        <h1><?= htmlspecialchars($config['app']['facility_name']) ?>編集: <?= htmlspecialchars($facility['name']) ?></h1>
        <div>
            <a href="admin.php">管理画面</a>
            <a href="?logout=1">ログアウト</a>
        </div>
    </div>
    
    <div class="container">
        <div class="form-section">
            <?php if ($message): ?>
                <div class="message <?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="coord-info">
                    <strong>座標情報:</strong><br>
                    緯度: <span id="currentLat"><?= htmlspecialchars($facility['lat']) ?></span><br>
                    経度: <span id="currentLng"><?= htmlspecialchars($facility['lng']) ?></span><br>
                    <button type="button" id="getCurrentLocationBtn" style="margin-top:0.5em; padding:0.5em 1em; font-size:0.9em;">現在位置に移動</button><br>
                    <small>※地図をクリックまたはドラッグして位置を調整してください</small>
                </div>
                
                <div class="form-group">
                    <label for="name"><?= htmlspecialchars($config['app']['field_labels']['name']) ?> *</label>
                    <input type="text" id="name" name="name" required value="<?= htmlspecialchars($facility['name']) ?>">
                </div>
                
                <div class="form-group">
                    <label for="category"><?= htmlspecialchars($config['app']['field_labels']['category']) ?> *</label>
                    <select id="category" name="category" required>
                        <option value="">カテゴリーを選択してください</option>
                        <?php foreach ($config['app']['categories'] as $category): ?>
                            <option value="<?= htmlspecialchars($category) ?>" <?= ($facility['category'] === $category) ? 'selected' : '' ?>><?= htmlspecialchars($category) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="address"><?= htmlspecialchars($config['app']['field_labels']['address']) ?></label>
                    <input type="text" id="address" name="address" value="<?= htmlspecialchars($facility['address']) ?>">
                    <button type="button" id="getAddressBtn" style="margin-top:0.5em; padding:0.5em 1em; font-size:0.9em;">マーカー位置の住所を取得</button>
                </div>
                
                <div class="form-group">
                    <label for="description"><?= htmlspecialchars($config['app']['field_labels']['description']) ?>（最大2000文字）</label>
                    <textarea id="description" name="description" rows="3" maxlength="2000" placeholder="施設の簡単な説明を入力してください"><?= htmlspecialchars($facility['description'] ?? '') ?></textarea>
                    <div style="font-size:0.8em; color:#666; text-align:right; margin-top:0.3em;">
                        <span id="descriptionCount">0</span>/2000文字
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="phone"><?= htmlspecialchars($config['app']['field_labels']['phone']) ?></label>
                    <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($facility['phone'] ?? '') ?>" placeholder="03-1234-5678">
                </div>
                
                <div class="form-group">
                    <label for="website"><?= htmlspecialchars($config['app']['field_labels']['website']) ?></label>
                    <input type="url" id="website" name="website" value="<?= htmlspecialchars($facility['website'] ?? '') ?>" placeholder="https://example.com">
                </div>
                
                <div class="form-group">
                    <label for="business_hours"><?= htmlspecialchars($config['app']['field_labels']['business_hours']) ?></label>
                    <input type="text" id="business_hours" name="business_hours" value="<?= htmlspecialchars($facility['business_hours'] ?? '') ?>" placeholder="11:00-21:00">
                </div>
                
                <div class="form-group">
                    <label for="sns_account"><?= htmlspecialchars($config['app']['field_labels']['sns_account']) ?></label>
                    <input type="text" id="sns_account" name="sns_account" value="<?= htmlspecialchars($facility['sns_account'] ?? '') ?>" placeholder="@example_account">
                </div>
                
                <div class="form-group">
                    <label for="review"><?= htmlspecialchars($config['app']['field_labels']['review']) ?>（最大2000文字）</label>
                    <textarea id="review" name="review" rows="5" maxlength="2000" placeholder="<?= htmlspecialchars($config['app']['facility_name']) ?>の特徴、雰囲気、おすすめメニューなどを詳しく記入してください..."><?= htmlspecialchars($facility['review'] ?? '') ?></textarea>
                    <div style="font-size:0.8em; color:#666; text-align:right; margin-top:0.3em;">
                        <span id="reviewCount">0</span>/2000文字
                    </div>
                </div>
                
                <div class="form-group">
                    <label>既存の<?= htmlspecialchars($config['app']['field_labels']['images']) ?></label>
                    <div class="existing-images">
                        <?php if (empty($images)): ?>
                            <p style="color:#999;"><?= htmlspecialchars($config['app']['field_labels']['images']) ?>がありません</p>
                        <?php else: ?>
                            <?php foreach ($images as $image): ?>
                                <div class="image-item">
                                    <img src="<?= htmlspecialchars($config['storage']['images_dir']) ?>/<?= htmlspecialchars($image['filename']) ?>" 
                                         title="<?= htmlspecialchars($image['original_name']) ?>">
                                    <button type="button" class="delete-btn" 
                                            onclick="deleteImage(<?= $image['id'] ?>)">×</button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="new_images">新しい<?= htmlspecialchars($config['app']['field_labels']['images']) ?>を追加（現在<?= count($images) ?>枚、最大10枚まで）</label>
                    <input type="file" id="new_images" name="new_images[]" multiple accept="image/*">
                    <div id="newImagePreview"></div>
                </div>
                
                <input type="hidden" id="lat" name="lat" value="<?= htmlspecialchars($facility['lat']) ?>">
                <input type="hidden" id="lng" name="lng" value="<?= htmlspecialchars($facility['lng']) ?>">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                
                <button type="submit">更新</button>
                <button type="button" class="btn-secondary" onclick="location.href='admin.php'">キャンセル</button>
            </form>
        </div>
        
        <div class="map-section">
            <div id="map"></div>
        </div>
    </div>
    
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        // 地図初期化
        const initialLat = <?= $facility['lat'] ?>;
        const initialLng = <?= $facility['lng'] ?>;
        const map = L.map('map').setView([initialLat, initialLng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);
        
        // 位置マーカー
        let marker = L.marker([initialLat, initialLng], { draggable: true }).addTo(map);
        
        // マーカードラッグ時の処理
        marker.on('dragend', function(e) {
            const position = e.target.getLatLng();
            updateCoordinates(position.lat, position.lng);
        });
        
        // 地図クリック時の処理
        map.on('click', function(e) {
            marker.setLatLng(e.latlng);
            updateCoordinates(e.latlng.lat, e.latlng.lng);
        });
        
        // 座標更新
        function updateCoordinates(lat, lng) {
            document.getElementById('lat').value = lat;
            document.getElementById('lng').value = lng;
            document.getElementById('currentLat').textContent = lat.toFixed(6);
            document.getElementById('currentLng').textContent = lng.toFixed(6);
        }
        
        // 住所取得
        document.getElementById('getAddressBtn').onclick = function() {
            const lat = document.getElementById('lat').value;
            const lng = document.getElementById('lng').value;
            
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&accept-language=ja`)
                .then(res => res.json())
                .then(data => {
                    const addr = data.address;
                    const address = [
                        addr.state || '',
                        addr.city || addr.town || addr.village || '',
                        addr.county || '',
                        addr.suburb || '',
                        addr.neighbourhood || '',
                        addr.hamlet || '',
                        addr.quarter || '',
                        addr.block || '',
                        addr.building || '',
                        addr.house_number || '',
                        addr.unit || ''
                    ].filter(Boolean).join('');
                    document.getElementById('address').value = address;
                });
        };
        
        // 画像削除
        function deleteImage(imageId) {
            if (confirm('この' + <?= json_encode($config['app']['field_labels']['images']) ?> + 'を削除しますか？')) {
                location.href = '?id=<?= $facilityId ?>&delete_image=' + imageId;
            }
        }
        
        // 新しい画像のプレビュー
        document.getElementById('new_images').onchange = function(e) {
            const files = e.target.files;
            const preview = document.getElementById('newImagePreview');
            preview.innerHTML = '';
            
            const currentImageCount = <?= count($images) ?>;
            if ((currentImageCount + files.length) > 10) {
                alert(<?= json_encode($config['app']['field_labels']['images']) ?> + 'は合計で最大10枚まで選択可能です');
                e.target.value = '';
                return;
            }
            
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                if (file.size > 5 * 1024 * 1024) {
                    alert(`${file.name} のサイズが5MBを超えています`);
                    e.target.value = '';
                    preview.innerHTML = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    preview.appendChild(img);
                };
                reader.readAsDataURL(file);
            }
        };
        
        // 説明文字数カウント
        document.getElementById('description').addEventListener('input', function() {
            const count = this.value.length;
            document.getElementById('descriptionCount').textContent = count;
            
            if (count > 2000) {
                document.getElementById('descriptionCount').style.color = 'red';
            } else {
                document.getElementById('descriptionCount').style.color = '#666';
            }
        });
        
        // レビュー文字数カウント
        document.getElementById('review').addEventListener('input', function() {
            const count = this.value.length;
            document.getElementById('reviewCount').textContent = count;
            
            if (count > 2000) {
                document.getElementById('reviewCount').style.color = 'red';
            } else {
                document.getElementById('reviewCount').style.color = '#666';
            }
        });
        
        // 現在位置ボタンの機能
        document.getElementById('getCurrentLocationBtn').onclick = function() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    // 地図を現在位置に移動
                    map.setView([lat, lng], 16);
                    
                    // マーカーを現在位置に移動
                    marker.setLatLng([lat, lng]);
                    
                    // 座標情報を更新
                    updateCoordinates(lat, lng);
                }, function(error) {
                    let errorMessage = '現在位置の取得に失敗しました';
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            errorMessage = '位置情報へのアクセスが拒否されました';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMessage = '位置情報が利用できません';
                            break;
                        case error.TIMEOUT:
                            errorMessage = '位置情報の取得がタイムアウトしました';
                            break;
                    }
                    alert(errorMessage);
                });
            } else {
                alert('この端末では現在位置取得がサポートされていません');
            }
        };
        
        // 初期表示時の文字数カウント
        window.onload = function() {
            const description = document.getElementById('description');
            if (description.value) {
                document.getElementById('descriptionCount').textContent = description.value.length;
            }
            
            const review = document.getElementById('review');
            if (review.value) {
                document.getElementById('reviewCount').textContent = review.value.length;
            }
        };
    </script>
</body>
</html>