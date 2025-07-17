<?php
// データベース初期化＆サンプルデータ投入

// 設定ファイル読み込み
require_once 'auth_check.php';

// 管理者認証チェック
checkAuth();

$config = getConfig();

// シンプルな初期化チェック関数
function getShopCount($config) {
    try {
        $db = new SQLite3($config['database']['path']);
        
        // テーブルの存在確認
        $tableCheck = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='shops'");
        if ($tableCheck && $tableCheck->fetchArray()) {
            // データ件数確認
            $result = $db->query("SELECT COUNT(*) as count FROM shops");
            $row = $result->fetchArray();
            return $row['count'];
        }
        
        $db->close();
    } catch (Exception $e) {
        // DB接続エラーの場合は0を返す
    }
    
    return 0;
}

// 店舗データの件数を取得
$shopCount = getShopCount($config);
$hasData = ($shopCount > 0);

// 処理実行部分（POST送信時）
if (isset($_POST['init_type'])) {
    // CSRF対策
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        echo "<div style='color: red; margin: 20px; padding: 20px; border: 2px solid red;'>";
        echo "<h3>❌ セキュリティエラー</h3>";
        echo "<p>CSRFトークンが無効です。再度お試しください。</p>";
        echo "</div>";
        exit;
    }
    
    // 選択されたタイプに応じて処理を実行
    $initType = $_POST['init_type'];
    $success = false;
    
    if ($initType === 'schema_only') {
        $success = updateDatabaseSchema($config);
    } elseif ($initType === 'full_reset') {
        $success = resetDatabaseWithSampleData($config);
    }
    
    // 処理結果に応じた完了メッセージ（この後に選択画面は表示されない）
    $currentTime = date('Y-m-d H:i:s');
    $newShopCount = getShopCount($config);
    
    if ($success) {
        echo "<div style='color: green; font-size: 1.2em; margin: 20px; padding: 20px; border: 2px solid green;'>";
        
        if ($initType === 'schema_only') {
            echo "<h3>✅ データベース構成更新完了</h3>";
            echo "<p>処理日時: " . htmlspecialchars($currentTime) . "</p>";
            echo "<p>処理内容: テーブル構造の更新（データ保持）</p>";
            echo "<p>店舗データ: {$newShopCount} 件（保持）</p>";
            echo "<p>既存データを保持したまま、データベース構成を更新しました。</p>";
        } elseif ($initType === 'full_reset') {
            echo "<h3>✅ データベース初期化＆サンプルデータ投入完了</h3>";
            echo "<p>初期化日時: " . htmlspecialchars($currentTime) . "</p>";
            echo "<p>処理内容: 全データ削除 + サンプルデータ投入</p>";
            echo "<p>店舗データ: {$newShopCount} 件（新規）</p>";
            echo "<p>データベースを完全にリセットし、サンプルデータで初期化しました。</p>";
        }
        
        echo "<p>管理者パスワード: <strong>" . htmlspecialchars($config['admin']['password']) . "</strong></p>";
        
        // テーブル構造の表示
        if (isset($_SESSION['table_structure'])) {
            echo "<div style='margin-top: 15px; background: #f8f9fa; padding: 10px; border-radius: 5px;'>";
            echo "<p><strong>📋 shopsテーブル構造:</strong></p>";
            echo "<ul style='margin: 5px 0; padding-left: 20px;'>";
            foreach ($_SESSION['table_structure'] as $column) {
                echo "<li>" . htmlspecialchars($column) . "</li>";
            }
            echo "</ul>";
            echo "</div>";
            unset($_SESSION['table_structure']); // 表示後に削除
        }
        
        echo "<div style='margin-top: 15px; color: #d63384;'>";
        echo "<p><strong>⚠️ 重要な注意事項:</strong></p>";
        echo "<ul>";
        echo "<li>パスワードは config.php ファイルで管理されています</li>";
        echo "<li>管理画面からパスワード変更が可能です</li>";
        echo "<li>セキュリティのため、このファイルを本番環境から削除することを推奨します</li>";
        echo "</ul>";
        echo "</div>";
        
        echo "<div style='margin-top: 15px;'>";
        echo "<a href='admin.php' style='background: #0d6efd; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>管理画面へ</a>";
        echo "<a href='index.php' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-left: 10px;'>地図へ</a>";
        echo "</div>";
        echo "</div>";
        
    } else {
        echo "<div style='color: red; font-size: 1.2em; margin: 20px; padding: 20px; border: 2px solid red;'>";
        echo "<h3>❌ 処理に失敗しました</h3>";
        echo "<p>データベースの初期化処理中にエラーが発生しました。</p>";
        echo "<p>ログを確認して問題を解決してください。</p>";
        echo "<div style='margin-top: 15px;'>";
        echo "<button onclick='history.back()' style='background: gray; color: white; padding: 10px 20px; border: none; cursor: pointer; border-radius: 5px;'>戻る</button>";
        echo "</div>";
        echo "</div>";
    }
    
    // 完了メッセージ表示後は処理終了（選択画面は表示しない）
    exit;
}

