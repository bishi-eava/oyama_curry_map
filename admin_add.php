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
                    <label for="description"><?= htmlspecialchars($config['app']['field_labels']['description']) ?></label>
                    <textarea id="description" name="description" rows="3" maxlength="500" placeholder="店舗の特徴や雰囲気などを記入してください..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    <div style="font-size:0.8em; color:#666; text-align:right; margin-top:0.3em;">
                        <span id="descriptionCount">0</span>/500文字
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="phone"><?= htmlspecialchars($config['app']['field_labels']['phone']) ?></label>
                    <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" placeholder="0285-20-1234">
                </div>
                
                <div class="form-group">
                    <label for="business_hours"><?= htmlspecialchars($config['app']['field_labels']['business_hours']) ?></label>
                    <input type="text" id="business_hours" name="business_hours" value="<?= htmlspecialchars($_POST['business_hours'] ?? '') ?>" placeholder="11:00-22:00">
                </div>
                
                <div class="form-group">
                    <label for="sns_account"><?= htmlspecialchars($config['app']['field_labels']['sns_account']) ?></label>
                    <input type="text" id="sns_account" name="sns_account" value="<?= htmlspecialchars($_POST['sns_account'] ?? '') ?>" placeholder="@username">
                </div>
                
                <div class="form-group">
                    <label for="website"><?= htmlspecialchars($config['app']['field_labels']['website']) ?></label>
                    <input type="url" id="website" name="website" value="<?= htmlspecialchars($_POST['website'] ?? '') ?>" placeholder="https://example.com">
                </div>
                
                <div class="form-group">
                    <label for="review"><?= htmlspecialchars($config['app']['field_labels']['review']) ?>（最大2000文字）</label>
                    <textarea id="review" name="review" rows="5" maxlength="2000" placeholder="店舗のレビューや詳細情報を記入してください..."><?= htmlspecialchars($_POST['review'] ?? '') ?></textarea>
                    <div style="font-size:0.8em; color:#666; text-align:right; margin-top:0.3em;">
                        <span id="reviewCount">0</span>/2000文字
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
                alert('<?= htmlspecialchars($config['app']['field_labels']['images']) ?>は最大10枚まで選択可能です');
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
        
        // 説明文字数カウント
        document.getElementById('description').addEventListener('input', function() {
            const count = this.value.length;
            document.getElementById('descriptionCount').textContent = count;
            
            if (count > 500) {
                document.getElementById('descriptionCount').style.color = 'red';
            } else {
                document.getElementById('descriptionCount').style.color = '#666';
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
            const review = document.getElementById('review');
            if (review.value) {
                document.getElementById('reviewCount').textContent = review.value.length;
            }
            
            const description = document.getElementById('description');
            if (description.value) {
                document.getElementById('descriptionCount').textContent = description.value.length;
            }
        };
    </script>
</body>
</html>