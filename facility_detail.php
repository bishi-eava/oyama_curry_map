<?php
// 施設詳細表示ページ
require_once 'auth_check.php';
$config = getConfig();

// 施設IDのチェック
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('HTTP/1.0 404 Not Found');
    die("{$config['app']['facility_name']}が見つかりません。");
}

$facilityId = intval($_GET['id']);

// データベースから施設情報を取得
$db = getDatabase();
$stmt = $db->prepare('SELECT * FROM facilities WHERE id = :id');
$stmt->bindValue(':id', $facilityId, SQLITE3_INTEGER);
$result = $stmt->execute();
$facility = $result->fetchArray(SQLITE3_ASSOC);

if (!$facility) {
    header('HTTP/1.0 404 Not Found');
    die("{$config['app']['facility_name']}が見つかりません。");
}

// 施設の画像を取得
$imageStmt = $db->prepare('SELECT filename, original_name FROM facility_images WHERE facility_id = :facility_id ORDER BY id');
$imageStmt->bindValue(':facility_id', $facilityId, SQLITE3_INTEGER);
$imageRes = $imageStmt->execute();

$images = [];
while ($imageRow = $imageRes->fetchArray(SQLITE3_ASSOC)) {
    $images[] = [
        'filename' => $imageRow['filename'],
        'original_name' => $imageRow['original_name'],
        'url' => $config['storage']['images_dir'] . '/' . $imageRow['filename']
    ];
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($facility['name']) ?> - <?= htmlspecialchars($config['app']['name']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="css/common.css" />
    <link rel="stylesheet" href="css/main.css" />
</head>
<body>
    <div class="header">
        <h1><?= htmlspecialchars($config['app']['name']) ?></h1>
        <div>
            <a href="index.php">地図に戻る</a>
        </div>
    </div>
    
    <div class="facility-detail-container">
        <div class="detail-section">
            <h2 class="facility-title"><?= htmlspecialchars($facility['name']) ?></h2>
            
            <div class="form-group">
                <span class="field-label"><?= htmlspecialchars($config['app']['field_labels']['category']) ?></span>
                <div class="readonly-field <?= empty(trim($facility['category'])) ? 'empty' : '' ?>">
                    <?= !empty(trim($facility['category'])) ? htmlspecialchars($facility['category']) : htmlspecialchars($config['app']['field_labels']['category']) . '情報がありません' ?>
                </div>
            </div>
            
            <div class="form-group">
                <span class="field-label" style="font-weight: 700; color: #212529; font-size: 1.1em;"><?= htmlspecialchars($config['app']['field_labels']['address']) ?></span>
                <div class="readonly-field <?= empty(trim($facility['address'])) ? 'empty' : '' ?>" style="background: #f8f9fa; border: 1px solid #e9ecef; padding: 1em; border-radius: 6px; margin-bottom: 0.5em;">
                    <?= !empty(trim($facility['address'])) ? htmlspecialchars($facility['address']) : htmlspecialchars($config['app']['field_labels']['address']) . '情報がありません' ?>
                </div>
            </div>
            
            <div class="form-group">
                <span class="field-label"><?= htmlspecialchars($config['app']['field_labels']['description']) ?></span>
                <div class="readonly-field <?= empty(trim($facility['description'])) ? 'empty' : '' ?>">
                    <?= !empty(trim($facility['description'])) ? nl2br(htmlspecialchars($facility['description'])) : htmlspecialchars($config['app']['field_labels']['description']) . 'がありません' ?>
                </div>
            </div>
            
            <div class="form-group">
                <span class="field-label"><?= htmlspecialchars($config['app']['field_labels']['phone']) ?></span>
                <div class="readonly-field <?= empty(trim($facility['phone'])) ? 'empty' : '' ?>">
                    <?php if (!empty(trim($facility['phone']))): ?>
                        <a href="tel:<?= htmlspecialchars($facility['phone']) ?>"><?= htmlspecialchars($facility['phone']) ?></a>
                    <?php else: ?>
                        <?= htmlspecialchars($config['app']['field_labels']['phone']) ?>がありません
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="form-group">
                <span class="field-label"><?= htmlspecialchars($config['app']['field_labels']['website']) ?></span>
                <div class="readonly-field <?= empty(trim($facility['website'])) ? 'empty' : '' ?>">
                    <?php if (!empty(trim($facility['website']))): ?>
                        <a href="<?= htmlspecialchars($facility['website']) ?>" target="_blank"><?= htmlspecialchars($facility['website']) ?></a>
                    <?php else: ?>
                        <?= htmlspecialchars($config['app']['field_labels']['website']) ?>がありません
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="form-group">
                <span class="field-label"><?= htmlspecialchars($config['app']['field_labels']['business_hours']) ?></span>
                <div class="readonly-field <?= empty(trim($facility['business_hours'])) ? 'empty' : '' ?>">
                    <?= !empty(trim($facility['business_hours'])) ? htmlspecialchars($facility['business_hours']) : htmlspecialchars($config['app']['field_labels']['business_hours']) . '情報がありません' ?>
                </div>
            </div>
            
            <div class="form-group">
                <span class="field-label"><?= htmlspecialchars($config['app']['field_labels']['sns_account']) ?></span>
                <div class="readonly-field <?= empty(trim($facility['sns_account'])) ? 'empty' : '' ?>">
                    <?php if (!empty(trim($facility['sns_account']))): ?>
                        <?php
                        $snsAccount = trim($facility['sns_account']);
                        
                        // 完全URLまたは@形式のみリンクとして処理
                        if (strpos($snsAccount, 'http') === 0) {
                            // 完全URL
                            echo '<a href="' . htmlspecialchars($snsAccount) . '" target="_blank">' . htmlspecialchars($snsAccount) . '</a>';
                        } elseif (strpos($snsAccount, '@') === 0) {
                            // Twitter @形式
                            $username = substr($snsAccount, 1);
                            $snsLink = "https://twitter.com/{$username}";
                            echo '<a href="' . htmlspecialchars($snsLink) . '" target="_blank">' . htmlspecialchars($snsAccount) . '</a>';
                        } else {
                            // その他はリンクなしで表示
                            echo htmlspecialchars($snsAccount);
                        }
                        ?>
                    <?php else: ?>
                        <?= htmlspecialchars($config['app']['field_labels']['sns_account']) ?>がありません
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (!empty(trim($facility['review']))): ?>
            <div class="form-group">
                <span class="field-label"><?= htmlspecialchars($config['app']['field_labels']['review']) ?></span>
                <div class="review-section">
                    <?= nl2br(htmlspecialchars($facility['review'])) ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($images)): ?>
            <div class="form-group">
                <span class="field-label"><?= htmlspecialchars($config['app']['field_labels']['images']) ?> (<?= count($images) ?>枚)</span>
                <div class="facility-images">
                    <?php foreach ($images as $image): ?>
                        <div class="facility-image" onclick="showImageModal('<?= htmlspecialchars($image['url']) ?>', '<?= htmlspecialchars($image['original_name']) ?>')">
                            <img src="<?= htmlspecialchars($image['url']) ?>" alt="<?= htmlspecialchars($image['original_name']) ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="form-group">
                <span class="field-label">最終更新日時</span>
                <div class="readonly-field">
                    <?= htmlspecialchars($facility['updated_at']) ?>
                </div>
            </div>
        </div>
        
        <div class="map-section">
            <div id="map"></div>
        </div>
    </div>
    
    <!-- 画像モーダル -->
    <div id="imageModal">
        <span class="close">&times;</span>
        <img id="modalImage" src="" alt="">
    </div>
    
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        // 地図の初期化
        const map = L.map('map').setView([<?= $facility['lat'] ?>, <?= $facility['lng'] ?>], 16);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);
        
        // 店舗位置マーカー
        const icon = L.icon({
            iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
            shadowSize: [41, 41]
        });
        
        L.marker([<?= $facility['lat'] ?>, <?= $facility['lng'] ?>], {icon})
            .addTo(map)
            .bindPopup('<b><?= htmlspecialchars($facility['name']) ?></b>')
            .openPopup();
        
        // 画像モーダル表示機能
        function showImageModal(imageUrl, imageName) {
            document.getElementById('modalImage').src = imageUrl;
            document.getElementById('modalImage').alt = imageName;
            document.getElementById('imageModal').style.display = 'block';
        }
        
        // モーダルを閉じる
        document.querySelector('#imageModal .close').onclick = function() {
            document.getElementById('imageModal').style.display = 'none';
        };
        
        // モーダル背景クリックで閉じる
        document.getElementById('imageModal').onclick = function(e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        };
        
        // ESCキーでモーダルを閉じる
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.getElementById('imageModal').style.display = 'none';
            }
        });
    </script>
</body>
</html>