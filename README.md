# おやまカレーマップ

栃木県小山市を中心としたエリアのカレーショップ情報を提供する地図アプリです。

一般ユーザーはindex.phpへアクセスすることでカレーショップ情報をピン留めした地図を閲覧できます。起動時には小山市を中心としたエリアが表示され、端末のGPS情報を使ってユーザーの現在位置を中心に表示することもできます。

管理ユーザーはカレーショップ情報の登録や削除、編集などが行えます。ショップ情報には、施設名、住所、レビュー、写真画像などが含まれます。

## 汎用性

このアプリはカレーショップにかぎらず、地域の施設情報を地図上に表示するWebアプリケーションとして完全に汎用化されています。設定ファイルの変更のみで異なる施設タイプに対応できます。

**設定可能な用途例**: カレーショップ、レストラン、観光スポット、公共施設、ショッピングモール、医療機関など

### 汎用化の特徴
- **設定ファイルベース**: カテゴリ、ラベル、アプリ名を統一管理
- **データベース構造**: 任意の施設タイプに対応した汎用テーブル構造
- **コード中立性**: 特定の施設タイプに依存しない変数名・関数名
- **柔軟なカスタマイズ**: 新しい施設タイプへの対応が容易

## 機能概要

### 一般ユーザー向け
- **地図表示**: OpenStreetMap + Leaflet.jsによる施設マーカー表示
- **施設情報**: 施設名、住所、レビュー、画像（最大10枚）の表示
- **施設詳細ページ**: 個別店舗の詳細情報・地図・画像ギャラリー
- **現在位置取得**: GPS機能による現在位置への移動
- **画像閲覧**: ポップアップ表示とモーダル拡大表示

### 管理者向け
- **認証システム**: ログイン・セッション管理・自動ログアウト
- **施設管理**: 新規登録・編集・削除
- **画像管理**: アップロード（5MB以下、最大10枚）・個別削除
- **レビュー管理**: 最大2000文字のレビュー・説明文
- **パスワード変更**: 管理画面からのパスワード変更
- **設定カスタマイズ**: 施設名称・初期地図位置・アプリ名の変更

## 技術構成
- **言語**: PHP 7.4以上
- **データベース**: SQLite3
- **地図**: OpenStreetMap（Leaflet.js）
- **認証**: セッションベース認証（30分タイムアウト）
- **画像保存**: ファイルシステム（`facility_images/`ディレクトリ）
- **スタイル**: 統合CSS（共通・管理画面・メインページ別ファイル）
- **設定管理**: 一元的な設定ファイル（Web外配置）

## セットアップ手順

### 1. ファイルアップロード
さくらインターネットのスタンダードプランのwwwディレクトリにアップロード：
```
www/
├── index.php
├── login.php
├── admin.php
├── admin_add.php
├── admin_edit.php
├── admin_password.php
├── api_facilities.php
├── api_add_facility.php （現在未使用）
├── auth_check.php
├── init_db.php
├── css/
│   ├── common.css （共通スタイル）
│   ├── admin.css （管理画面用スタイル）
│   └── main.css （メインページ用スタイル）
├── license/ （ライセンス関連ファイル）
│   ├── LICENSE
│   ├── THIRD_PARTY_LICENSES.md
│   ├── LICENSE_LEAFLET
│   └── LICENSE_OPENSTREETMAP
└── facility_images/ （自動作成）

app_db/oyama_curry_map/ （www外のディレクトリ）
├── config.php
└── facilities.db （初期化時に作成）
```

### 2. 設定ファイル
`app_db/oyama_curry_map/config.php` のファイル権限を600に設定：
```bash
chmod 600 app_db/oyama_curry_map/config.php
```

### 3. データベース初期化
**管理者でログイン後**、ブラウザで `init_db.php` にアクセスしてデータベースを初期化：

