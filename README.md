# WordPress マルチサイト（サブドメイン型）ローカル環境構築ガイド

## 概要

macOS (Apple Silicon) 上で Apache + PHP + MySQL を使用した WordPress マルチサイト（サブドメイン型）のローカル開発環境を構築します。

## 前提条件

- macOS (Apple Silicon)
- Homebrew がインストールされていること

## セットアップ手順

### 1. 初期セットアップスクリプトの実行

```bash
cd /Users/sumikazuhisa/project/wordpress/wp-multi-subdomain.idemii.tech
chmod +x setup.sh
./setup.sh
```

このスクリプトは以下を実行します：
- Homebrew の確認
- Apache, PHP, MySQL のインストール
- ディレクトリ構造の作成
- MySQL データベースの作成

### 2. Apache 設定

#### 2.1 バーチャルホスト設定ファイルの配置

Homebrew でインストールした Apache の設定ファイルを編集します：

```bash
# Apache設定ファイルの場所を確認
brew info httpd
```

通常、設定ファイルは `/usr/local/etc/httpd/httpd.conf` または `/opt/homebrew/etc/httpd/httpd.conf` にあります。

#### 2.2 httpd.conf の編集

1. バーチャルホスト設定を有効化：

```apache
# 以下の行のコメントを外す、または追加
Include /Users/sumikazuhisa/project/wordpress/wp-multi-subdomain.idemii.tech/config/apache/httpd-vhosts.conf
```

2. `mod_rewrite` が有効になっていることを確認：

```apache
LoadModule rewrite_module lib/httpd/modules/mod_rewrite.so
```

3. メインの DocumentRoot の `AllowOverride` を確認：

```apache
<Directory "/usr/local/var/www">
    AllowOverride All
    Require all granted
</Directory>
```

設定確認スクリプトを実行：

```bash
chmod +x scripts/configure-apache.sh
./scripts/configure-apache.sh
```

#### 2.3 /etc/hosts の設定

```bash
sudo nano /etc/hosts
```

以下を追加：

```
127.0.0.1 localhost
127.0.0.1 subA.localhost
127.0.0.1 subB.localhost
```

**注意**: `127.0.0.1 localhost` は通常既に存在しますが、念のため確認してください。

### 3. PHP 設定

#### 3.1 PHP 設定ファイルの場所を確認

```bash
php --ini
```

#### 3.2 php.ini の編集

`config/php/php.ini.custom` の内容を参考に、php.ini の該当箇所を編集してください。

主な設定項目：
- `memory_limit = 256M`
- `max_execution_time = 120`
- `upload_max_filesize = 64M`
- `post_max_size = 64M`
- `date.timezone = Asia/Tokyo`

#### 3.3 必要な PHP 拡張の確認

```bash
chmod +x scripts/check-php-extensions.sh
./scripts/check-php-extensions.sh
```

不足している場合は：

```bash
# 例: mbstring が不足している場合
brew install php@8.3-mbstring
```

### 4. MySQL 設定

#### 4.1 データベース作成

```bash
chmod +x scripts/create-database.sh
./scripts/create-database.sh
```

データベース情報：
- データベース名: `wordpress_multisite_apache`
- ユーザー名: `wp_user_apache`
- パスワード: `wp_password_apache`
- ホスト: `localhost`

#### 4.2 MySQL 設定ファイル（オプション）

必要に応じて、`config/mysql/my.cnf` の内容を MySQL の設定ファイルに反映してください。

Homebrew でインストールした MySQL の設定ファイルは通常：
- `/usr/local/etc/my.cnf` または
- `/opt/homebrew/etc/my.cnf`

### 5. WordPress のインストール

#### 5.1 WordPress のダウンロードと配置

```bash
chmod +x scripts/install-wordpress.sh
./scripts/install-wordpress.sh
```

このスクリプトは最新の WordPress をダウンロードして `public/` ディレクトリに配置します。

#### 5.2 wp-config.php の設定

`public/wp-config.php` を編集して、データベース情報を設定してください：

```php
define( 'DB_NAME', 'wordpress_multisite_apache' );
define( 'DB_USER', 'wp_user_apache' );
define( 'DB_PASSWORD', 'wp_password_apache' );
define( 'DB_HOST', 'localhost' );

// 文字セット
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );
```

