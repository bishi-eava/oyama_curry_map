<?php
require_once 'auth_check.php';

$error = '';
$success = '';

// 既にログイン済みの場合は管理画面へリダイレクト
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin.php');
    exit;
}

// ログイン処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    $password = $_POST['password'];
    
    if (doLogin($password)) {
        header('Location: admin.php');
        exit;
    } else {
        $error = 'パスワードが正しくありません';
    }
}

// タイムアウトメッセージ
if (isset($_GET['timeout'])) {
    $error = 'セッションがタイムアウトしました。再度ログインしてください。';
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>管理者ログイン - <?= htmlspecialchars($config['app']['name']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/common.css" />
    <link rel="stylesheet" href="css/admin.css" />
</head>
<body class="login-page">
    <div class="login-container">
        <h1>管理者ログイン</h1>
        
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="password">パスワード</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="full-width">ログイン</button>
        </form>
        
        <div class="back-link">
            <a href="index.php">← 地図に戻る</a>
        </div>
    </div>
</body>
</html>