#### 初期化オプション
- **構成のみ更新（データ保持）**: 既存データを保持してテーブル構造のみ更新
- **全削除して初期化**: 全データを削除してサンプルデータで初期化

#### セキュリティ機能
- **認証チェック**: 管理者ログインが必要
- **CSRF対策**: トークン検証による不正アクセス防止
- **処理確認**: 実行前の確認メッセージ表示

### 4. 設定カスタマイズ・セキュリティ
- 初期パスワードを変更
- `app_db/oyama_curry_map/config.php` で以下をカスタマイズ：
  - アプリケーション名（`app.name`）
  - 施設の呼称（`app.facility_name`）: 「店舗」「施設」「レストラン」など
  - 初期地図位置（`map.initial_latitude`, `map.initial_longitude`）
  - 管理者パスワード（`admin.password`）

## 使用方法

### 一般ユーザー
1. `index.php` にアクセス
2. 地図上のマーカーをクリックで施設情報表示
3. 「詳細を見る」ボタンで施設詳細ページ（`facility_detail.php`）に移動
4. 「現在位置」ボタンでGPS位置に移動

### 管理者
1. `login.php` でログイン（初期パスワード: admin123）
2. **管理画面**: 施設一覧・編集・削除
3. **新規登録**: 地図で位置指定・施設情報・画像・レビュー入力
4. **施設編集**: 既存施設の情報・画像・レビュー編集
5. **パスワード変更**: セキュリティ向上のため定期変更推奨

## ファイル構成

### メインファイル
- `index.php` - 一般ユーザー向け地図画面
- `facility_detail.php` - 施設詳細表示ページ
- `login.php` - 管理者ログイン画面
- `auth_check.php` - 認証・セキュリティ機能

### 管理者画面
- `admin.php` - 施設一覧・管理画面
- `admin_add.php` - 新規施設登録画面
- `admin_edit.php` - 施設編集画面
- `admin_password.php` - パスワード変更画面

### API
- `api_facilities.php` - 施設一覧JSON API
- `api_add_facility.php` - 施設登録API（認証付き）**※現在未使用**

### スタイルシート
- `css/common.css` - 共通スタイル（ヘッダー、基本レイアウト）
- `css/admin.css` - 管理画面用統合CSS
- `css/main.css` - メインページ・詳細ページ用CSS

### 設定・初期化
- `app_db/oyama_curry_map/config.php` - 設定ファイル（Web外配置）
- `init_db.php` - データベース初期化（認証・CSRF対策付き）

### データ
- `app_db/oyama_curry_map/facilities.db` - SQLiteデータベース
- `facility_images/` - アップロード画像保存ディレクトリ

## 設定項目

### config.php の主要設定

#### アプリケーション設定
```php
'app' => [
    'name' => 'おやまカレーマップ',           // アプリケーション名
    'facility_name' => '店舗',                // 施設の呼称
    'version' => '1.0.0',
    'timezone' => 'Asia/Tokyo'
]
```

#### 地図設定
```php
'map' => [
    'initial_latitude' => 36.3141,   // 初期表示緯度
    'initial_longitude' => 139.8006, // 初期表示経度
    'initial_zoom' => 14             // 初期ズームレベル
]
```

#### 管理者設定
```php
'admin' => [
    'password' => 'admin123',        // 管理者パスワード
    'session_timeout' => 1800       // セッションタイムアウト（秒）
]
```

#### セキュリティ設定
```php
'security' => [
    'max_image_size' => 5 * 1024 * 1024,  // 最大画像サイズ（5MB）
    'max_images_per_facility' => 10,      // 施設あたり最大画像数
    'max_review_length' => 2000           // レビュー最大文字数
]
```

## セキュリティ機能
- **認証システム**: セッションベース認証
- **CSRF対策**: トークン検証
- **パスワード保護**: Web外設定ファイル
- **セッションタイムアウト**: 30分自動ログアウト
- **ファイル制限**: 画像5MB以下、10枚まで
- **入力検証**: XSS対策、文字数制限

