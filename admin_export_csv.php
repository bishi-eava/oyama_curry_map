<?php
// CSV出力管理ページ
require_once 'auth_check.php';

// 認証チェック
checkAuth();

$db = getDatabase();

// CSRFトークン生成
$csrf_token = generateCSRFToken();

// CSRFトークン検証
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        die('CSRF token validation failed.');
    }
}

// CSV出力処理
if (isset($_POST['export']) || isset($_GET['export'])) {
    try {
        // ファイル名生成（日時付き）
        $filename = 'oyama_curry_map_' . date('Ymd_His') . '.csv';
        
        // HTTPヘッダー設定
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // 出力バッファをクリア
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // CSV出力を開始
        $output = fopen('php://output', 'w');
        
        // CSVヘッダー行を出力（カレーマップ用の構成）
        $header = [
            '店舗名',
            '緯度', 
            '経度',
            '住所',
            '説明',
            '電話番号',
            'ウェブサイト',
            '営業時間',
            'SNSアカウント',
            'カテゴリ',
            'レビュー・詳細'
        ];
        
        fputcsv($output, $header);
        
        // facilitiesテーブルからカレー店用フィールドのみ取得
        $query = "SELECT name, lat, lng, address, description, phone, website, business_hours, sns_account, category, review FROM facilities ORDER BY updated_at DESC";
        $result = $db->query($query);
        
        if (!$result) {
            throw new Exception('Database query failed: ' . $db->lastErrorMsg());
        }
        
        // データ行を出力（カレーマップ用のフィールド構成）
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            // config.phpのfield_mappingの順序に完全一致
            $csvRow = [
                $row['name'] ?? '',          // 0: 店舗名
                $row['lat'] ?? '',           // 1: 緯度
                $row['lng'] ?? '',           // 2: 経度
                $row['address'] ?? '',       // 3: 住所
                $row['description'] ?? '',   // 4: 説明
                $row['phone'] ?? '',         // 5: 電話番号
                $row['website'] ?? '',       // 6: ウェブサイト
                $row['business_hours'] ?? '', // 7: 営業時間
                $row['sns_account'] ?? '',   // 8: SNSアカウント
                $row['category'] ?? '',      // 9: カテゴリ
                $row['review'] ?? ''         // 10: レビュー・詳細
            ];
            
            fputcsv($output, $csvRow);
        }
        
        fclose($output);
        exit;
        
    } catch (Exception $e) {
        // エラー処理
        error_log('CSV Export Error: ' . $e->getMessage());
        header('HTTP/1.1 500 Internal Server Error');
        die('CSV出力中にエラーが発生しました: ' . htmlspecialchars($e->getMessage()));
    }
}

// データ件数を取得
$countQuery = "SELECT COUNT(*) as count FROM facilities";
$countResult = $db->query($countQuery);
$count = 0;
if ($countResult) {
    $countRow = $countResult->fetchArray(SQLITE3_ASSOC);
    $count = $countRow['count'];
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>CSV出力 - <?= htmlspecialchars($config['app']['facility_name']) ?>管理</title>
    <link rel="stylesheet" href="css/common.css" />
    <link rel="stylesheet" href="css/admin.css" />
</head>
<body>
    <div class="header">
        <h1>CSV出力 - <?= htmlspecialchars($config['app']['facility_name']) ?>管理</h1>
        <div>
            <a href="admin.php">管理画面に戻る</a>
            <a href="index.php">地図に戻る</a>
            <a href="admin.php?logout=1">ログアウト</a>
        </div>
    </div>
    
    <div style="max-width: 800px; margin: 20px auto; padding: 20px;">
        <h2>CSV出力</h2>
        
        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <h3>出力内容</h3>
            <ul>
                <li>対象データ: facilitiesテーブルの全データ</li>
                <li>データ件数: <strong><?= $count ?></strong> 件</li>
                <li>ファイル形式: CSV（UTF-8）</li>
                <li>ファイル名: facilities_YYYYMMDD_HHMMSS.csv</li>
                <li>列数: 11列（カレー店マップ用の構成）</li>
                <li>出力項目: 店舗名、緯度、経度、住所、説明、電話番号、ウェブサイト、営業時間、SNSアカウント、カテゴリ、レビュー・詳細</li>
            </ul>
        </div>
        
        <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #ffeaa7;">
            <h4>注意事項</h4>
            <ul>
                <li>出力されるCSVファイルには、データベースに登録されている全ての店舗情報が含まれます</li>
                <li>このCSVファイルは、データベース初期化画面（init_db.php）でのCSVインポートに使用できます</li>
                <li>出力後のファイルは適切に管理してください</li>
                <li>CSVファイルの編集時は、列数と列順序を変更しないでください</li>
            </ul>
        </div>
        
        <?php if ($count > 0): ?>
        <form method="post" style="text-align: center;">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <button type="submit" name="export" value="1" 
                    style="background: #007bff; color: white; padding: 12px 30px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer;"
                    onclick="return confirm('<?= $count ?> 件のデータをCSVファイルとして出力します。よろしいですか？');">
                CSV出力を実行
            </button>
        </form>
        
        <p style="text-align: center; margin-top: 15px; color: #666; font-size: 14px;">
            ※ ボタンをクリックするとCSVファイルのダウンロードが開始されます
        </p>
        <?php else: ?>
        <div style="text-align: center; color: #dc3545;">
            <p>出力対象のデータがありません。</p>
            <p><a href="admin_add.php">新規施設登録</a>から施設を追加してください。</p>
        </div>
        <?php endif; ?>
        
    </div>
</body>
</html>