# WordPress マルチサイト セットアップガイド

このガイドでは、WordPressマルチサイト（サブドメイン型）をセットアップする手順を詳しく説明します。

## 前提条件

- PHP 7.4以上（現在のバージョン: `php -v`で確認）
- MySQL 5.7以上 または MariaDB 10.3以上
- Webサーバー（Apache、Nginx、またはPHPビルトインサーバー）

## ステップ1: MySQL/MariaDBのインストール

### macOS (Homebrew)

```bash
# MySQLをインストール
brew install mysql

# MySQLを起動
brew services start mysql

# または、MariaDBをインストール
brew install mariadb
brew services start mariadb
```

### Ubuntu/Debian

```bash
sudo apt-get update
sudo apt-get install mysql-server
# または
sudo apt-get install mariadb-server
```

### インストール確認

```bash
mysql --version
```

## ステップ2: データベースの作成

### 方法A: SQLスクリプトを使用（推奨）

1. `database-setup.sql`ファイルを編集して、パスワードを設定します：

```bash
nano database-setup.sql
```

`your_secure_password`を強力なパスワードに変更します。

2. MySQLに接続してスクリプトを実行します：

```bash
mysql -u root -p < database-setup.sql
```

パスワードを求められたら、MySQLのrootパスワードを入力します。

### 方法B: 手動でSQLを実行

```bash
mysql -u root -p
```

MySQLプロンプトで以下を実行：

```sql
CREATE DATABASE wordpress_multisite CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'wp_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON wordpress_multisite.* TO 'wp_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

## ステップ3: 環境変数の設定

1. `.env`ファイルを作成します：

```bash
cp env.example .env
```

2. `.env`ファイルを編集します：

```bash
nano .env
```

以下の値を実際のデータベース情報に変更します：

```
DB_NAME=wordpress_multisite
DB_USER=wp_user
DB_PASSWORD=your_secure_password_here  # ステップ2で設定したパスワード
DB_HOST=localhost
```

## ステップ4: Webサーバーの起動

### PHPビルトインサーバーを使用（開発環境）

```bash
php -S localhost:8000
```

### Apacheを使用する場合

ドキュメントルートをこのディレクトリに設定します。

### Nginxを使用する場合

ルートディレクトリをこのディレクトリに設定します。

## ステップ5: WordPressのインストール

1. ブラウザで `http://localhost:8000` にアクセスします
2. 言語を選択します（日本語を選択可能）
3. 「さあ、始めましょう！」をクリックします
4. データベース接続情報が`.env`ファイルから自動的に読み込まれます
   - データベース名: `wordpress_multisite`
   - ユーザー名: `wp_user`
   - パスワード: `.env`で設定したパスワード
   - データベースホスト: `localhost`
   - テーブル接頭辞: `wp_`（デフォルト）
5. 「送信」をクリックします
6. 「インストール実行」をクリックします
7. サイト情報を入力します：
   - サイトのタイトル: 任意のタイトル
   - ユーザー名: 管理者ユーザー名（例: `admin`）
   - パスワード: 強力なパスワード（自動生成も可能）
   - メールアドレス: 管理者のメールアドレス
8. 「WordPressをインストール」をクリックします
9. インストール完了後、「ログイン」をクリックして管理画面にログインします

## ステップ6: マルチサイト機能の有効化

1. WordPress管理画面にログインします（`http://localhost:8000/wp-admin`）
2. 左メニューから **ツール** > **ネットワークの設置** にアクセスします
3. ネットワークのタイプを選択します：
   - **サブドメイン型** を選択（推奨）
     - 例: `site1.example.com`, `site2.example.com`
   - または **サブディレクトリ型** を選択
     - 例: `example.com/site1`, `example.com/site2`
4. ネットワーク名と管理者メールアドレスを確認します
5. **インストール** をクリックします

### マルチサイト設定後の作業

WordPressが`wp-config.php`と`.htaccess`を自動的に更新します。表示されたコードをコピーして、それぞれのファイルに追加します。

**wp-config.phpへの追加:**

`/* That's all, stop editing! Happy publishing. */`の前に、表示されたコードを追加します。例：

```php
define( 'MULTISITE', true );
define( 'SUBDOMAIN_INSTALL', true );
define( 'DOMAIN_CURRENT_SITE', 'localhost' );
define( 'PATH_CURRENT_SITE', '/' );
define( 'SITE_ID_CURRENT_SITE', 1 );
define( 'BLOG_ID_CURRENT_SITE', 1 );
```

**.htaccessへの追加:**

`.htaccess`ファイルの`# BEGIN WordPress`の前に、表示されたコードを追加します。

6. ブラウザを再読み込みします
7. ネットワーク管理画面にログインします
8. 必要に応じて、変更をGitにコミットします：

```bash
git add wp-config.php .htaccess
git commit -m "Enable WordPress multisite"
```

## ステップ7: サイトの追加

マルチサイトが有効化されると、ネットワーク管理画面から新しいサイトを追加できます：

1. **サイト** > **新規追加** にアクセスします
2. サイト情報を入力します：
   - サイトアドレス: サブドメイン名（例: `site1`）
   - サイトタイトル: サイトのタイトル
   - 管理者メール: サイト管理者のメールアドレス
3. **サイトを追加** をクリックします

## トラブルシューティング

### データベース接続エラー

- `.env`ファイルの設定が正しいか確認してください
- MySQL/MariaDBが起動しているか確認してください：
  ```bash
  brew services list  # macOS
  sudo systemctl status mysql  # Linux
  ```
- データベース、ユーザー、パスワードが正しく作成されているか確認してください

### マルチサイトの設定が表示されない

- `wp-config.php`で`WP_ALLOW_MULTISITE`が`true`に設定されているか確認してください
- すべてのプラグインを無効化してから再度試してください
- ブラウザのキャッシュをクリアしてください

### サブドメインにアクセスできない（ローカル開発環境）

ローカル開発環境では、サブドメインを使用するために`/etc/hosts`ファイルを編集する必要があります：

```bash
sudo nano /etc/hosts
```

以下の行を追加します：

```
127.0.0.1 localhost
127.0.0.1 site1.localhost
127.0.0.1 site2.localhost
```

## 次のステップ

- テーマのインストールとカスタマイズ
- プラグインのインストール
- AWS Lightsailへのデプロイ準備

詳細は`README.md`を参照してください。