// 初期化タイプ選択画面
if ($hasData) {
    // データが存在する場合：2つのオプションを提供
    echo "<div style='font-size: 1.2em; margin: 20px; padding: 20px; border: 2px solid #ffc107; background: #fff9c4;'>";
    echo "<h3>⚠️ データベースに既存データがあります</h3>";
    echo "<p>現在 <strong>{$shopCount} 件</strong> の店舗データが登録されています。</p>";
    echo "<p>以下のどちらかを選択してください：</p>";
    echo "</div>";
    
    echo "<form method='POST' style='margin: 20px;'>";
    echo "<div style='margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px;'>";
    echo "<label style='display: block; cursor: pointer;'>";
    echo "<input type='radio' name='init_type' value='schema_only' required style='margin-right: 10px;'>";
    echo "<strong>構成のみ更新（データ保持）</strong>";
    echo "</label>";
    echo "<p style='margin: 10px 0 0 25px; color: #666; font-size: 0.9em;'>";
    echo "既存データを保持したまま、テーブル構造のみ更新<br>";
    echo "新機能対応やバージョンアップ時に使用";
    echo "</p>";
    echo "</div>";
    
    echo "<div style='margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px;'>";
    echo "<label style='display: block; cursor: pointer;'>";
    echo "<input type='radio' name='init_type' value='full_reset' required style='margin-right: 10px;'>";
    echo "<strong>全削除して初期化（サンプルデータのみ）</strong>";
    echo "</label>";
    echo "<p style='margin: 10px 0 0 25px; color: #666; font-size: 0.9em;'>";
    echo "全データを削除してサンプルデータで初期化<br>";
    echo "開発・テスト用や完全リセット時に使用";
    echo "</p>";
    echo "</div>";
    
    echo "<input type='hidden' name='csrf_token' value='" . generateCSRFToken() . "'>";
    echo "<button type='submit' style='background: #0d6efd; color: white; padding: 10px 20px; border: none; cursor: pointer; border-radius: 5px; margin-right: 10px;'>実行</button>";
    echo "<button type='button' onclick='history.back()' style='background: gray; color: white; padding: 10px 20px; border: none; cursor: pointer; border-radius: 5px;'>キャンセル</button>";
    echo "</form>";
    
} else {
    // データが存在しない場合：サンプルデータ投入のみ
    echo "<div style='color: blue; font-size: 1.2em; margin: 20px; padding: 20px; border: 2px solid blue;'>";
    echo "<h3>🚀 データベースの初期化を実行します</h3>";
    echo "<p>以下の処理を実行します：</p>";
    echo "<ul>";
    echo "<li>テーブルの作成（shops, shop_images, admin_settings）</li>";
    echo "<li>サンプルデータの投入（3件の店舗データ）</li>";
    echo "</ul>";
    echo "<p>この操作は元に戻すことができません。実行してもよろしいですか？</p>";
    echo "<form method='POST' style='margin-top: 15px;'>";
    echo "<input type='hidden' name='init_type' value='full_reset'>";
    echo "<input type='hidden' name='csrf_token' value='" . generateCSRFToken() . "'>";
    echo "<button type='submit' style='background: blue; color: white; padding: 10px 20px; border: none; cursor: pointer; border-radius: 5px; margin-right: 10px;'>初期化実行</button>";
    echo "<button type='button' onclick='history.back()' style='background: gray; color: white; padding: 10px 20px; border: none; cursor: pointer; border-radius: 5px;'>キャンセル</button>";
    echo "</form>";
    echo "</div>";
}

