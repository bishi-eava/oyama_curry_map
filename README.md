# おやま子ども施設マップ

栃木県小山市の子育て支援施設を地図で確認できるWebアプリケーションです。

## 📍 ユーザー向け使い方

### 地図で子育て施設を確認
1. **メインページ**: [`index.php`](index.php) にアクセス
2. **地図表示**: 小山市を中心とした地図に子育て支援施設がマーカーで表示
3. **現在位置取得**: 「現在位置」ボタンでGPS機能を使用して現在地に移動 (デバイスの位置情報利用許可が必要)
4. **カテゴリフィルター**: 施設の種類別に表示を絞り込み可能

### 施設詳細情報の確認
1. **マーカークリック**: 地図上のマーカーをクリックで基本情報を表示
2. **詳細ページ**: 「詳細を見る」ボタンで施設詳細ページ（`facility_detail.php`）に移動
3. **詳細情報**: 施設名、住所、電話番号、利用時間、小児対応の有無、画像など
4. **画像ギャラリー**: 施設の画像をクリックでモーダル表示・拡大表示

### 施設情報の内容
- **基本情報**: 施設名、住所、電話番号、所属組織
- **利用情報**: 利用可能日、利用時間、小児対応の有無
- **画像**: 施設の外観・内観の写真
- **備考**: 利用に関する注意事項や詳細情報

## 🛠️ 管理者向け使い方

### ログイン・認証
1. **管理者ログイン**: [`login.php`](login.php) でログイン
2. **セキュリティ**: セッションベース認証・30分自動ログアウト
3. **パスワード変更**: 管理画面からパスワード変更可能

### 子育て支援施設の管理
1. **施設一覧**: [`admin.php`](admin.php) で登録済み施設の一覧・編集・削除
2. **新規登録**: [`admin_add.php`](admin_add.php) で新しい子育て支援施設を登録
   - 地図で位置指定（クリック・ドラッグ）
   - 施設情報入力（名前、住所、電話番号、利用時間等）
   - 画像アップロード（最大10枚、5MB以下）
3. **施設編集**: [`admin_edit.php`](admin_edit.php) で既存施設の情報修正
   - 位置変更、情報更新、画像追加・削除

### CSVインポート機能
1. **データベース初期化**: [`init_db.php`](init_db.php) でCSVファイルからデータ一括登録
2. **ブラウザアップロード**: CSVファイルをブラウザから選択・アップロード
3. **小児対応デフォルト**: 空欄の小児対応フィールドは自動的に「無」に設定

## 🚀 クイックデプロイ（一般ユーザー向け最小構成）

一般ユーザーが地図閲覧のみを利用する場合の最小構成です。管理機能を除外することで、セキュリティリスクを最小化し、簡単にデプロイできます。

### 📁 最小構成ファイル（4つのPHPファイル + CSS + 設定）

```
www/
├── index.php                    # 📍 メインページ（地図表示）
├── facility_detail.php         # 📄 施設詳細ページ
├── api_facilities.php          # 🔌 施設情報API
├── auth_check.php              # ⚙️ 設定ファイル読み込み・共通関数
├── css/
│   ├── common.css              # 🎨 共通スタイル
│   └── main.css                # 🎨 メインページ用CSS
├── license/                    # 📄 ライセンス情報
└── facility_images/            # 🖼️ 画像保存ディレクトリ（自動作成）

app_db/oyama_child_facilitation_map/           # 🔒 Web外ディレクトリ
├── config.php                  # ⚙️ 設定ファイル
└── facilities.db               # 🗄️ SQLiteデータベース
```

### 🎯 最小構成の特徴
- **セキュリティ**: 管理機能なしで外部からの攻撃リスクを最小化
- **メンテナンス**: 更新・保守作業が不要
- **パフォーマンス**: 管理機能のないシンプルな構成で高速
- **コスト**: 最小限のサーバー容量で運用可能

### ⚡ 最小構成デプロイ手順

1. **ファイル配置**: 上記の最小構成ファイルのみをアップロード
2. **権限設定**: `chmod 600 app_db/oyama_child_facilitation_map/config.php`
3. **データベース準備**: 事前に作成したデータベースファイルを配置
4. **動作確認**: `index.php` にアクセスして地図表示を確認

### 🔧 データベース準備方法（最小構成用）
最小構成では管理機能がないため、事前にデータベースを準備する必要があります：

