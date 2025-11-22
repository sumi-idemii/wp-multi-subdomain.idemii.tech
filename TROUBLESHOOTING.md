# トラブルシューティングガイド

## データベース接続エラーが発生する場合

### 症状
- `http://localhost:8000`にアクセスすると「Error establishing a database connection」が表示される
- 500 Internal Server Errorが発生する

### 原因
1. MySQL/MariaDBがインストールされていない
2. MySQL/MariaDBが起動していない
3. データベースが作成されていない
4. `.env`ファイルの設定が正しくない

### 解決方法

#### ステップ1: MySQLのインストール

```bash
# macOS (Homebrew)
brew install mysql

# MySQLを起動
brew services start mysql
```

#### ステップ2: データベースの作成

1. `database-setup.sql`を編集してパスワードを設定：

```bash
nano database-setup.sql
```

`your_secure_password`を強力なパスワードに変更します。

2. データベースを作成：

```bash
mysql -u root -p < database-setup.sql
```

#### ステップ3: .envファイルの更新

`.env`ファイルを編集して、実際のデータベース情報を設定：

```bash
nano .env
```

以下の値を設定：
- `DB_PASSWORD`: database-setup.sqlで設定したパスワード

#### ステップ4: データベース接続の確認

```bash
mysql -u wp_user -p wordpress_multisite
```

パスワードを入力して接続できれば成功です。

## ポート8000が使用できない場合

### 別のポートを使用する

```bash
php -S localhost:8080
```

ブラウザで `http://localhost:8080` にアクセスします。

### 使用中のポートを確認

```bash
lsof -i :8000
```

### 使用中のプロセスを終了

```bash
# プロセスIDを確認
lsof -ti :8000

# プロセスを終了
kill -9 $(lsof -ti :8000)
```

## PHPサーバーが起動しない場合

### PHPのバージョン確認

```bash
php -v
```

PHP 7.4以上が必要です。

### エラーログの確認

```bash
tail -f wp-content/debug.log
```

WP_DEBUGを有効にするには、`wp-config.php`で以下を設定：

```php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
```

