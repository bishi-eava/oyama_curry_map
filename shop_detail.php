<?php
// 店舗詳細表示ページ
require_once 'auth_check.php';
$config = getConfig();

// 店舗IDのチェック
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('HTTP/1.0 404 Not Found');
    die('店舗が見つかりません。');
}

$shopId = intval($_GET['id']);

// データベースから店舗情報を取得
$db = getDatabase();
$stmt = $db->prepare('SELECT * FROM shops WHERE id = :id');
$stmt->bindValue(':id', $shopId, SQLITE3_INTEGER);
$result = $stmt->execute();
$shop = $result->fetchArray(SQLITE3_ASSOC);

if (!$shop) {
    header('HTTP/1.0 404 Not Found');
    die('店舗が見つかりません。');
}

// 店舗の画像を取得
$imageStmt = $db->prepare('SELECT filename, original_name FROM shop_images WHERE shop_id = :shop_id ORDER BY id');
$imageStmt->bindValue(':shop_id', $shopId, SQLITE3_INTEGER);
$imageRes = $imageStmt->execute();

$images = [];
while ($imageRow = $imageRes->fetchArray(SQLITE3_ASSOC)) {
    $images[] = [
        'filename' => $imageRow['filename'],
        'original_name' => $imageRow['original_name'],
        'url' => 'shop_images/' . $imageRow['filename']
    ];
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($shop['name']) ?> - <?= htmlspecialchars($config['app']['name']) ?></title>
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
    
    <div class="shop-detail-container">
        <div class="detail-section">
            <h2 class="shop-title"><?= htmlspecialchars($shop['name']) ?></h2>
            
            <div class="form-group">
                <span class="field-label">カテゴリー</span>
                <div class="readonly-field <?= empty(trim($shop['category'])) ? 'empty' : '' ?>">
                    <?= !empty(trim($shop['category'])) ? htmlspecialchars($shop['category']) : 'カテゴリー情報がありません' ?>
                </div>
            </div>
            
            <div class="form-group">
                <span class="field-label" style="font-weight: 700; color: #212529; font-size: 1.1em;">住所</span>
                <div class="readonly-field <?= empty(trim($shop['address'])) ? 'empty' : '' ?>" style="background: #f8f9fa; border: 1px solid #e9ecef; padding: 1em; border-radius: 6px; margin-bottom: 0.5em;">
                    <?= !empty(trim($shop['address'])) ? htmlspecialchars($shop['address']) : '住所情報がありません' ?>
                </div>
            </div>
            
            <div class="form-group">
                <span class="field-label">説明</span>
                <div class="readonly-field <?= empty(trim($shop['description'])) ? 'empty' : '' ?>">
                    <?= !empty(trim($shop['description'])) ? nl2br(htmlspecialchars($shop['description'])) : '説明がありません' ?>
                </div>
            </div>
            
            <div class="form-group">
                <span class="field-label">電話番号</span>
                <div class="readonly-field <?= empty(trim($shop['phone'])) ? 'empty' : '' ?>">
                    <?php if (!empty(trim($shop['phone']))): ?>
                        <a href="tel:<?= htmlspecialchars($shop['phone']) ?>"><?= htmlspecialchars($shop['phone']) ?></a>
                    <?php else: ?>
                        電話番号がありません
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="form-group">
                <span class="field-label">ウェブページ</span>
                <div class="readonly-field <?= empty(trim($shop['website'])) ? 'empty' : '' ?>">
                    <?php if (!empty(trim($shop['website']))): ?>
                        <a href="<?= htmlspecialchars($shop['website']) ?>" target="_blank"><?= htmlspecialchars($shop['website']) ?></a>
                    <?php else: ?>
                        ウェブページがありません
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="form-group">
                <span class="field-label">営業時間</span>
                <div class="readonly-field <?= empty(trim($shop['business_hours'])) ? 'empty' : '' ?>">
                    <?= !empty(trim($shop['business_hours'])) ? htmlspecialchars($shop['business_hours']) : '営業時間情報がありません' ?>
                </div>
            </div>
            
            <div class="form-group">
                <span class="field-label">SNSアカウント</span>
                <div class="readonly-field <?= empty(trim($shop['sns_account'])) ? 'empty' : '' ?>">
                    <?php if (!empty(trim($shop['sns_account']))): ?>
                        <?php
                        $snsAccount = trim($shop['sns_account']);
                        
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
                        SNSアカウントがありません
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (!empty(trim($shop['review']))): ?>
            <div class="form-group">
                <span class="field-label">レビュー・詳細説明</span>
                <div class="review-section">
                    <?= nl2br(htmlspecialchars($shop['review'])) ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($images)): ?>
            <div class="form-group">
                <span class="field-label">画像 (<?= count($images) ?>枚)</span>
                <div class="shop-images">
                    <?php foreach ($images as $image): ?>
                        <div class="shop-image" onclick="showImageModal('<?= htmlspecialchars($image['url']) ?>', '<?= htmlspecialchars($image['original_name']) ?>')">
                            <img src="<?= htmlspecialchars($image['url']) ?>" alt="<?= htmlspecialchars($image['original_name']) ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="form-group">
                <span class="field-label">最終更新日時</span>
                <div class="readonly-field">
                    <?= htmlspecialchars($shop['updated_at']) ?>
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
        const map = L.map('map').setView([<?= $shop['lat'] ?>, <?= $shop['lng'] ?>], 16);
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
        
        L.marker([<?= $shop['lat'] ?>, <?= $shop['lng'] ?>], {icon})
            .addTo(map)
            .bindPopup('<b><?= htmlspecialchars($shop['name']) ?></b>')
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