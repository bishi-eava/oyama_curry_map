<?php
// 共通認証機能

session_start();

// config.phpのパスを取得する関数（環境に応じて自動検索）
function getConfigPath() {
    $searchPaths = [
        __DIR__ . '/../../app_db/oyama_curry_map/config.php',        // さくらサーバー用
        __DIR__ . '/../../../app_db/oyama_curry_map/config.php',     // ローカル用
        __DIR__ . '/app_db/oyama_curry_map/config.php',              // その他
        __DIR__ . '/../app_db/oyama_curry_map/config.php',           // 別パターン
    ];
    
    foreach ($searchPaths as $path) {
        if (file_exists($path)) {
            return $path;
        }
    }
    
    throw new Exception('Config file not found. Searched paths: ' . implode(', ', $searchPaths));
}

// config設定を取得する関数
function getConfig() {
    static $config = null;
    if ($config === null) {
        define('CONFIG_ACCESS_ALLOWED', true);
        $config = require_once getConfigPath();
    }
    return $config;
}

// 設定ファイル読み込み
$config = getConfig();

// 認証チェック関数
function checkAuth() {
    global $config;
    
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: login.php');
        exit;
    }
    
    // セッションタイムアウトチェック
    $timeout = $config['admin']['session_timeout'];
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
        session_destroy();
        header('Location: login.php?timeout=1');
        exit;
    }
    
    $_SESSION['last_activity'] = time();
}

// パスワード検証関数
function verifyPassword($inputPassword) {
    global $config;
    
    // 設定ファイルから直接パスワードを確認
    $adminPassword = $config['admin']['password'];
    return $inputPassword === $adminPassword;
}

// ログイン処理
function doLogin($password) {
    if (verifyPassword($password)) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['last_activity'] = time();
        $_SESSION['login_time'] = time();
        return true;
    }
    return false;
}

// ログアウト処理
function doLogout() {
    session_destroy();
    header('Location: login.php');
    exit;
}

// CSRFトークン生成
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// CSRFトークン検証
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// データベース接続取得
function getDatabase() {
    global $config;
    return new SQLite3($config['database']['path']);
}

// パスワード更新関数
function updatePassword($newPassword) {
    global $config;
    
    // 設定ファイルのパスワードを更新
    $configPath = getConfigPath();
    $configContent = file_get_contents($configPath);
    
    // パスワード行を置換
    $pattern = "/'password' => '[^']*'/";
    $replacement = "'password' => '" . addslashes($newPassword) . "'";
    $newContent = preg_replace($pattern, $replacement, $configContent);
    
    if ($newContent && file_put_contents($configPath, $newContent)) {
        // メモリ上の設定も更新
        $config['admin']['password'] = $newPassword;
        return true;
    }
    return false;
}