// 構成のみ更新関数（データ保持）
function updateDatabaseSchema($config) {
    $db = new SQLite3($config['database']['path']);
    
    // テーブル作成（既存データは保持）
    $db->exec('CREATE TABLE IF NOT EXISTS shops (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        lat REAL NOT NULL,
        lng REAL NOT NULL,
        address TEXT,
        description TEXT,
        phone TEXT,
        website TEXT,
        business_hours TEXT,
        sns_account TEXT,
        category TEXT,
        review TEXT,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )');
    
    // 既存テーブルに新しいカラムを追加（カラム存在チェック付き）
    $columns = [
        'description' => 'TEXT',
        'phone' => 'TEXT',
        'website' => 'TEXT',
        'business_hours' => 'TEXT',
        'sns_account' => 'TEXT',
        'category' => 'TEXT',
        'updated_at' => 'DATETIME'
    ];
    
    foreach ($columns as $columnName => $columnType) {
        // カラム存在チェック
        $checkResult = $db->query("PRAGMA table_info(shops)");
        $columnExists = false;
        while ($row = $checkResult->fetchArray()) {
            if ($row['name'] === $columnName) {
                $columnExists = true;
                break;
            }
        }
        
        // カラムが存在しない場合のみ追加
        if (!$columnExists) {
            try {
                $result = $db->exec("ALTER TABLE shops ADD COLUMN {$columnName} {$columnType}");
                if ($result === false) {
                    error_log("Failed to add column {$columnName}: " . $db->lastErrorMsg());
                } else {
                    // updated_atカラムを追加した場合、既存レコードに日本時間を設定
                    if ($columnName === 'updated_at') {
                        $japanTime = date('Y-m-d H:i:s', time());
                        $db->exec("UPDATE shops SET updated_at = '{$japanTime}' WHERE updated_at IS NULL");
                    }
                }
            } catch (Exception $e) {
                error_log("Exception adding column {$columnName}: " . $e->getMessage());
            }
        }
    }
    
    $db->exec('CREATE TABLE IF NOT EXISTS shop_images (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        shop_id INTEGER NOT NULL,
        filename TEXT NOT NULL,
        original_name TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (shop_id) REFERENCES shops (id) ON DELETE CASCADE
    )');
    
    $db->exec('CREATE TABLE IF NOT EXISTS admin_settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        setting_key TEXT UNIQUE NOT NULL,
        setting_value TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )');
    
    // テーブル構造の確認結果を取得
    $tableInfo = [];
    $result = $db->query("PRAGMA table_info(shops)");
    while ($row = $result->fetchArray()) {
        $tableInfo[] = $row['name'] . ' (' . $row['type'] . ')';
    }
    
    $db->close();
    
    // テーブル構造をセッションに保存（完了画面で表示するため）
    $_SESSION['table_structure'] = $tableInfo;
    
    return true;
}