## データベース構造

### テーブル一覧

#### 1. facilities テーブル（施設情報）
| カラム名 | データ型 | 制約 | 説明 |
|---------|----------|-----|------|
| id | INTEGER | PRIMARY KEY AUTOINCREMENT | 施設ID |
| name | TEXT | NOT NULL | 施設名 |
| lat | REAL | NOT NULL | 緯度 |
| lng | REAL | NOT NULL | 経度 |
| address | TEXT | | 住所 |
| description | TEXT | | 説明 |
| phone | TEXT | | 電話番号 |
| website | TEXT | | ウェブページアドレス |
| business_hours | TEXT | | 営業時間 |
| sns_account | TEXT | | SNSアカウント |
| category | TEXT | | カテゴリ |
| review | TEXT | | レビュー・詳細説明文 |
| updated_at | DATETIME | DEFAULT CURRENT_TIMESTAMP | 更新日時 |

#### 2. facility_images テーブル（画像情報）
| カラム名 | データ型 | 制約 | 説明 |
|---------|----------|-----|------|
| id | INTEGER | PRIMARY KEY AUTOINCREMENT | 画像ID |
| facility_id | INTEGER | NOT NULL, FOREIGN KEY | 施設ID（facilities.id） |
| filename | TEXT | NOT NULL | 保存されたファイル名 |
| original_name | TEXT | NOT NULL | 元のファイル名 |
| created_at | DATETIME | DEFAULT CURRENT_TIMESTAMP | 作成日時 |

- **外部キー制約**: `facility_id` → `facilities.id` (ON DELETE CASCADE)


## 要件
- **PHP**: 7.4以上
- **拡張機能**: SQLite3、GD（画像処理）
- **ブラウザ**: モダンブラウザ（JavaScript有効）
- **サーバー**: さくらインターネット スタンダードプラン推奨

## トラブルシューティング
- **ログインできない**: `app_db/oyama_curry_map/config.php`のパスワード確認
- **画像が表示されない**: `facility_images/`ディレクトリの権限確認
- **データベースエラー**: `app_db/oyama_curry_map/`ディレクトリの権限確認
- **地図が表示されない**: JavaScript・ネットワーク接続確認
- **init_db.phpにアクセスできない**: 管理者認証が必要（ログイン後にアクセス）

## 注意事項
- 初期パスワード（admin123）は必ず変更してください
- `app_db/oyama_curry_map/config.php`のファイル権限を適切に設定してください
- 定期的なデータベースバックアップを推奨します
- 画像ファイルも含めたバックアップ計画を立ててください
- `init_db.php`は管理者認証が必要です（セキュリティ強化済み）

## 📄 ライセンス

このプロジェクトは以下のオープンソースライブラリとサービスを使用しています：

### 使用ライブラリ

| ライブラリ | ライセンス | 用途 |
|-----------|-----------|------|
| **Leaflet.js** | BSD-2-Clause | 地図表示ライブラリ |
| **OpenStreetMap** | Open Database License (ODbL) | 地図データ・タイル |
| **Nominatim** | Open Database License (ODbL) | 逆ジオコーディング |

### ライセンス詳細

詳細なライセンス情報は以下のファイルを参照してください：

- 📋 [`license/THIRD_PARTY_LICENSES.md`](license/THIRD_PARTY_LICENSES.md) - すべてのサードパーティライセンス
- 📄 [`license/LICENSE_LEAFLET`](license/LICENSE_LEAFLET) - Leaflet.js ライセンス
- 📄 [`license/LICENSE_OPENSTREETMAP`](license/LICENSE_OPENSTREETMAP) - OpenStreetMap ライセンス

### 帰属表示

このプロジェクトの地図上には `© OpenStreetMap contributors` の帰属表示が表示されており、OpenStreetMap の利用規約に従っています。