**オプション1: フル機能版で作成後に移植**
1. 一時的にフル機能版をローカル環境に構築
2. 管理画面でデータ登録・画像アップロード
3. `facilities.db` と `facility_images/` を最小構成環境にコピー

**オプション2: CSVインポート機能を使用**
1. 一時的に `init_db.php` のみを追加配置
2. 管理者ログイン後にCSVファイルでデータ一括登録
3. 登録完了後に `init_db.php` と管理関連ファイルを削除

---

## ⚙️ フル機能版セットアップ手順

管理機能を含む完全版のセットアップ手順です。施設情報の追加・編集・削除が可能です。

### 📁 フル機能版ファイル構成

```
www/
├── index.php                    # 📍 メインページ
├── facility_detail.php         # 📄 施設詳細ページ
├── login.php                   # 🔐 管理者ログイン
├── admin.php                   # 🛠️ 管理者ダッシュボード
├── admin_add.php               # ➕ 新規施設登録
├── admin_edit.php              # ✏️ 施設編集
├── admin_password.php          # 🔑 パスワード変更
├── admin_export_csv.php        # 📊 CSV エクスポート
├── auth_check.php              # 🔒 認証・セキュリティ
├── api_facilities.php          # 🔌 施設情報API
├── init_db.php                 # 🗄️ データベース初期化
├── facility_form_functions.php # 🔧 共通フォーム処理
├── css/
│   ├── common.css              # 🎨 共通スタイル
│   ├── admin.css               # 🎨 管理画面用CSS
│   └── main.css                # 🎨 メインページ用CSS
├── license/                    # 📄 ライセンス情報
└── facility_images/            # 🖼️ 画像保存ディレクトリ（自動作成）

app_db/oyama_child_facilitation_map/           # 🔒 Web外ディレクトリ
├── config.php                  # ⚙️ 設定ファイル
└── facilities.db               # 🗄️ SQLiteデータベース
```

### 🔄 構成比較表

| 項目 | 最小構成（一般ユーザー向け） | フル機能版（管理者向け） |
|------|---------------------------|----------------------|
| **ファイル数** | 4つのPHPファイル + CSS | 12つのPHPファイル + CSS |
| **セキュリティリスク** | 🟢 最小 | 🟡 管理機能あり |
| **メンテナンス** | 🟢 不要 | 🟡 定期的な更新必要 |
| **機能** | 地図閲覧・詳細表示のみ | 施設管理・編集・CSV機能 |
| **対象ユーザー** | 一般利用者 | 管理者・運営者 |
| **推奨用途** | 公開サイト・情報提供 | 内部管理・データ更新 |

### 1. フル機能版ファイル配置
上記のフル機能版ファイル構成をWebサーバーにアップロード

### 2. フル機能版権限設定
```bash
# 設定ファイルの権限を制限
chmod 600 app_db/oyama_child_facilitation_map/config.php

# 画像ディレクトリの権限設定
chmod 755 facility_images/
```

### 3. フル機能版データベース初期化
1. **管理者ログイン**: [`login.php`](login.php) でログイン（初期パスワード: admin123）
2. **データベース初期化**: [`init_db.php`](init_db.php) にアクセス
3. **初期化オプション選択**:
   - **構成のみ更新**: 既存データを保持してテーブル構造のみ更新
   - **全削除して初期化**: 全データを削除してサンプルデータで初期化
   - **CSVインポート**: ブラウザからCSVファイルをアップロードして一括登録

### 4. フル機能版設定カスタマイズ
`app_db/oyama_child_facilitation_map/config.php` で以下を変更：

```php
'app' => [
    'name' => 'おやま子ども施設マップ',      # アプリケーション名
    'facility_name' => '子育て支援施設', # 施設の呼称
    'categories' => ['保育所（認可公立）', '保育所（認可私立）', '認定こども園', '幼稚園', '認可外保育所', '放課後児童クラブ', '児童館', 'その他'] # カテゴリ
],
'map' => [
    'initial_latitude' => 36.3141,   # 初期表示緯度（小山市）
    'initial_longitude' => 139.8006, # 初期表示経度（小山市）
    'initial_zoom' => 14             # 初期ズームレベル
],
'admin' => [
    'password' => 'your_secure_password', # 管理者パスワード（要変更）
    'session_timeout' => 1800       # セッションタイムアウト（30分）
]
```

## 💻 技術説明

