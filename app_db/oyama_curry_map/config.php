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
        'path' => __DIR__ . '/facilities.db',
        'tables' => [
            'facilities' => [
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
                ],
                'indexes' => [
                    'idx_facilities_location' => ['lat', 'lng'],
                    'idx_facilities_updated_at' => ['updated_at'],
                    'idx_facilities_category' => ['category']
                ]
            ],
            'facility_images' => [
                'columns' => [
                    'id' => 'INTEGER PRIMARY KEY AUTOINCREMENT',
                    'facility_id' => 'INTEGER NOT NULL',
                    'filename' => 'TEXT NOT NULL',
                    'original_name' => 'TEXT NOT NULL',
                    'created_at' => 'DATETIME DEFAULT CURRENT_TIMESTAMP'
                ],
                'foreign_keys' => [
                    'facility_id' => [
                        'references' => 'facilities(id)',
                        'on_delete' => 'CASCADE'
                    ]
                ],
                'indexes' => [
                    'idx_facility_images_facility_id' => ['facility_id'],
                    'idx_facility_images_created_at' => ['created_at']
                ]
            ],
            'admin_settings' => [
                'columns' => [
                    'id' => 'INTEGER PRIMARY KEY AUTOINCREMENT',
                    'setting_key' => 'TEXT UNIQUE NOT NULL',
                    'setting_value' => 'TEXT NOT NULL',
                    'created_at' => 'DATETIME DEFAULT CURRENT_TIMESTAMP',
                    'updated_at' => 'DATETIME DEFAULT CURRENT_TIMESTAMP'
                ],
                'indexes' => [
                    'idx_admin_settings_key' => ['setting_key'],
                    'idx_admin_settings_updated_at' => ['updated_at']
                ]
            ]
        ],
        'drop_order' => ['facility_images', 'facilities', 'admin_settings']
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
        'facility_name' => '店舗',  // 施設の呼称（店舗、施設、お店など）
        'categories' => [
            'インドカレー',
            'タイカレー',
            '欧風カレー',
            '日本式カレー',
            'その他'
        ],
        'field_labels' => [
            'name' => '店舗名',
            'category' => 'カテゴリー',
            'address' => '住所',
            'description' => '説明',
            'phone' => '電話番号',
            'website' => 'ウェブサイト',
            'business_hours' => '営業時間',
            'sns_account' => 'SNSアカウント',
            'review' => 'レビュー・詳細説明',
            'images' => '画像',
            'location' => '位置情報'
        ]
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
        'max_images_per_facility' => 10,
        'max_review_length' => 2000
    ],
    
    // ストレージ設定
    'storage' => [
        'images_dir' => 'facility_images',
        'database_file' => 'facilities.db'
    ],
    
    // サンプルデータ設定
    'sample_data' => [
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
        ]
    ]
];
?>