#### 5.3 マルチサイト設定の追加

```bash
chmod +x scripts/add-multisite-config.sh
./scripts/add-multisite-config.sh
```

または、手動で `wp-config.php` に以下を追加：

```php
/* マルチサイト設定 */
define( 'WP_ALLOW_MULTISITE', true );
```

**注意**: この時点では `.htaccess` の修正は不要です。管理画面からマルチサイト設定を完了した後に作業します。

### 6. サービスの起動

```bash
chmod +x scripts/start-services.sh
./scripts/start-services.sh
```

または手動で：

```bash
brew services start httpd
brew services start mysql
```

### 7. WordPress のセットアップ

1. ブラウザで以下にアクセス：
   - http://localhost

2. WordPress の初期セットアップを完了：
   - サイトタイトル、管理者ユーザー名、パスワードなどを設定

3. 管理画面にログイン：
   - http://localhost/wp-admin

4. マルチサイト設定：
   - ツール > ネットワーク設定 からマルチサイトを有効化
   - 管理画面の指示に従って `.htaccess` と `wp-config.php` を更新

## よく使うコマンド

### サービスの起動/停止

```bash
# 起動
./scripts/start-services.sh
# または
brew services start httpd
brew services start mysql

# 停止
./scripts/stop-services.sh
# または
brew services stop httpd
brew services stop mysql

# 状態確認
brew services list
```

### Apache の再起動（設定変更後）

```bash
brew services restart httpd
```

### MySQL への接続

```bash
mysql -u wp_user_apache -p wordpress_multisite_apache
# パスワード: wp_password_apache
```

### ログの確認

```bash
# Apache エラーログ
tail -f /usr/local/var/log/httpd/error_log
# または
tail -f /opt/homebrew/var/log/httpd/error_log

# PHP エラーログ
tail -f /usr/local/var/log/php_errors.log

# MySQL エラーログ
tail -f /usr/local/var/log/mysql/error.log
# または
tail -f /opt/homebrew/var/log/mysql/error.log
```

## トラブルシューティング

### Apache が起動しない

1. ポート80が使用中でないか確認：
```bash
sudo lsof -i :80
```

2. 設定ファイルの構文チェック：
```bash
httpd -t
```

### PHP が動作しない

1. PHP のバージョン確認：
```bash
php -v
```

2. Apache に PHP モジュールが読み込まれているか確認：
```bash
httpd -M | grep php
```

### データベースに接続できない

1. MySQL が起動しているか確認：
```bash
brew services list | grep mysql
```

2. ユーザーとデータベースが正しく作成されているか確認：
```bash
mysql -u root -e "SHOW DATABASES;"
mysql -u root -e "SELECT User, Host FROM mysql.user;"
```

## 注意事項

- **PHP ビルトインサーバーは使用しない**: この環境では Apache を使用します。`php -S` コマンドは使用しないでください。
- バーチャルサブドメインの設定は後から依頼することとなります。
- `.htaccess` の修正は、管理画面でマルチサイト設定を完了した後に行います。

## ディレクトリ構造

```
wp-multi-subdomain.idemii.tech/
├── config/
│   ├── apache/
│   │   ├── httpd-vhosts.conf          # バーチャルホスト設定
│   │   └── httpd.conf.patch           # httpd.conf への追加内容（参考）
│   ├── php/
│   │   └── php.ini.custom             # PHP設定（参考）
│   └── mysql/
│       └── my.cnf                     # MySQL設定（参考）
├── scripts/
│   ├── setup.sh                       # 初期セットアップ
│   ├── create-database.sh             # データベース作成
│   ├── install-wordpress.sh           # WordPressインストール
│   ├── add-multisite-config.sh       # マルチサイト設定追加
│   ├── configure-apache.sh            # Apache設定確認
│   ├── check-php-extensions.sh        # PHP拡張確認
│   ├── start-services.sh              # サービス起動
│   └── stop-services.sh               # サービス停止
├── public/                             # Apache ドキュメントルート（WordPressファイルを配置）
└── README.md                           # このファイル
```

## 次のステップ

1. WordPress 管理画面でマルチサイトを有効化
2. `.htaccess` の修正（管理画面の指示に従う）
3. バーチャルサブドメインの設定（後から依頼予定）
