# WordPress マルチサイト（サブドメイン型）

このプロジェクトは、AWS Lightsail上で動作するWordPressマルチサイト（サブドメイン型）のインストールです。

## 要件

- PHP 7.4以上
- MySQL 5.7以上 または MariaDB 10.3以上
- Apache または Nginx
- mod_rewrite が有効なApache（サブドメイン型マルチサイトの場合）

## セットアップ手順

### 1. データベースの準備

MySQL/MariaDBでデータベースとユーザーを作成します：

```sql
CREATE DATABASE wordpress_multisite CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'wp_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON wordpress_multisite.* TO 'wp_user'@'localhost';
FLUSH PRIVILEGES;
```

### 2. 環境変数の設定

`wp-config.php`では環境変数からデータベース情報を読み込みます。以下の環境変数を設定してください：

```bash
export DB_NAME=wordpress_multisite
export DB_USER=wp_user
export DB_PASSWORD=your_secure_password
export DB_HOST=localhost
```

または、`.env`ファイルを作成して設定することもできます（ただし、`.env`ファイルは`.gitignore`に含まれています）。

### 3. WordPressのインストール

1. ブラウザでサイトにアクセスします
2. WordPressのインストールウィザードに従って初期設定を行います
3. データベース情報を入力します

### 4. マルチサイト機能の有効化

WordPressのインストール後、以下の手順でマルチサイト機能を有効化します：

1. WordPress管理画面にログインします
2. **ツール** > **ネットワークの設置** にアクセスします
3. サブドメイン型を選択します
4. ネットワーク名と管理者メールアドレスを入力します
5. **インストール** をクリックします

### 5. wp-config.phpと.htaccessの更新

マルチサイトのインストール後、WordPressが自動的に`wp-config.php`と`.htaccess`を更新します。これらの変更をGitにコミットしてください。

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

