<?php
// 管理者パスワード変更画面
require_once 'auth_check.php';

// 認証チェック
checkAuth();

// ログアウト処理
if (isset($_GET['logout'])) {
    doLogout();
}

$message = '';
$messageType = '';

// パスワード変更処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['current_password'])) {
    // CSRF対策
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $message = 'セキュリティエラーが発生しました。再度お試しください。';
        $messageType = 'error';
    } else {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        // 入力検証
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $message = 'すべての項目を入力してください';
            $messageType = 'error';
        } elseif ($newPassword !== $confirmPassword) {
            $message = '新しいパスワードと確認用パスワードが一致しません';
            $messageType = 'error';
        } elseif (strlen($newPassword) < 6) {
            $message = 'パスワードは6文字以上で入力してください';
            $messageType = 'error';
        } elseif (!verifyPassword($currentPassword)) {
            $message = '現在のパスワードが正しくありません';
            $messageType = 'error';
        } else {
            // パスワード更新
            if (updatePassword($newPassword)) {
                $message = 'パスワードを正常に変更しました';
                $messageType = 'success';
                
                // フォームクリア
                $_POST = [];
            } else {
                $message = 'パスワードの変更に失敗しました';
                $messageType = 'error';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>パスワード変更 - <?= htmlspecialchars($config['app']['name']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/common.css" />
    <link rel="stylesheet" href="css/admin.css" />
</head>
<body class="password-page">
    <div class="header">
        <h1>パスワード変更</h1>
        <div>
            <a href="admin.php">管理画面</a>
            <a href="?logout=1">ログアウト</a>
        </div>
    </div>
    
    <div class="center-container">
        <?php if ($message): ?>
            <div class="message <?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <div class="security-info">
            <h3>パスワード変更について</h3>
            <ul>
                <li>新しいパスワードは6文字以上で設定してください</li>
                <li>英数字と記号を組み合わせることを推奨します</li>
                <li>他のサービスで使用していないパスワードを設定してください</li>
                <li>変更後は新しいパスワードでログインしてください</li>
            </ul>
        </div>
        
        <form method="POST">
            <div class="form-group">
                <label for="current_password">現在のパスワード *</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>
            
            <div class="form-group">
                <label for="new_password">新しいパスワード *</label>
                <input type="password" id="new_password" name="new_password" required minlength="6">
            </div>
            
            <div class="form-group">
                <label for="confirm_password">新しいパスワード（確認） *</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
            </div>
            
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            
            <button type="submit" class="full-width">パスワードを変更</button>
            <button type="button" class="btn-secondary full-width" onclick="location.href='admin.php'">キャンセル</button>
        </form>
    </div>
    
    <script>
        // パスワード確認のリアルタイムチェック
        document.getElementById('confirm_password').addEventListener('input', function() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            
            if (newPassword && confirmPassword && newPassword !== confirmPassword) {
                this.style.borderColor = '#dc3545';
            } else {
                this.style.borderColor = '#ddd';
            }
        });
    </script>
</body>
</html>