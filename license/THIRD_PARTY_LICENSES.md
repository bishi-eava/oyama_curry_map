# サードパーティライセンス

このプロジェクトは以下のオープンソースライブラリとサービスを使用しています。

---

## 1. Leaflet.js

**使用方法**: 地図表示ライブラリ  
**バージョン**: 1.9.4  
**配布元**: https://leafletjs.com/  
**CDN**: https://unpkg.com/leaflet/dist/leaflet.js  
**ライセンス**: BSD-2-Clause License  
**ソース**: https://github.com/Leaflet/Leaflet/blob/main/LICENSE  

### ライセンス全文

```
BSD 2-Clause License

Copyright (c) 2010-2025, Volodymyr Agafonkin
Copyright (c) 2010-2011, CloudMade
All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

1. Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.

2. Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
```

---

## 2. OpenStreetMap

**使用方法**: 地図データおよび地図タイル  
**公式サイト**: https://www.openstreetmap.org/  
**著作権ページ**: https://www.openstreetmap.org/copyright  
**ライセンス**: Open Data Commons Open Database License (ODbL) v1.0  
**ライセンス公式ページ**: https://opendatacommons.org/licenses/odbl/1.0/  

### 著作権表示

© OpenStreetMap contributors

### ライセンス参照

OpenStreetMap データは Open Data Commons Open Database License (ODbL) v1.0 の下で提供されています。

**完全なライセンス文書**: https://opendatacommons.org/licenses/odbl/1.0/

OpenStreetMap Foundation による帰属表示ガイドライン:  
https://osmfoundation.org/wiki/Licence/Attribution_Guidelines

---

## 3. Nominatim

**使用方法**: 逆ジオコーディング（座標から住所への変換）  
**API URL**: https://nominatim.openstreetmap.org/  
**利用規約**: https://operations.osmfoundation.org/policies/nominatim/  
**ライセンス**: Open Data Commons Open Database License (ODbL) v1.0  

### 著作権表示

© OpenStreetMap contributors

### ライセンス参照

Nominatim は OpenStreetMap データを使用しているため、上記の OpenStreetMap と同じライセンスおよび著作権表示が適用されます。

**完全なライセンス文書**: https://opendatacommons.org/licenses/odbl/1.0/

---

## 帰属表示の実装

### 地図上の表示

このプロジェクトの地図上には以下の帰属表示が実装されています：

```javascript
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);
```

### 実装ファイル

帰属表示は以下のファイルで実装されています：

- `index.php` - メインページの地図
- `facility_detail.php` - 店舗詳細ページの地図  
- `admin_add.php` - 管理者新規登録ページの地図
- `admin_edit.php` - 管理者編集ページの地図

---

## 重要な注意事項

1. **完全なライセンス文書**: 上記は参照情報です。法的な拘束力を持つのは各ライセンスの公式文書です。

2. **ライセンス更新**: 使用するライブラリやサービスのライセンスが更新される場合があります。定期的に確認してください。

3. **帰属表示の維持**: 地図上の帰属表示は削除せず、適切に維持してください。

4. **法的助言**: ライセンスに関する法的な質問については、適切な法的助言を求めてください。

---

**このファイルの最終更新**: 2024年12月  
**参照した公式ライセンス日付**: 取得時点の最新版