### システム構成
- **フロントエンド**: HTML5 + CSS3 + JavaScript（ES6+）
- **バックエンド**: PHP 7.4+ 
- **データベース**: SQLite3（WALモード）
- **地図**: OpenStreetMap + Leaflet.js 1.9+
- **認証**: PHPセッション + CSRF対策

### 主要技術要素
1. **地図表示**: Leaflet.jsライブラリによる軽量地図表示
2. **現在位置取得**: Geolocation API使用
3. **レスポンシブデザイン**: モバイル・デスクトップ対応
4. **画像処理**: GD拡張による画像リサイズ・検証
5. **セキュリティ**: 入力検証、XSS対策、CSRF対策
6. **データベース**: SQLite WALモードによる同時実行対応

### データベース構造
```sql
-- 施設情報テーブル
CREATE TABLE facilities (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    csv_no TEXT,                    -- CSV番号（インポート用）
    name TEXT NOT NULL,             -- 施設名
    name_kana TEXT,                 -- 施設名（カナ）
    lat REAL NOT NULL,              -- 緯度
    lng REAL NOT NULL,              -- 経度
    address TEXT,                   -- 住所
    address_detail TEXT,            -- 詳細住所
    installation_position TEXT,     -- アクセス方法
    phone TEXT,                     -- 電話番号
    phone_extension TEXT,           -- 電話番号（内線）
    corporate_number TEXT,          -- 法人番号
    organization_name TEXT,         -- 組織名
    available_days TEXT,            -- 利用可能日
    start_time TEXT,                -- 開始時間
    end_time TEXT,                  -- 終了時間
    available_hours_note TEXT,      -- 利用時間の注記
    pediatric_support TEXT,         -- 小児対応（有/無）
    website TEXT,                   -- ウェブサイト
    note TEXT,                      -- 備考
    category TEXT,                  -- カテゴリ
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 画像情報テーブル
CREATE TABLE facility_images (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    facility_id INTEGER NOT NULL,
    filename TEXT NOT NULL,
    original_name TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (facility_id) REFERENCES facilities(id) ON DELETE CASCADE
);
```

### セキュリティ機能
- **認証**: セッションベース認証
- **CSRF対策**: トークン検証
- **XSS対策**: 入力値のサニタイズ
- **ファイル制限**: 画像ファイルのみ、5MB以下、最大10枚
- **権限設定**: 設定ファイルはWeb外配置、権限600
- **API情報制限**: 機密情報などの漏洩防止（updated_at、original_name等を除外）

---

## 📊 ファイル依存関係とセキュリティレベル

### 🔗 ファイル依存関係図

```
📁 最小構成での依存関係
index.php ─── auth_check.php ─── config.php
    │              │               │
    └─── css/      └─── facilities.db
    │
facility_detail.php ─── auth_check.php
    │
api_facilities.php ─── auth_check.php

📁 フル機能版での追加依存関係
admin.php ─── auth_check.php (認証必須)
admin_add.php ─── auth_check.php ─── facility_form_functions.php
admin_edit.php ─── auth_check.php ─── facility_form_functions.php
login.php ─── auth_check.php
init_db.php ─── auth_check.php (認証必須)
```

### 🔒 セキュリティレベル別要件

#### 🟢 レベル1: 最小構成（一般ユーザー向け）
**セキュリティ要件**: 基本的な対策のみ
- `config.php` の権限設定（600）
- 入力値のサニタイズ（XSS対策）
- SQLインジェクション対策
- **管理機能なし** → 外部攻撃面を最小化

**必要な対策**:
```bash
chmod 600 app_db/oyama_child_facilitation_map/config.php
# 基本的な権限設定のみ
```

#### 🟡 レベル2: フル機能版（管理者向け）
**セキュリティ要件**: 高度な対策必須
- レベル1の全ての対策
- セッション管理・タイムアウト設定
- CSRF トークン検証
- 管理者パスワード強化
- ファイルアップロード制限
- 定期的なセキュリティ更新

**必要な対策**:
```bash
# 初期パスワードの変更（必須）
# admin_password.php でパスワード変更

# セッションディレクトリの権限確認
chmod 700 /tmp  # または使用中のセッションディレクトリ

# 定期的なセキュリティチェック
# - 不審なアクセスログの確認
# - アップロードファイルの検査
# - セッションハイジャック対策
```

---

## 🔧 別のマップシステムを作る際の手順

