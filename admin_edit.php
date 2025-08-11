<?php
// 管理者用施設編集画面
require_once 'auth_check.php';
require_once 'facility_form_functions.php';

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
$images = getFacilityImages($db, $facilityId);

// 更新処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $result = processFacilityForm($facilityId);
    $message = $result['message'];
    $messageType = $result['messageType'];
    
    // 成功時はデータを再取得
    if ($result['success']) {
        // 更新後のデータを再取得
        $stmt = $db->prepare('SELECT * FROM facilities WHERE id = :id');
        $stmt->bindValue(':id', $facilityId, SQLITE3_INTEGER);
        $res = $stmt->execute();
        $facility = $res->fetchArray(SQLITE3_ASSOC);
        
        // 画像も再取得
        $images = getFacilityImages($db, $facilityId);
    }
}

// 画像削除処理
if (isset($_GET['delete_image'])) {
    $imageId = intval($_GET['delete_image']);
    $deleteMessage = deleteFacilityImage($db, $imageId, $facilityId, $config);
    
    if ($deleteMessage) {
        // 画像リストを再取得
        $images = getFacilityImages($db, $facilityId);
        
        $message = $deleteMessage;
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
                    <label for="name_kana"><?= htmlspecialchars($config['app']['field_labels']['name_kana']) ?></label>
                    <input type="text" id="name_kana" name="name_kana" value="<?= htmlspecialchars($facility['name_kana'] ?? '') ?>">
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
                    <label for="address_detail"><?= htmlspecialchars($config['app']['field_labels']['address_detail']) ?></label>
                    <input type="text" id="address_detail" name="address_detail" value="<?= htmlspecialchars($facility['address_detail'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="installation_position"><?= htmlspecialchars($config['app']['field_labels']['installation_position']) ?></label>
                    <input type="text" id="installation_position" name="installation_position" value="<?= htmlspecialchars($facility['installation_position'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="phone"><?= htmlspecialchars($config['app']['field_labels']['phone']) ?></label>
                    <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($facility['phone'] ?? '') ?>" placeholder="(0285)23-1111">
                </div>
                
                <div class="form-group">
                    <label for="phone_extension"><?= htmlspecialchars($config['app']['field_labels']['phone_extension']) ?></label>
                    <input type="text" id="phone_extension" name="phone_extension" value="<?= htmlspecialchars($facility['phone_extension'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="organization_name"><?= htmlspecialchars($config['app']['field_labels']['organization_name']) ?></label>
                    <input type="text" id="organization_name" name="organization_name" value="<?= htmlspecialchars($facility['organization_name'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="available_days"><?= htmlspecialchars($config['app']['field_labels']['available_days']) ?></label>
                    <input type="text" id="available_days" name="available_days" value="<?= htmlspecialchars($facility['available_days'] ?? '') ?>" placeholder="月火水木金">
                </div>
                
                <div class="form-group">
                    <label for="start_time"><?= htmlspecialchars($config['app']['field_labels']['start_time']) ?></label>
                    <input type="time" id="start_time" name="start_time" value="<?= htmlspecialchars(formatTimeForInput($facility['start_time'] ?? '')) ?>">
                </div>
                
                <div class="form-group">
                    <label for="end_time"><?= htmlspecialchars($config['app']['field_labels']['end_time']) ?></label>
                    <input type="time" id="end_time" name="end_time" value="<?= htmlspecialchars(formatTimeForInput($facility['end_time'] ?? '')) ?>">
                </div>
                
                <div class="form-group">
                    <label for="available_hours_note"><?= htmlspecialchars($config['app']['field_labels']['available_hours_note']) ?></label>
                    <textarea id="available_hours_note" name="available_hours_note" rows="2" maxlength="500" placeholder="祝日、年末年始を除く。"><?= htmlspecialchars($facility['available_hours_note'] ?? '') ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="pediatric_support"><?= htmlspecialchars($config['app']['field_labels']['pediatric_support']) ?></label>
                    
                    <?php
                    // 値を正規化：null、空文字、空白を「無」に変換
                    $pediatric_value = trim($facility['pediatric_support'] ?? '');
                    if ($pediatric_value === '' || $pediatric_value === null) {
                        $pediatric_value = '無';
                    }
                    ?>
                    
                    <select id="pediatric_support" name="pediatric_support">
                        <option value="有" <?= ($pediatric_value === '有') ? 'selected' : '' ?>>有</option>
                        <option value="無" <?= ($pediatric_value === '無') ? 'selected' : '' ?>>無</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="website"><?= htmlspecialchars($config['app']['field_labels']['website']) ?></label>
                    <input type="url" id="website" name="website" value="<?= htmlspecialchars($facility['website'] ?? '') ?>" placeholder="https://example.com">
                </div>
                
                <div class="form-group">
                    <label for="note"><?= htmlspecialchars($config['app']['field_labels']['note']) ?>（最大1000文字）</label>
                    <textarea id="note" name="note" rows="5" maxlength="1000" placeholder="備考情報や注意事項などを記入してください..."><?= htmlspecialchars($facility['note'] ?? '') ?></textarea>
                    <div style="font-size:0.8em; color:#666; text-align:right; margin-top:0.3em;">
                        <span id="noteCount">0</span>/1000文字
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
        
        // ノート文字数カウント
        document.getElementById('note').addEventListener('input', function() {
            const count = this.value.length;
            document.getElementById('noteCount').textContent = count;
            
            if (count > 1000) {
                document.getElementById('noteCount').style.color = 'red';
            } else {
                document.getElementById('noteCount').style.color = '#666';
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
            const note = document.getElementById('note');
            if (note.value) {
                document.getElementById('noteCount').textContent = note.value.length;
            }
        };
    </script>
</body>
</html>