// 全削除初期化関数（サンプルデータのみ）
function resetDatabaseWithSampleData($config) {
    $db = new SQLite3($config['database']['path']);
    
    // テーブルを削除（既存データクリア）
    $db->exec('DROP TABLE IF EXISTS shop_images');
    $db->exec('DROP TABLE IF EXISTS shops');
    $db->exec('DROP TABLE IF EXISTS admin_settings');
    
    // 既存の画像ファイルも削除
    $imageDir = __DIR__ . '/shop_images/';
    if (is_dir($imageDir)) {
        $files = glob($imageDir . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
    
    // テーブル再作成
    $db->exec('CREATE TABLE IF NOT EXISTS shops (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        lat REAL NOT NULL,
        lng REAL NOT NULL,
        address TEXT,
        description TEXT,
        phone TEXT,
        website TEXT,
        business_hours TEXT,
        sns_account TEXT,
        category TEXT,
        review TEXT,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )');
    
    $db->exec('CREATE TABLE IF NOT EXISTS shop_images (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        shop_id INTEGER NOT NULL,
        filename TEXT NOT NULL,
        original_name TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (shop_id) REFERENCES shops (id) ON DELETE CASCADE
    )');
    
    $db->exec('CREATE TABLE IF NOT EXISTS admin_settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        setting_key TEXT UNIQUE NOT NULL,
        setting_value TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )');
    
    // サンプルデータ（小山市内のカレーショップ例）
    $shops = [
        [
            'name' => 'カレーショップA',
            'lat' => 36.3141,
            'lng' => 139.8006,
            'address' => '小山市中央町1-1-1',
            'description' => '地元で愛される老舗カレーショップ',
            'phone' => '0285-12-3456',
            'website' => 'https://curry-shop-a.example.com',
            'business_hours' => '11:00-21:00',
            'sns_account' => '@curry_shop_a',
            'category' => '日本式カレー'
        ],
        [
            'name' => 'カレーショップB',
            'lat' => 36.3085,
            'lng' => 139.8062,
            'address' => '小山市駅東通り2-2-2',
            'description' => '駅近で便利な本格カレー店',
            'phone' => '0285-23-4567',
            'website' => 'https://curry-shop-b.example.com',
            'business_hours' => '11:30-22:00',
            'sns_account' => '@curry_shop_b',
            'category' => '欧風カレー'
        ],
        [
            'name' => 'カレーショップC',
            'lat' => 36.3120,
            'lng' => 139.7970,
            'address' => '小山市城山町3-3-3',
            'description' => '手作りスパイスの本格インドカレー',
            'phone' => '0285-34-5678',
            'website' => 'https://curry-shop-c.example.com',
            'business_hours' => '11:00-15:00, 17:00-21:00',
            'sns_account' => '@curry_shop_c',
            'category' => 'インドカレー'
        ],
    ];
    
    foreach ($shops as $shop) {
        $stmt = $db->prepare('INSERT INTO shops (name, lat, lng, address, description, phone, website, business_hours, sns_account, category) VALUES (:name, :lat, :lng, :address, :description, :phone, :website, :business_hours, :sns_account, :category)');
        $stmt->bindValue(':name', $shop['name'], SQLITE3_TEXT);
        $stmt->bindValue(':lat', $shop['lat'], SQLITE3_FLOAT);
        $stmt->bindValue(':lng', $shop['lng'], SQLITE3_FLOAT);
        $stmt->bindValue(':address', $shop['address'], SQLITE3_TEXT);
        $stmt->bindValue(':description', $shop['description'], SQLITE3_TEXT);
        $stmt->bindValue(':phone', $shop['phone'], SQLITE3_TEXT);
        $stmt->bindValue(':website', $shop['website'], SQLITE3_TEXT);
        $stmt->bindValue(':business_hours', $shop['business_hours'], SQLITE3_TEXT);
        $stmt->bindValue(':sns_account', $shop['sns_account'], SQLITE3_TEXT);
        $stmt->bindValue(':category', $shop['category'], SQLITE3_TEXT);
        $stmt->execute();
    }
    
    $db->close();
    return true;
}

