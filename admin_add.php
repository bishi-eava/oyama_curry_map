<?php
// 管理者用施設登録画面
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

// 登録処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $result = processFacilityForm();
    $message = $result['message'];
    $messageType = $result['messageType'];
    
    // 成功時はフォームをクリア
    if ($result['success'] && $result['clearForm']) {
        $_POST = [];
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>新規<?= htmlspecialchars($config['app']['facility_name']) ?>登録 - <?= htmlspecialchars($config['app']['name']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="css/common.css" />
    <link rel="stylesheet" href="css/admin.css" />
</head>
<body>
    <div class="header">
        <h1>新規<?= htmlspecialchars($config['app']['facility_name']) ?>登録</h1>
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
                    緯度: <span id="currentLat"><?= $config['map']['initial_latitude'] ?></span><br>
                    経度: <span id="currentLng"><?= $config['map']['initial_longitude'] ?></span><br>
                    <button type="button" id="getCurrentLocationBtn" style="margin-top:0.5em; padding:0.5em 1em; font-size:0.9em;">現在位置に移動</button><br>
                    <small>※地図をクリックまたはドラッグして位置を調整してください</small>
                </div>
                
                <div class="form-group">
                    <label for="name"><?= htmlspecialchars($config['app']['field_labels']['name']) ?> *</label>
                    <input type="text" id="name" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="name_kana"><?= htmlspecialchars($config['app']['field_labels']['name_kana']) ?></label>
                    <input type="text" id="name_kana" name="name_kana" value="<?= htmlspecialchars($_POST['name_kana'] ?? '') ?>">
                </div>
                
                
                <div class="form-group">
                    <label for="category"><?= htmlspecialchars($config['app']['field_labels']['category']) ?> *</label>
                    <select id="category" name="category" required>
                        <option value="">カテゴリーを選択してください</option>
                        <?php foreach ($config['app']['categories'] as $category): ?>
                            <option value="<?= htmlspecialchars($category) ?>" <?= (($_POST['category'] ?? '') === $category) ? 'selected' : '' ?>><?= htmlspecialchars($category) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="address"><?= htmlspecialchars($config['app']['field_labels']['address']) ?></label>
                    <input type="text" id="address" name="address" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
                    <button type="button" id="getAddressBtn" style="margin-top:0.5em; padding:0.5em 1em; font-size:0.9em;">マーカー位置の住所を取得</button>
                </div>
                
                <div class="form-group">
                    <label for="address_detail"><?= htmlspecialchars($config['app']['field_labels']['address_detail']) ?></label>
                    <input type="text" id="address_detail" name="address_detail" value="<?= htmlspecialchars($_POST['address_detail'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="installation_position"><?= htmlspecialchars($config['app']['field_labels']['installation_position']) ?></label>
                    <input type="text" id="installation_position" name="installation_position" value="<?= htmlspecialchars($_POST['installation_position'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="phone"><?= htmlspecialchars($config['app']['field_labels']['phone']) ?></label>
                    <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" placeholder="(0285)23-1111">
                </div>
                
                <div class="form-group">
                    <label for="phone_extension"><?= htmlspecialchars($config['app']['field_labels']['phone_extension']) ?></label>
                    <input type="text" id="phone_extension" name="phone_extension" value="<?= htmlspecialchars($_POST['phone_extension'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="organization_name"><?= htmlspecialchars($config['app']['field_labels']['organization_name']) ?></label>
                    <input type="text" id="organization_name" name="organization_name" value="<?= htmlspecialchars($_POST['organization_name'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="available_days"><?= htmlspecialchars($config['app']['field_labels']['available_days']) ?></label>
                    <input type="text" id="available_days" name="available_days" value="<?= htmlspecialchars($_POST['available_days'] ?? '') ?>" placeholder="月火水木金">
                </div>
                
                <div class="form-group">
                    <label for="start_time"><?= htmlspecialchars($config['app']['field_labels']['start_time']) ?></label>
                    <input type="time" id="start_time" name="start_time" value="<?= htmlspecialchars(formatTimeForInput($_POST['start_time'] ?? '08:00')) ?>">
                </div>
                
                <div class="form-group">
                    <label for="end_time"><?= htmlspecialchars($config['app']['field_labels']['end_time']) ?></label>
                    <input type="time" id="end_time" name="end_time" value="<?= htmlspecialchars(formatTimeForInput($_POST['end_time'] ?? '18:00')) ?>">
                </div>
                
                <div class="form-group">
                    <label for="available_hours_note"><?= htmlspecialchars($config['app']['field_labels']['available_hours_note']) ?></label>
                    <textarea id="available_hours_note" name="available_hours_note" rows="2" maxlength="500" placeholder="祝日、年末年始を除く。"><?= htmlspecialchars($_POST['available_hours_note'] ?? '') ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="pediatric_support"><?= htmlspecialchars($config['app']['field_labels']['pediatric_support']) ?></label>
                    <select id="pediatric_support" name="pediatric_support">
                        <option value="有" <?= (($_POST['pediatric_support'] ?? '') === '有') ? 'selected' : '' ?>>有</option>
                        <option value="無" <?= (($_POST['pediatric_support'] ?? '無') === '無') ? 'selected' : '' ?>>無</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="website"><?= htmlspecialchars($config['app']['field_labels']['website']) ?></label>
                    <input type="url" id="website" name="website" value="<?= htmlspecialchars($_POST['website'] ?? '') ?>" placeholder="https://example.com">
                </div>
                
                <div class="form-group">
                    <label for="note"><?= htmlspecialchars($config['app']['field_labels']['note']) ?>（最大1000文字）</label>
                    <textarea id="note" name="note" rows="5" maxlength="1000" placeholder="備考情報や注意事項などを記入してください..."><?= htmlspecialchars($_POST['note'] ?? '') ?></textarea>
                    <div style="font-size:0.8em; color:#666; text-align:right; margin-top:0.3em;">
                        <span id="noteCount">0</span>/1000文字
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="images"><?= htmlspecialchars($config['app']['field_labels']['images']) ?>（最大10枚、1枚あたり5MBまで）</label>
                    <input type="file" id="images" name="images[]" multiple accept="image/*">
                    <div id="imagePreview"></div>
                </div>
                
                <input type="hidden" id="lat" name="lat" value="<?= $config['map']['initial_latitude'] ?>">
                <input type="hidden" id="lng" name="lng" value="<?= $config['map']['initial_longitude'] ?>">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                
                <button type="submit"><?= htmlspecialchars($config['app']['facility_name']) ?>を登録</button>
            </form>
        </div>
        
        <div class="map-section">
            <div id="map"></div>
        </div>
    </div>
    
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        // 地図初期化
        const map = L.map('map').setView([<?= $config['map']['initial_latitude'] ?>, <?= $config['map']['initial_longitude'] ?>], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);
        
        // 位置マーカー
        let marker = L.marker([<?= $config['map']['initial_latitude'] ?>, <?= $config['map']['initial_longitude'] ?>], { draggable: true }).addTo(map);
        
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
        
        // 画像プレビュー
        document.getElementById('images').onchange = function(e) {
            const files = e.target.files;
            const preview = document.getElementById('imagePreview');
            preview.innerHTML = '';
            
            if (files.length > 10) {
                alert(<?= json_encode($config['app']['field_labels']['images']) ?> + 'は最大10枚まで選択可能です');
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