この子ども施設マップシステムは完全に汎用化されており、他の施設マップシステムに簡単に転用できます。

### 1. 基本設定の変更
`app_db/oyama_child_facilitation_map/config.php` を編集：

```php
'app' => [
    'name' => '新しいマップアプリ名',
    'facility_name' => '施設',        # 「レストラン」「観光地」「公園」など
    'categories' => ['カテゴリ1', 'カテゴリ2', 'カテゴリ3'], # 用途に応じたカテゴリ
    'field_labels' => [
        'name' => '施設名',
        'address' => '住所',
        'phone' => '電話番号',
        'category' => 'カテゴリ',
        # 必要に応じてラベルを変更
    ]
],
'map' => [
    'initial_latitude' => 35.6762,   # 対象地域の緯度
    'initial_longitude' => 139.6503, # 対象地域の経度
    'initial_zoom' => 12
]
```

### 2. データベーステーブル構成の変更
`config.php` の `database.tables` セクションでテーブル構成を変更できます：

```php
'database' => [
    'tables' => [
        'facilities' => [
            'columns' => [
                'id' => 'INTEGER PRIMARY KEY AUTOINCREMENT',
                'name' => 'TEXT NOT NULL',
                'lat' => 'REAL NOT NULL',
                'lng' => 'REAL NOT NULL',
                'address' => 'TEXT',
                'phone' => 'TEXT',
                'website' => 'TEXT',
                'category' => 'TEXT',
                // 用途に応じて追加・削除
                'cuisine_type' => 'TEXT',      # レストラン用
                'price_range' => 'TEXT',       # 価格帯
                'rating' => 'REAL',            # 評価
                'opening_hours' => 'TEXT',     # 営業時間
                'updated_at' => 'DATETIME DEFAULT CURRENT_TIMESTAMP'
            ],
            'indexes' => [
                'idx_facilities_location' => ['lat', 'lng'],
                'idx_facilities_category' => ['category'],
                'idx_facilities_rating' => ['rating']  # 評価での検索用
            ]
        ]
    ]
]
```

#### 観光地マップ用のテーブル構成例
```php
'database' => [
    'tables' => [
        'facilities' => [
            'columns' => [
                'id' => 'INTEGER PRIMARY KEY AUTOINCREMENT',
                'name' => 'TEXT NOT NULL',
                'lat' => 'REAL NOT NULL',
                'lng' => 'REAL NOT NULL',
                'address' => 'TEXT',
                'phone' => 'TEXT',
                'website' => 'TEXT',
                'category' => 'TEXT',
                'admission_fee' => 'TEXT',     # 入場料
                'opening_hours' => 'TEXT',     # 開館時間
                'closed_days' => 'TEXT',       # 休館日
                'parking' => 'TEXT',           # 駐車場情報
                'access_info' => 'TEXT',       # アクセス情報
                'season' => 'TEXT',            # 見頃・シーズン
                'note' => 'TEXT',              # 備考
                'updated_at' => 'DATETIME DEFAULT CURRENT_TIMESTAMP'
            ]
        ]
    ]
]
```

**重要**: テーブル構成を変更した後は、管理者ログイン後に [`init_db.php`](init_db.php) で「構成のみ更新」を実行してデータベースに反映させてください。

### 3. フィールドラベルのカスタマイズ
用途に応じてフィールドラベルを変更：

```php
// 例: レストランマップの場合
'field_labels' => [
    'name' => 'レストラン名',
    'address' => '住所',
    'phone' => '電話番号',
    'website' => 'ウェブサイト',
    'opening_hours' => '営業時間',
    'category' => '料理ジャンル',
    'cuisine_type' => '料理種別',
    'price_range' => '価格帯',
    'rating' => '評価',
    'note' => '備考（予約情報、アクセス等）'
],
'categories' => ['和食', '洋食', '中華', 'イタリアン', 'フレンチ', 'カフェ', 'ファストフード']
```

### 4. 用途別カスタマイズ例

#### 観光地マップ
```php
'app' => [
    'name' => '○○市観光マップ',
    'facility_name' => '観光地',
    'categories' => ['神社・寺院', '公園', '博物館', '展望台', '温泉', 'グルメ', 'お土産']
],
'field_labels' => [
    'name' => '観光地名',
    'address' => '住所',
    'phone' => '問い合わせ先',
    'website' => 'ウェブサイト',
    'business_hours' => '営業時間',
    'category' => '観光地種別',
    'review' => '観光地紹介',
    'note' => 'アクセス・料金情報'
]
```

