<?php
// 設定ファイル読み込み
require_once 'auth_check.php';
$config = getConfig();
$appName = $config['app']['name'];
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($appName) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="css/common.css" />
    <link rel="stylesheet" href="css/main.css" />
</head>
<body>
    <div class="header">
        <h1><?= htmlspecialchars($appName) ?></h1>
        <div>
            <button id="filterBtn"><?= htmlspecialchars($config['app']['field_labels']['category']) ?>選択</button>
            <button id="locateBtn">現在位置に移動</button>
        </div>
    </div>
    
    <div class="map-container">
        <!-- カテゴリーフィルターメニュー -->
        <div id="categoryFilter">
            <div class="filter-content">
                <div class="filter-options">
                    <?php
                    $categoryColors = [
                        '#1e88e5', '#43a047', '#fdd835', '#8d6e63', '#e53935'
                    ];
                    foreach ($config['app']['categories'] as $index => $category):
                        $color = $categoryColors[$index % count($categoryColors)];
                    ?>
                        <label><input type="checkbox" value="<?= htmlspecialchars($category) ?>" checked> <span class="category-color" style="background:<?= $color ?>;"></span> <?= htmlspecialchars($category) ?></label>
                    <?php endforeach; ?>
                </div>
                <div class="filter-actions">
                    <button id="selectAllBtn">全選択</button>
                    <button id="deselectAllBtn">全解除</button>
                </div>
            </div>
        </div>
        
        <div id="map"></div>
    </div>
    
    <!-- 画像モーダル -->
    <div id="imageModal">
        <span class="close">&times;</span>
        <img id="modalImage" src="" alt="">
    </div>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
    // 地図の初期表示
    var map;
    function initMap(center) {
      map = L.map('map').setView(center, <?= $config['map']['initial_zoom'] ?>);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution: '&copy; OpenStreetMap contributors'
      }).addTo(map);
      // 中心に赤丸とドット
      var cross = L.divIcon({
        className: 'center-cross',
        html: '<div style="width:20px;height:20px;">'
          + '<div style="width:20px;height:20px;border:3px solid red;border-radius:50%;position:absolute;left:0;top:0;box-sizing:border-box;"></div>'
          + '<div style="width:4px;height:4px;background:red;border-radius:50%;position:absolute;left:8px;top:8px;"></div>'
          + '</div>',
        iconSize: [20,20],
        iconAnchor: [10,10]
      });
      window.centerMarker = L.marker(map.getCenter(), {icon: cross, interactive: false}).addTo(map);
      map.on('move', function() {
        window.centerMarker.setLatLng(map.getCenter());
      });
    }
    // 設定から初期座標を取得して地図を初期化
    initMap([<?= $config['map']['initial_latitude'] ?>, <?= $config['map']['initial_longitude'] ?>]);

    // 現在位置ボタン
    document.getElementById('locateBtn').onclick = function() {
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(pos) {
          map.setView([pos.coords.latitude, pos.coords.longitude], 15);
        }, function() {
          alert('現在位置の取得に失敗しました');
        });
      } else {
        alert('この端末では現在位置取得がサポートされていません');
      }
    };

    // グローバル変数
    let allFacilities = [];
    let markersLayer = L.layerGroup();
    
    // カテゴリーごとのマーカー色を定義
    const categoryColors = <?= json_encode(array_combine($config['app']['categories'], ['#1e88e5', '#43a047', '#fdd835', '#8d6e63', '#e53935'])) ?>;
    
    // カテゴリーに応じたマーカーアイコンを取得する関数
    function getMarkerIcon(category) {
      const color = categoryColors[category] || '<?= end($config['app']['categories']) === 'その他' ? '#e53935' : '#666666' ?>';
      
      // SVGでカスタムマーカーを作成
      const svgIcon = `
        <svg width="25" height="41" viewBox="0 0 25 41" xmlns="http://www.w3.org/2000/svg">
          <path d="M12.5 0C5.6 0 0 5.6 0 12.5c0 10.9 12.5 28.5 12.5 28.5S25 23.4 25 12.5C25 5.6 19.4 0 12.5 0z" 
                fill="${color}" stroke="#fff" stroke-width="2"/>
          <circle cx="12.5" cy="12.5" r="6" fill="#fff"/>
        </svg>`;
      
      return L.divIcon({
        html: svgIcon,
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        className: 'custom-marker'
      });
    }
    
    // 施設情報をAPIから取得してマーカー表示
    function loadFacilities() {
      fetch('api_facilities.php')
        .then(res => res.json())
        .then(data => {
          allFacilities = data;
          displayFacilities(data);
        });
    }
    
    // 施設データを地図に表示する関数
    function displayFacilities(facilities) {
      // 既存のマーカーをクリア
      markersLayer.clearLayers();
      
      facilities.forEach(facility => {
            // ポップアップ内容を構築
            let popupContent = `<b>${facility.name}</b>`;
            
            // カテゴリーがあれば表示
            if (facility.category && facility.category.trim() !== '') {
              const categoryColor = categoryColors[facility.category] || '<?= end($config['app']['categories']) === 'その他' ? '#e53935' : '#666666' ?>';
              popupContent += `<br><span style="background:${categoryColor}; color:#fff; padding:0.2em 0.5em; border-radius:3px; font-size:0.8em;">${facility.category}</span>`;
            }
            
            // 説明があれば表示
            if (facility.description && facility.description.trim() !== '') {
              popupContent += `<br><i>${facility.description}</i>`;
            }
            
            // 住所があれば表示
            if (facility.address && facility.address.trim() !== '') {
              popupContent += `<br>📍 ${facility.address}`;
            }
            
            // 電話番号があれば表示
            if (facility.phone && facility.phone.trim() !== '') {
              popupContent += `<br>📞 <a href="tel:${facility.phone}">${facility.phone}</a>`;
            }
            
            // 営業時間があれば表示
            if (facility.business_hours && facility.business_hours.trim() !== '') {
              popupContent += `<br>⏰ ${facility.business_hours}`;
            }
            
            // ウェブサイトがあれば表示
            if (facility.website && facility.website.trim() !== '') {
              popupContent += `<br>🌐 <a href="${facility.website}" target="_blank">ウェブサイト</a>`;
            }
            
            // SNSアカウントがあれば表示
            if (facility.sns_account && facility.sns_account.trim() !== '') {
              const snsAccount = facility.sns_account.trim();
              
              // 完全URLまたは@形式のみリンクとして処理
              if (snsAccount.startsWith('http')) {
                // 完全URL
                popupContent += `<br>📱 <a href="${snsAccount}" target="_blank">${facility.sns_account}</a>`;
              } else if (snsAccount.startsWith('@')) {
                // Twitter @形式
                const username = snsAccount.substring(1);
                const snsLink = `https://twitter.com/${username}`;
                popupContent += `<br>📱 <a href="${snsLink}" target="_blank">${facility.sns_account}</a>`;
              } else {
                // その他はリンクなしで表示
                popupContent += `<br>📱 ${facility.sns_account}`;
              }
            }
            
            // レビューがあれば表示
            if (facility.review && facility.review.trim() !== '') {
              const reviewText = facility.review.length > 150 ? facility.review.substring(0, 150) + '...' : facility.review;
              popupContent += `<br><div style="margin-top:0.5em; padding:0.5em; background:#f8f9fa; border-radius:3px; font-size:0.9em;">${reviewText.replace(/\n/g, '<br>')}</div>`;
            }
            
            // 画像があれば表示
            if (facility.images && facility.images.length > 0) {
              popupContent += '<br><div style="margin-top:0.5em;">';
              facility.images.forEach((image, index) => {
                if (index < 3) { // 最初の3枚のみ表示
                  popupContent += `<img src="${image.url}" style="width:60px;height:60px;object-fit:cover;margin:2px;border-radius:3px;" onclick="showImageModal('${image.url}', '${image.original_name}')">`;
                }
              });
              if (facility.images.length > 3) {
                popupContent += `<span style="font-size:0.8em;color:#666;">他${facility.images.length - 3}枚</span>`;
              }
              popupContent += '</div>';
            }
            
            // 詳細を見るボタンを追加
            const facilityName = <?= json_encode($config['app']['facility_name']) ?>;
            popupContent += `<br><div style="margin-top:1em; text-align:center;">
              <a href="facility_detail.php?id=${facility.id}" style="display:inline-block; padding:0.5em 1em; background:#f8b500; color:#fff; text-decoration:none; border-radius:4px; font-size:0.9em;">${facilityName}詳細を見る</a>
            </div>`;
            
            // カテゴリーに応じたマーカーアイコンを取得
            const markerIcon = getMarkerIcon(facility.category);
            
            // マーカーを作成してmarkersLayerに追加
            const marker = L.marker([facility.lat, facility.lng], {icon: markerIcon})
              .bindPopup(popupContent);
            markersLayer.addLayer(marker);
          });
          
          // markersLayerを地図に追加
          markersLayer.addTo(map);
    }
    // 地図初期化後にマーカー表示
    function waitMapAndLoad() {
      if (typeof map === 'undefined') {
        setTimeout(waitMapAndLoad, 200);
      } else {
        loadFacilities();
      }
    }
    waitMapAndLoad();

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
    
    // カテゴリーフィルター機能（トグル）
    document.getElementById('filterBtn').onclick = function() {
      const filterMenu = document.getElementById('categoryFilter');
      // getComputedStyleで実際の表示状態を取得
      const computedStyle = window.getComputedStyle(filterMenu);
      const isVisible = computedStyle.display === 'block';
      
      if (isVisible) {
        filterMenu.style.display = 'none';
      } else {
        filterMenu.style.display = 'block';
      }
    };
    
    // フィルター処理を独立した関数に分離
    function applyFilter() {
      const checkboxes = document.querySelectorAll('#categoryFilter input[type="checkbox"]');
      const selectedCategories = [];
      
      checkboxes.forEach(checkbox => {
        if (checkbox.checked) {
          selectedCategories.push(checkbox.value);
        }
      });
      
      // 選択されたカテゴリーの店舗のみをフィルター
      const lastCategory = <?= json_encode(end($config['app']['categories'])) ?>;
      const filteredFacilities = allFacilities.filter(facility => {
        return selectedCategories.includes(facility.category) || 
               (!facility.category && selectedCategories.includes(lastCategory));
      });
      
      // フィルターされた店舗を表示
      displayFacilities(filteredFacilities);
    }
    
    // 全選択ボタン
    document.getElementById('selectAllBtn').onclick = function() {
      const checkboxes = document.querySelectorAll('#categoryFilter input[type="checkbox"]');
      checkboxes.forEach(checkbox => checkbox.checked = true);
      applyFilter();
    };
    
    // 全解除ボタン
    document.getElementById('deselectAllBtn').onclick = function() {
      const checkboxes = document.querySelectorAll('#categoryFilter input[type="checkbox"]');
      checkboxes.forEach(checkbox => checkbox.checked = false);
      applyFilter();
    };
    
    // チェックボックスの変更時にフィルターを自動適用
    document.addEventListener('DOMContentLoaded', function() {
      const checkboxes = document.querySelectorAll('#categoryFilter input[type="checkbox"]');
      checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', applyFilter);
      });
    });
    </script>
</body>
</html>
