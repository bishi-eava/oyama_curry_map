<?php
// 施設マップ設定ファイル
// このファイルはWeb外に配置されているため直接アクセス不可

// 直接アクセス防止
if (!defined('CONFIG_ACCESS_ALLOWED')) {
    die('Direct access to this file is not allowed.');
}

return [
    // データベース設定
    'database' => [
        'path' => __DIR__ . '/curry_shops.db',
        'tables' => [
            'shops' => [
                'columns' => [
                    'id' => 'INTEGER PRIMARY KEY AUTOINCREMENT',
                    'name' => 'TEXT NOT NULL',
                    'lat' => 'REAL NOT NULL',
                    'lng' => 'REAL NOT NULL',
                    'address' => 'TEXT',
                    'description' => 'TEXT',
                    'phone' => 'TEXT',
                    'website' => 'TEXT',
                    'business_hours' => 'TEXT',
                    'sns_account' => 'TEXT',
                    'category' => 'TEXT',
                    'review' => 'TEXT',
                    'updated_at' => 'DATETIME DEFAULT CURRENT_TIMESTAMP'
                ]
            ],
            'shop_images' => [
                'columns' => [
                    'id' => 'INTEGER PRIMARY KEY AUTOINCREMENT',
                    'shop_id' => 'INTEGER NOT NULL',
                    'filename' => 'TEXT NOT NULL',
                    'original_name' => 'TEXT NOT NULL',
                    'created_at' => 'DATETIME DEFAULT CURRENT_TIMESTAMP'
                ],
                'foreign_keys' => [
                    'shop_id' => [
                        'references' => 'shops(id)',
                        'on_delete' => 'CASCADE'
                    ]
                ]
            ],
            'admin_settings' => [
                'columns' => [
                    'id' => 'INTEGER PRIMARY KEY AUTOINCREMENT',
                    'setting_key' => 'TEXT UNIQUE NOT NULL',
                    'setting_value' => 'TEXT NOT NULL',
                    'created_at' => 'DATETIME DEFAULT CURRENT_TIMESTAMP',
                    'updated_at' => 'DATETIME DEFAULT CURRENT_TIMESTAMP'
                ]
            ]
        ]
    ],
    
    // 管理者設定
    'admin' => [
        'password' => 'admin123',  // 初期パスワード（初回設定後に変更推奨）
        'session_timeout' => 1800  // 30分（秒）
    ],
    
    // アプリケーション設定
    'app' => [
        'name' => 'おやまカレーマップ',
        'version' => '1.0.0',
        'timezone' => 'Asia/Tokyo',
        'facility_name' => '店舗'  // 施設の呼称（店舗、施設、お店など）
    ],
    
    // 地図設定
    'map' => [
        'initial_latitude' => 36.3141,   // 初期表示緯度（小山市中心）
        'initial_longitude' => 139.8006, // 初期表示経度（小山市中心）
        'initial_zoom' => 14             // 初期ズームレベル
    ],
    
    // セキュリティ設定
    'security' => [
        'max_image_size' => 5 * 1024 * 1024,  // 5MB
        'max_images_per_shop' => 10,
        'max_review_length' => 2000
    ]
];
?>