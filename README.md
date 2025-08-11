# おやまカレーマップ

栃木県小山市のカレー店を地図で確認できるWebアプリケーションです。

このプロジェクトは [code4oyama/oyama_child_facilitation_map](https://github.com/code4oyama/oyama_child_facilitation_map) をベースとして開発されました。

## 📍 使い方

### 地図でカレー店を確認
1. **メインページ**: `index.php` にアクセス
2. **地図表示**: 小山市を中心とした地図にカレー店がマーカーで表示
3. **現在位置取得**: 「現在位置」ボタンでGPS機能を使用して現在地に移動
4. **カテゴリフィルター**: カレーの種類別に表示を絞り込み可能

### 店舗詳細情報の確認
1. **マーカークリック**: 地図上のマーカーをクリックで基本情報を表示
2. **詳細ページ**: 「詳細を見る」ボタンで店舗詳細ページに移動
3. **詳細情報**: 店舗名、住所、電話番号、営業時間、説明、画像など
4. **画像ギャラリー**: 店舗の画像をクリックでモーダル表示・拡大表示

## 🛠️ 管理者向け使い方

### ログイン・認証
1. **管理者ログイン**: `login.php` でログイン（初期パスワード: admin123）
2. **セキュリティ**: セッションベース認証・30分自動ログアウト
3. **パスワード変更**: 管理画面からパスワード変更可能

### カレー店の管理
1. **店舗一覧**: `admin.php` で登録済み店舗の一覧・編集・削除
2. **新規登録**: `admin_add.php` で新しいカレー店を登録
   - 地図で位置指定（クリック・ドラッグ）
   - 店舗情報入力（名前、住所、電話番号、営業時間等）
   - 画像アップロード（最大10枚、5MB以下）
3. **店舗編集**: `admin_edit.php` で既存店舗の情報修正

### CSVインポート機能
1. **データベース初期化**: `init_db.php` でCSVファイルからデータ一括登録
2. **ブラウザアップロード**: CSVファイルをブラウザから選択・アップロード

## 🚀 デプロイ手順

### 1. ファイル配置
以下の構成でWebサーバーにアップロード：

```
www/
├── index.php                    # メインページ
├── facility_detail.php         # 店舗詳細ページ
├── login.php                   # 管理者ログイン
├── admin.php                   # 管理者ダッシュボード
├── admin_add.php               # 新規店舗登録
├── admin_edit.php              # 店舗編集
├── admin_password.php          # パスワード変更
├── admin_export_csv.php        # CSV エクスポート
├── auth_check.php              # 認証・セキュリティ
├── api_facilities.php          # 店舗情報API
├── init_db.php                 # データベース初期化
├── facility_form_functions.php # 共通フォーム処理
├── css/
│   ├── common.css              # 共通スタイル
│   ├── admin.css               # 管理画面用CSS
│   └── main.css                # メインページ用CSS
├── license/                    # ライセンス情報
└── facility_images/            # 画像保存ディレクトリ（自動作成）

app_db/oyama_curry_map/           # Web外ディレクトリ
├── config.php                  # 設定ファイル
└── facilities.db               # SQLiteデータベース
```

### 2. 権限設定
```bash
# 設定ファイルの権限を制限
chmod 600 app_db/oyama_curry_map/config.php

# 画像ディレクトリの権限設定
chmod 755 facility_images/
```

### 3. データベース初期化
1. **管理者ログイン**: `login.php` でログイン（初期パスワード: admin123）
2. **データベース初期化**: `init_db.php` にアクセス
3. **初期化オプション選択**:
   - **構成のみ更新**: 既存データを保持してテーブル構造のみ更新
   - **全削除して初期化**: 全データを削除してサンプルデータで初期化
   - **CSVインポート**: ブラウザからCSVファイルをアップロードして一括登録

### 4. 設定カスタマイズ
`app_db/oyama_curry_map/config.php` で以下を変更：

```php
'app' => [
    'name' => 'おやまカレーマップ',      # アプリケーション名
    'facility_name' => '店舗',           # 店舗の呼称
    'categories' => ['インドカレー','タイカレー','欧風カレー','日本式カレー','その他'] # カテゴリ
],
'map' => [
    'initial_latitude' => 36.3141,   # 初期表示緯度（小山市）
    'initial_longitude' => 139.8006, # 初期表示経度（小山市）
    'initial_zoom' => 14             # 初期ズームレベル
],
'admin' => [
    'password' => 'your_secure_password', # 管理者パスワード（要変更）
    'session_timeout' => 1800            # セッションタイムアウト（30分）
]
```

### 5. セキュリティ設定
```bash
# 初期パスワードの変更（必須）
# admin_password.php でパスワード変更

# 設定ファイルがWeb外に配置されていることを確認
# app_db/ ディレクトリは Document Root の外に配置
```

## 💻 技術仕様

- **フロントエンド**: HTML5 + CSS3 + JavaScript + Leaflet.js
- **バックエンド**: PHP 7.4+ 
- **データベース**: SQLite3（WALモード）
- **地図**: OpenStreetMap + Leaflet.js
- **認証**: PHPセッション + CSRF対策

## 📊 CSVインポート形式

CSVファイルは以下の11列形式で作成してください：

```
店舗名,緯度,経度,住所,説明,電話番号,ウェブサイト,営業時間,SNSアカウント,カテゴリ,レビュー・詳細
カレーハウス スパイシー,36.3141,139.8006,栃木県小山市中央町3-1-1,本格スパイスを使用したインドカレー専門店,0285-20-1234,,11:00-22:00,@spicy_curry_oyama,インドカレー,スパイスの効いた本格インドカレーが味わえる人気店。ナンは焼きたてで絶品。
```

## 📋 システム要件

- **PHP**: 7.4以上
- **拡張機能**: SQLite3、GD、Session
- **Webサーバー**: Apache/Nginx
- **ブラウザ**: モダンブラウザ（JavaScript有効）

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
- [`license/THIRD_PARTY_LICENSES.md`](license/THIRD_PARTY_LICENSES.md)
- [`license/LICENSE_LEAFLET`](license/LICENSE_LEAFLET)
- [`license/LICENSE_OPENSTREETMAP`](license/LICENSE_OPENSTREETMAP)