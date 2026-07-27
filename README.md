プロジェクト名:COACHTECH お問い合わせフォーム

概要:
fortifyを利用した,認証機能の設定。
バリデーション失敗時のエラーメッセージ表現。
ControllerでN+1問題の制御によりサーバー読み取りの負荷軽減。
fputs,fopen,fclose関数の使い方の勉強。
ポストマンの使い方の練習。

ER図:
CATEGORIES ||--o{ 
CONTACTS 一つの問い合わせ分類は,多くの問い合わせ内容に対応するが,一つの問い合わせ内容は一つの問い合わせ分類に属する。(1:多)

CONTACTS }o--o{ TAGS  
一つの問い合わせ内容は複数のタグを持ち,一つのタグは複数の問い合わせ内容に対応する。(多:多) 
![ER図](ER_picture.png)

環境構築手順:
1. Laravelプロジェクトの作成 (Laravel 10.x)
2. Laravel Sailのインストール
3. .env ファイルの設定
4. phpMyAdminの追加
5. Sailの起動とエイリアス設定
6. アプリケーションキーの生成
7. フロントエンドのセットアップ (Vite & Tailwind CSS)※sail npm install を実行する前に、必ずSailコンテナが起動している必要がある為。
8. データベースのマイグレーションと初期データ投入
使用技術:
HTML+CSS+Javascript(bootstrap,tailwind,node)
PHP,Laravel 10, MySQL 8.0, Nginx, Docker
APIエンドポイント一覧:
/api/v1/contacts,/api/v1/contacts/{contact},/api/v1/contacts,/api/v1/contacts/{contact},/api/v1/contacts/{contact}
開発環境URL:
http://localhost(127.0.0.1)mysql(:8080)
作成者:高橋 秀和