#### 公共施設マップ
```php
'app' => [
    'name' => '○○市公共施設マップ',
    'facility_name' => '公共施設',
    'categories' => ['市役所', '図書館', '体育館', '公民館', '病院', '学校', '公園']
],
'field_labels' => [
    'name' => '施設名',
    'address' => '住所',
    'phone' => '電話番号',
    'website' => 'ウェブサイト',
    'business_hours' => '開館時間',
    'category' => '施設種別',
    'review' => '施設案内',
    'note' => '休館日・利用料金'
]
```

### 4. 外観カスタマイズ
CSS ファイルを編集してデザインを変更：

```css
/* css/common.css - 基本色の変更 */
:root {
    --primary-color: #your-color;     /* メインカラー */
    --secondary-color: #your-color;   /* サブカラー */
    --accent-color: #your-color;      /* アクセントカラー */
}

/* ヘッダーの背景色 */
.header {
    background-color: var(--primary-color);
}
```

### 5. 地図の初期設定
対象地域に応じて地図の初期表示を調整：

```php
'map' => [
    'initial_latitude' => 35.6762,   # 東京の場合
    'initial_longitude' => 139.6503,
    'initial_zoom' => 12              # 市区町村レベル
]
```

### 6. サンプルデータの準備
新しい用途に応じたサンプルデータを作成：

```php
'sample_data' => [
    [
        'name' => 'サンプル施設1',
        'lat' => 35.6762,
        'lng' => 139.6503,
        'category' => 'カテゴリ1',
        'address' => 'サンプル住所1',
        'phone' => '03-1234-5678'
    ],
    // 追加のサンプルデータ
]
```

### 7. フィールド表示部分の手動変更
用途に応じて各ページのフィールド表示を手動で調整する必要があります：

#### 対象ファイル
- **`facility_detail.php`** - 施設詳細ページの表示項目
- **`admin.php`** - 管理画面の一覧表示項目
- **`admin_add.php`** - 新規登録フォームの項目
- **`admin_edit.php`** - 編集フォームの項目
- **`index.php`** - 地図ポップアップの表示項目

#### 変更例（レストランマップの場合）
```php
// facility_detail.php での表示項目調整
<?php if (!empty($facility['business_hours'])): ?>
    <p><strong>営業時間:</strong> <?= htmlspecialchars($facility['business_hours']) ?></p>
<?php endif; ?>

<?php if (!empty($facility['website'])): ?>
    <p><strong>ウェブサイト:</strong> <a href="<?= htmlspecialchars($facility['website']) ?>" target="_blank">公式サイト</a></p>
<?php endif; ?>

// 料理ジャンルの表示
<?php if (!empty($facility['category'])): ?>
    <p><strong>料理ジャンル:</strong> <?= htmlspecialchars($facility['category']) ?></p>
<?php endif; ?>
```

#### 注意点
- **フィールド構成**: データベースのフィールド名に合わせて変更
- **ユーザビリティ**: 用途に応じた情報の優先順位を考慮
- **レスポンシブ**: モバイル表示での見やすさを確認

### 8. CSVインポート機能でのデフォルト値設定
`init_db.php` のCSVアップロード機能で、必要に応じてデフォルト値を設定できます：

#### config.php での設定例
```php
'csv_import' => [
    'field_mapping' => [
        'name' => 'name',
        'address' => 'address',
        'phone' => 'phone',
        // CSVに存在しないフィールドにデフォルト値を設定
        'category' => 'default_category',
        'website' => '',
        'note' => '情報更新予定'
    ],
    'default_values' => [
        'pediatric_support' => '無',      // 小児対応のデフォルト値
        'category' => 'その他',          // カテゴリのデフォルト値
        'available_days' => '平日',       // 利用可能日のデフォルト値
        'start_time' => '09:00',         // 開始時間のデフォルト値
        'end_time' => '17:00'            // 終了時間のデフォルト値
    ]
]
```

#### init_db.php の categorize_facility 関数の書き換え
用途に応じて自動カテゴリ分類ロジックを変更する必要があります：

