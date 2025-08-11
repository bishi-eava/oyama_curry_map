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
        $filename = 'facilities_' . date('Ymd_His') . '.csv';
        
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
        
        // CSVヘッダー行を出力（元のCSVファイルと同じ構成）
        $header = [
            '都道府県コード又は市区町村コード',
            'NO',
            '都道府県名',
            '市区町村名',
            '名称',
            '名称_カナ',
            '住所',
            '方書',
            '緯度',
            '経度',
            'アクセス方法',
            '電話番号',
            '内線番号',
            '法人番号',
            '団体名',
            '利用可能曜日',
            '開始時間',
            '終了時間',
            '利用可能日時特記事項',
            '小児対応設備の有無',
            'URL',
            '備考'
        ];
        
        fputcsv($output, $header);
        
        // facilitiesテーブルからデータを取得
        $query = "SELECT * FROM facilities ORDER BY updated_at DESC";
        $result = $db->query($query);
        
        if (!$result) {
            throw new Exception('Database query failed: ' . $db->lastErrorMsg());
        }
        
        // データ行を出力
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $csvRow = [
                '092088', // 都道府県コード（小山市固定）
                !empty($row['csv_no']) ? $row['csv_no'] : '', // NO
                '栃木県', // 都道府県名（固定）
                '小山市', // 市区町村名（固定）
                !empty($row['name']) ? $row['name'] : '', // 名称
                !empty($row['name_kana']) ? $row['name_kana'] : '', // 名称_カナ
                !empty($row['address']) ? $row['address'] : '', // 住所
                !empty($row['address_detail']) ? $row['address_detail'] : '', // 方書
                !empty($row['lat']) ? $row['lat'] : '', // 緯度
                !empty($row['lng']) ? $row['lng'] : '', // 経度
                !empty($row['installation_position']) ? $row['installation_position'] : '', // アクセス方法
                !empty($row['phone']) ? $row['phone'] : '', // 電話番号
                !empty($row['phone_extension']) ? $row['phone_extension'] : '', // 内線番号
                !empty($row['corporate_number']) ? $row['corporate_number'] : '', // 法人番号
                !empty($row['organization_name']) ? $row['organization_name'] : '', // 団体名
                !empty($row['available_days']) ? $row['available_days'] : '', // 利用可能曜日
                !empty($row['start_time']) ? $row['start_time'] : '', // 開始時間
                !empty($row['end_time']) ? $row['end_time'] : '', // 終了時間
                !empty($row['available_hours_note']) ? $row['available_hours_note'] : '', // 利用可能日時特記事項
                !empty($row['pediatric_support']) ? $row['pediatric_support'] : '', // 小児対応設備の有無
                !empty($row['website']) ? $row['website'] : '', // URL
                !empty($row['note']) ? $row['note'] : '' // 備考
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
                <li>列数: 22列（元のオープンデータCSVと同じ構成）</li>
            </ul>
        </div>
        
        <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #ffeaa7;">
            <h4>注意事項</h4>
            <ul>
                <li>出力されるCSVファイルには、データベースに登録されている全ての施設情報が含まれます</li>
                <li>個人情報や機密情報が含まれる場合は、取り扱いにご注意ください</li>
                <li>出力後のファイルは適切に管理してください</li>
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