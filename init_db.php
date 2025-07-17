<?php
// データベース初期化＆サンプルデータ投入

// 設定ファイル読み込み
require_once 'auth_check.php';

// 管理者認証チェック
checkAuth();

$config = getConfig();

// シンプルな初期化チェック関数
function getFacilityCount($config) {
    try {
        $db = new SQLite3($config['database']['path']);
        
        // テーブルの存在確認
        $tableCheck = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='facilities'");
        if ($tableCheck && $tableCheck->fetchArray()) {
            // データ件数確認
            $result = $db->query("SELECT COUNT(*) as count FROM facilities");
            $row = $result->fetchArray();
            return $row['count'];
        }
        
        $db->close();
    } catch (Exception $e) {
        // DB接続エラーの場合は0を返す
    }
    
    return 0;
}

// 施設データの件数を取得
$facilityCount = getFacilityCount($config);
$hasData = ($facilityCount > 0);

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
    $newFacilityCount = getFacilityCount($config);
    
    if ($success) {
        echo "<div style='color: green; font-size: 1.2em; margin: 20px; padding: 20px; border: 2px solid green;'>";
        
        if ($initType === 'schema_only') {
            echo "<h3>✅ データベース構成更新完了</h3>";
            echo "<p>処理日時: " . htmlspecialchars($currentTime) . "</p>";
            echo "<p>処理内容: テーブル構造の更新（データ保持）</p>";
            echo "<p>施設データ: {$newFacilityCount} 件（保持）</p>";
            echo "<p>既存データを保持したまま、データベース構成を更新しました。</p>";
        } elseif ($initType === 'full_reset') {
            echo "<h3>✅ データベース初期化＆サンプルデータ投入完了</h3>";
            echo "<p>初期化日時: " . htmlspecialchars($currentTime) . "</p>";
            echo "<p>処理内容: 全データ削除 + サンプルデータ投入</p>";
            echo "<p>施設データ: {$newFacilityCount} 件（新規）</p>";
            echo "<p>データベースを完全にリセットし、サンプルデータで初期化しました。</p>";
        }
        
        echo "<p>管理者パスワード: <strong>" . htmlspecialchars($config['admin']['password']) . "</strong></p>";
        
        // テーブル構造の表示
        if (isset($_SESSION['table_structure'])) {
            echo "<div style='margin-top: 15px; background: #f8f9fa; padding: 10px; border-radius: 5px;'>";
            echo "<p><strong>📋 facilitiesテーブル構造:</strong></p>";
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
    echo "<p>現在 <strong>{$facilityCount} 件</strong> の施設データが登録されています。</p>";
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
    echo "<li>テーブルの作成（facilities, facility_images, admin_settings）</li>";
    echo "<li>サンプルデータの投入（3件の施設データ）</li>";
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

// 設定ファイル構造検証機能
function validateConfig($config) {
    $requiredKeys = ['database', 'app', 'admin'];
    foreach ($requiredKeys as $key) {
        if (!isset($config[$key])) {
            throw new Exception("Missing required config section: {$key}");
        }
    }
    
    // データベース設定の検証
    if (!isset($config['database']['tables'])) {
        throw new Exception("Missing database.tables configuration");
    }
    
    validateTableConfig($config);
}

function validateTableConfig($config) {
    $required = ['columns'];
    $tables = $config['database']['tables'];
    
    foreach ($tables as $tableName => $tableConfig) {
        foreach ($required as $key) {
            if (!isset($tableConfig[$key])) {
                throw new Exception("Missing {$key} in table {$tableName}");
            }
        }
        
        // カラム定義の検証
        if (empty($tableConfig['columns'])) {
            throw new Exception("Table {$tableName} has no columns defined");
        }
        
        // 外部キー制約の検証
        if (isset($tableConfig['foreign_keys'])) {
            foreach ($tableConfig['foreign_keys'] as $fkName => $fkConfig) {
                if (!isset($fkConfig['references'])) {
                    throw new Exception("Foreign key {$fkName} in table {$tableName} missing references");
                }
            }
        }
    }
}

// テーブル構造読み込み用ヘルパー関数
function getTableSchema($config, $tableName) {
    if (!isset($config['database']['tables'][$tableName])) {
        $available = implode(', ', array_keys($config['database']['tables']));
        throw new Exception("Table '{$tableName}' not found in configuration. Available tables: {$available}");
    }
    
    $table = $config['database']['tables'][$tableName];
    $columns = $table['columns'];
    
    // CREATE TABLE文の生成
    $sql = "CREATE TABLE IF NOT EXISTS {$tableName} (\n";
    $columnDefinitions = [];
    
    foreach ($columns as $columnName => $columnType) {
        $columnDefinitions[] = "        {$columnName} {$columnType}";
    }
    
    $sql .= implode(",\n", $columnDefinitions);
    
    // 外部キー制約の追加
    if (isset($table['foreign_keys'])) {
        foreach ($table['foreign_keys'] as $keyName => $keyDef) {
            $sql .= ",\n        FOREIGN KEY ({$keyName}) REFERENCES {$keyDef['references']}";
            if (isset($keyDef['on_delete'])) {
                $sql .= " ON DELETE {$keyDef['on_delete']}";
            }
        }
    }
    
    $sql .= "\n    )";
    
    return $sql;
}

// インデックス作成用ヘルパー関数
function createTableIndexes($config, $tableName, $db) {
    if (!isset($config['database']['tables'][$tableName]['indexes'])) {
        return; // インデックス定義がない場合は何もしない
    }
    
    $indexes = $config['database']['tables'][$tableName]['indexes'];
    
    foreach ($indexes as $indexName => $columns) {
        $columnList = implode(', ', $columns);
        $sql = "CREATE INDEX IF NOT EXISTS {$indexName} ON {$tableName} ({$columnList})";
        
        try {
            $db->exec($sql);
        } catch (Exception $e) {
            error_log("Failed to create index {$indexName}: " . $e->getMessage());
        }
    }
}

// テーブル削除用ヘルパー関数
function dropAllTables($config, $db) {
    // 設定ファイルから削除順序を取得
    $dropOrder = $config['database']['drop_order'] ?? array_keys($config['database']['tables']);
    
    foreach ($dropOrder as $tableName) {
        try {
            $db->exec("DROP TABLE IF EXISTS {$tableName}");
        } catch (Exception $e) {
            error_log("Failed to drop table {$tableName}: " . $e->getMessage());
        }
    }
}

// 構成のみ更新関数（データ保持）
function updateDatabaseSchema($config) {
    // 設定ファイルの検証
    validateConfig($config);
    
    $db = new SQLite3($config['database']['path']);
    
    // 設定からテーブル構造を取得してテーブル作成
    $facilitiesTableSQL = getTableSchema($config, 'facilities');
    $db->exec($facilitiesTableSQL);
    
    // 既存テーブルに新しいカラムを追加（設定ファイルベース）
    $facilityColumns = $config['database']['tables']['facilities']['columns'];
    
    foreach ($facilityColumns as $columnName => $columnType) {
        // idカラムはスキップ（既存のPRIMARY KEYのため）
        if ($columnName === 'id') {
            continue;
        }
        
        // カラム存在チェック
        $checkResult = $db->query("PRAGMA table_info(facilities)");
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
                $result = $db->exec("ALTER TABLE facilities ADD COLUMN {$columnName} {$columnType}");
                if ($result === false) {
                    error_log("Failed to add column {$columnName}: " . $db->lastErrorMsg());
                } else {
                    // updated_atカラムを追加した場合、既存レコードに日本時間を設定
                    if ($columnName === 'updated_at') {
                        $japanTime = date('Y-m-d H:i:s', time());
                        $db->exec("UPDATE facilities SET updated_at = '{$japanTime}' WHERE updated_at IS NULL");
                    }
                }
            } catch (Exception $e) {
                error_log("Exception adding column {$columnName}: " . $e->getMessage());
            }
        }
    }
    
    // 他のテーブルも設定から作成
    $facilityImagesTableSQL = getTableSchema($config, 'facility_images');
    $db->exec($facilityImagesTableSQL);
    
    $adminSettingsTableSQL = getTableSchema($config, 'admin_settings');
    $db->exec($adminSettingsTableSQL);
    
    // 全テーブルのインデックスを作成
    createTableIndexes($config, 'facilities', $db);
    createTableIndexes($config, 'facility_images', $db);
    createTableIndexes($config, 'admin_settings', $db);
    
    // テーブル構造の確認結果を取得
    $tableInfo = [];
    $result = $db->query("PRAGMA table_info(facilities)");
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
    // 設定ファイルの検証
    validateConfig($config);
    
    $db = new SQLite3($config['database']['path']);
    
    // テーブルを削除（設定ファイルベース）
    dropAllTables($config, $db);
    
    // 既存の画像ファイルも削除
    $imageDir = __DIR__ . '/' . $config['storage']['images_dir'] . '/';
    if (is_dir($imageDir)) {
        $files = glob($imageDir . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
    
    // テーブル再作成（設定ファイルから）
    $tables = array_keys($config['database']['tables']);
    foreach ($tables as $tableName) {
        $tableSQL = getTableSchema($config, $tableName);
        $db->exec($tableSQL);
        
        // インデックスも作成
        createTableIndexes($config, $tableName, $db);
    }
    
    // サンプルデータ（設定ファイルから取得）
    $facilities = $config['sample_data'];
    
    foreach ($facilities as $facility) {
        $stmt = $db->prepare('INSERT INTO facilities (name, lat, lng, address, description, phone, website, business_hours, sns_account, category) VALUES (:name, :lat, :lng, :address, :description, :phone, :website, :business_hours, :sns_account, :category)');
        $stmt->bindValue(':name', $facility['name'], SQLITE3_TEXT);
        $stmt->bindValue(':lat', $facility['lat'], SQLITE3_FLOAT);
        $stmt->bindValue(':lng', $facility['lng'], SQLITE3_FLOAT);
        $stmt->bindValue(':address', $facility['address'], SQLITE3_TEXT);
        $stmt->bindValue(':description', $facility['description'], SQLITE3_TEXT);
        $stmt->bindValue(':phone', $facility['phone'], SQLITE3_TEXT);
        $stmt->bindValue(':website', $facility['website'], SQLITE3_TEXT);
        $stmt->bindValue(':business_hours', $facility['business_hours'], SQLITE3_TEXT);
        $stmt->bindValue(':sns_account', $facility['sns_account'], SQLITE3_TEXT);
        $stmt->bindValue(':category', $facility['category'], SQLITE3_TEXT);
        $stmt->execute();
    }
    
    $db->close();
    return true;
}