```php
// init_db.php の categorize_facility 関数例（レストランマップ用）
function categorize_facility($name, $address = '') {
    $name = mb_strtolower($name);
    $address = mb_strtolower($address);
    
    // 料理ジャンル別の分類
    if (strpos($name, '寿司') !== false || strpos($name, '鮨') !== false) return '和食';
    if (strpos($name, 'ラーメン') !== false || strpos($name, '中華') !== false) return '中華';
    if (strpos($name, 'カフェ') !== false || strpos($name, 'コーヒー') !== false) return 'カフェ';
    if (strpos($name, 'ステーキ') !== false || strpos($name, 'イタリアン') !== false) return '洋食';
    if (strpos($name, 'マクドナルド') !== false || strpos($name, 'kfc') !== false) return 'ファストフード';
    
    return 'その他';
}

// 観光地マップ用の例
function categorize_facility($name, $address = '') {
    $name = mb_strtolower($name);
    
    if (strpos($name, '神社') !== false || strpos($name, '寺院') !== false) return '神社・寺院';
    if (strpos($name, '公園') !== false || strpos($name, '庭園') !== false) return '公園';
    if (strpos($name, '博物館') !== false || strpos($name, '美術館') !== false) return '博物館';
    if (strpos($name, '展望台') !== false || strpos($name, 'タワー') !== false) return '展望台';
    if (strpos($name, '温泉') !== false || strpos($name, '湯') !== false) return '温泉';
    
    return 'その他';
}
```

#### 活用例
- **一括データ移行**: 既存システムからのデータ移行時
- **初期データ設定**: 新規システム構築時のサンプルデータ
- **データ補完**: 不完全なCSVデータの補完
- **自動分類**: 大量データのカテゴリ自動判定

### 9. APIセキュリティの調整

**重要**: 他の用途でマップシステムを作成する際は、`api_facilities.php` のSELECT文を見直し、機密情報や不要な情報が外部に公開されないよう注意してください。

#### API情報制限の実装例
```php
// api_facilities.php - セキュアなフィールド選択
// ❌ 避けるべき方法
$res = $db->query('SELECT * FROM facilities');  // 全フィールドが公開される

// ✅ 推奨方法
$res = $db->query('SELECT id, name, lat, lng, address, category FROM facilities');  // 必要な情報のみ

// 画像情報も同様に制限
$imageStmt = $db->prepare('SELECT filename FROM facility_images WHERE facility_id = :facility_id');  // original_nameは除外
```

#### 除外を検討する情報の例(あくまで用途による)
- **システム管理情報**: `updated_at`, `created_at`, `csv_no`
- **機密情報**: `phone_extension`, `corporate_number`, `original_name`
- **内部情報**: `address_detail`, `installation_position`
- **プライベート情報**: 管理用メモ、内部コード等

### 10. 必要に応じたコードカスタマイズ
- **バリデーション**: 用途に応じた入力検証ルール
- **検索機能**: 用途特有の検索・フィルター機能
- **表示ロジック**: 特定フィールドの表示・非表示制御

## 📋 システム要件

- **PHP**: 7.4以上
- **拡張機能**: SQLite3、GD、Session
- **Webサーバー**: Apache/Nginx
- **ブラウザ**: モダンブラウザ（JavaScript有効）
- **推奨環境**: さくらインターネット スタンダードプラン

## 🚨 注意事項

### セキュリティ
- **パスワード変更**: 初期パスワード（admin123）は必ず変更
- **権限設定**: `config.php` の権限を600に設定
- **定期バックアップ**: データベースと画像ファイルのバックアップ

### 運用時の注意
- **画像容量**: 大量の画像アップロードに注意
- **データベース**: 定期的なVACUUM実行を推奨
- **セッション**: 30分で自動ログアウト

## 📄 ライセンス

このプロジェクトは以下のオープンソースライブラリを使用しています：

| ライブラリ | ライセンス | 用途 |
|-----------|-----------|------|
| **Leaflet.js** | BSD-2-Clause | 地図表示ライブラリ |
| **OpenStreetMap** | Open Database License (ODbL) | 地図データ・タイル |
| **Nominatim** | Open Database License (ODbL) | 住所検索 |

詳細なライセンス情報：
- 📋 [`license/THIRD_PARTY_LICENSES.md`](license/THIRD_PARTY_LICENSES.md)
- 📄 [`license/LICENSE_LEAFLET`](license/LICENSE_LEAFLET)
- 📄 [`license/LICENSE_OPENSTREETMAP`](license/LICENSE_OPENSTREETMAP)