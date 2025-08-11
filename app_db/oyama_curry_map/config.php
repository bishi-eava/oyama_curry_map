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
                    'name' => 'TEXT NOT NULL',  // 施設名
                    'lat' => 'REAL NOT NULL',  // 緯度
                    'lng' => 'REAL NOT NULL',  // 経度
                    'address' => 'TEXT',  // 住所
                    'description' => 'TEXT',  // 説明
                    'phone' => 'TEXT',  // 電話番号
                    'website' => 'TEXT',  // ウェブページアドレス
                    'business_hours' => 'TEXT',  // 営業時間
                    'sns_account' => 'TEXT',  // SNSアカウント
                    'category' => 'TEXT',  // カテゴリ
                    'review' => 'TEXT',  // レビュー・詳細説明文
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
        'facility_name' => '店舗',  // 施設の呼称
        'categories' => [
            'インドカレー',
            'タイカレー',
            '欧風カレー',
            '日本式カレー',
            'その他'
        ],
        'field_labels' => [
            'name' => '店舗名',
            'category' => 'カテゴリ',
            'address' => '住所',
            'description' => '説明',
            'phone' => '電話番号',
            'website' => 'ウェブサイト',
            'business_hours' => '営業時間',
            'sns_account' => 'SNSアカウント',
            'review' => 'レビュー・詳細',
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
    
    // CSVインポート設定
    'csv_import' => [
        'encoding' => 'UTF-8',
        'has_header' => true,
        'max_file_size' => 10 * 1024 * 1024, // 10MB
        'allowed_extensions' => ['csv'],
        'allowed_mime_types' => ['text/csv', 'text/plain', 'application/csv'],
        'field_mapping' => [
            // フィールド名 => CSV列番号（0ベース）
            'name' => 0,
            'lat' => 1,
            'lng' => 2,
            'address' => 3,
            'description' => 4,
            'phone' => 5,
            'website' => 6,
            'business_hours' => 7,
            'sns_account' => 8,
            'category' => 9,
            'review' => 10
        ],
        'required_fields' => ['name', 'lat', 'lng'],
        'default_values' => [
            'category' => 'その他'
        ],
        'validation' => [
            'lat_min' => 24,
            'lat_max' => 46,
            'lng_min' => 123,
            'lng_max' => 146,
            'expected_columns' => 11
        ]
    ],
    
    // サンプルデータ設定
    'sample_data' => [
        [
            'name' => 'カレーハウス スパイシー',
            'lat' => 36.3141,
            'lng' => 139.8006,
            'address' => '栃木県小山市中央町3-1-1',
            'description' => '本格スパイスを使用したインドカレー専門店',
            'phone' => '0285-20-1234',
            'website' => '',
            'business_hours' => '11:00-22:00',
            'sns_account' => '@spicy_curry_oyama',
            'category' => 'インドカレー',
            'review' => 'スパイスの効いた本格インドカレーが味わえる人気店。ナンは焼きたてで絶品。'
        ],
        [
            'name' => 'タイ料理 バンコク',
            'lat' => 36.3100,
            'lng' => 139.8050,
            'address' => '栃木県小山市駅南町2-5-10',
            'description' => 'タイ人シェフが作る本格タイカレー',
            'phone' => '0285-25-5678',
            'website' => 'https://bangkok-oyama.example.com',
            'business_hours' => '11:30-21:00（火曜定休）',
            'sns_account' => '',
            'category' => 'タイカレー',
            'review' => 'ココナッツミルクの甘さと香辛料のバランスが絶妙なグリーンカレーが自慢。'
        ],
        [
            'name' => 'カレーレストラン ヨーロッパ',
            'lat' => 36.3080,
            'lng' => 139.7980,
            'address' => '栃木県小山市本町1-2-3',
            'description' => '老舗の欧風カレー専門店',
            'phone' => '0285-22-9999',
            'website' => '',
            'business_hours' => '11:00-20:30',
            'sns_account' => '',
            'category' => '欧風カレー',
            'review' => '創業30年の老舗。じっくり煮込んだ欧風カレーは深いコクと旨味が特徴。'
        ]
    ]
];
?>