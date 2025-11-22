# WordPress マルチサイト（サブドメイン型）

このプロジェクトは、AWS Lightsail上で動作するWordPressマルチサイト（サブドメイン型）のインストールです。

## 要件

- PHP 7.4以上
- MySQL 5.7以上 または MariaDB 10.3以上
- Apache または Nginx
- mod_rewrite が有効なApache（サブドメイン型マルチサイトの場合）

## セットアップ手順

### クイックスタート

セットアップスクリプトを実行して、基本的な設定を行います：

```bash
./setup.sh
```

### 1. データベースの準備

#### 方法A: SQLスクリプトを使用（推奨）

1. `database-setup.sql`ファイルを編集して、データベース名、ユーザー名、パスワードを設定します
2. MySQLに接続してスクリプトを実行します：

```bash
mysql -u root -p < database-setup.sql
```

または、MySQLに接続してから実行：

```bash
mysql -u root -p
source database-setup.sql;
```

#### 方法B: 手動でSQLを実行

MySQL/MariaDBに接続して、以下のSQLを実行します：

```sql
CREATE DATABASE wordpress_multisite CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'wp_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON wordpress_multisite.* TO 'wp_user'@'localhost';
FLUSH PRIVILEGES;
```

**重要**: `your_secure_password`を強力なパスワードに変更してください。

### 2. 環境変数の設定

#### 方法A: .envファイルを使用（推奨）

1. `env.example`ファイルを`.env`にコピーします：

```bash
cp env.example .env
```

2. `.env`ファイルを編集して、データベース情報を設定します：

```bash
# エディタで開く（例：nano、vim、VS Codeなど）
nano .env
```

以下の値を実際のデータベース情報に変更します：

```
DB_NAME=wordpress_multisite
DB_USER=wp_user
DB_PASSWORD=your_secure_password_here
DB_HOST=localhost
```

#### 方法B: 環境変数を直接設定

シェルで環境変数を設定します：

```bash
export DB_NAME=wordpress_multisite
export DB_USER=wp_user
export DB_PASSWORD=your_secure_password
export DB_HOST=localhost
```

### 3. Webサーバーの起動

#### ローカル開発環境（PHPビルトインサーバー）

```bash
php -S localhost:8000
```

ブラウザで `http://localhost:8000` にアクセスします。

#### Apache/Nginxを使用する場合

- Apache: ドキュメントルートをこのディレクトリに設定
- Nginx: ルートディレクトリをこのディレクトリに設定

### 4. WordPressのインストール

1. ブラウザでサイトにアクセスします（例：`http://localhost:8000`）
2. 言語を選択します
3. 「さあ、始めましょう！」をクリックします
4. データベース接続情報が`.env`ファイルから自動的に読み込まれます
   - データベース名、ユーザー名、パスワード、データベースホストが正しく設定されていることを確認
5. 「送信」をクリックします
6. 「インストール実行」をクリックします
7. サイト情報を入力します：
   - サイトのタイトル
   - ユーザー名（管理者）
   - パスワード（強力なパスワードを推奨）
   - メールアドレス
8. 「WordPressをインストール」をクリックします

### 5. マルチサイト機能の有効化

WordPressのインストール後、以下の手順でマルチサイト機能を有効化します：

1. WordPress管理画面にログインします（`/wp-admin`）
2. **ツール** > **ネットワークの設置** にアクセスします
3. ネットワークのタイプを選択します：
   - **サブドメイン型** を選択（推奨）
   - または **サブディレクトリ型** を選択
4. ネットワーク名と管理者メールアドレスを確認します
5. **インストール** をクリックします

#### マルチサイト設定後の作業

WordPressが`wp-config.php`と`.htaccess`を自動的に更新します。表示されたコードをコピーして、それぞれのファイルに追加します。

**重要**: マルチサイト設定後、必ず以下を実行してください：

1. ブラウザを再読み込みします
2. ネットワーク管理画面にログインします
3. 必要に応じて、変更をGitにコミットします：

```bash
git add wp-config.php .htaccess
git commit -m "Enable WordPress multisite"
```

### 6. DNS設定（サブドメイン型の場合）

サブドメイン型のマルチサイトを使用する場合、ワイルドカードDNSレコードを設定する必要があります：

```
*.yourdomain.com.  IN  A  YOUR_SERVER_IP
```

または、AWS LightsailのDNSゾーンで設定します。

## AWS Lightsailへのデプロイ

### 1. Gitリポジトリの準備

```bash
git add .
git commit -m "Initial WordPress multisite installation"
git remote add origin <your-repository-url>
git push -u origin master
```

### 2. Lightsailインスタンスでの設定

1. LightsailインスタンスにSSH接続します
2. Gitをインストールします（まだの場合）：
   ```bash
   sudo apt-get update
   sudo apt-get install git
   ```
3. WordPressのディレクトリに移動します：
   ```bash
   cd /opt/bitnami/wordpress
   ```
4. Gitリポジトリをクローンまたはプルします：
   ```bash
   git clone <your-repository-url> .
   # または既存のリポジトリを更新
   git pull origin master
   ```
5. ファイルのパーミッションを設定します：
   ```bash
   sudo chown -R bitnami:daemon /opt/bitnami/wordpress
   sudo find /opt/bitnami/wordpress -type d -exec chmod 755 {} \;
   sudo find /opt/bitnami/wordpress -type f -exec chmod 644 {} \;
   ```
6. `wp-config.php`の環境変数を設定します（Bitnamiの場合は`/opt/bitnami/wordpress/wp-config.php`を直接編集するか、環境変数を設定）

### 3. 自動デプロイの設定（オプション）

GitHub ActionsやGitLab CI/CDを使用して自動デプロイを設定することもできます。

## 注意事項

- `wp-config.php`には機密情報が含まれる可能性があるため、本番環境では環境変数を使用することを推奨します
- `wp-content/uploads/`ディレクトリは`.gitignore`に含まれているため、アップロードされたファイルは別途バックアップが必要です
- マルチサイトの設定後、`wp-config.php`と`.htaccess`が自動的に更新されます。これらの変更をGitにコミットしてください

## トラブルシューティング

### マルチサイトの設定が表示されない

- `wp-config.php`で`WP_ALLOW_MULTISITE`が`true`に設定されているか確認してください
- すべてのプラグインを無効化してから再度試してください

### サブドメインにアクセスできない

- DNSのワイルドカードレコードが正しく設定されているか確認してください
- Apacheの`mod_rewrite`が有効になっているか確認してください
- `.htaccess`ファイルが正しく配置されているか確認してください

## ライセンス

WordPressはGPL v2またはそれ以降のライセンスの下で公開されています。

