# Docker MySQL セットアップ完了

## 設定内容

Dockerコンテナ（`/Users/sumikazuhisa/project/movabletype/study`）のMySQLを使用するように設定しました。

### データベース情報

- **データベース名**: `wordpress_multisite`
- **ユーザー名**: `wp_user`
- **パスワード**: `wp_password`
- **ホスト**: `127.0.0.1` (localhostのTCP接続)
- **ポート**: `3306`

### 設定ファイル

- `.env`ファイルの`DB_HOST`を`127.0.0.1`に設定済み
- `wp-config.php`のデフォルト値も`127.0.0.1`に更新済み

## 確認方法

### 1. Docker MySQLコンテナの状態確認

```bash
cd /Users/sumikazuhisa/project/movabletype/study
docker-compose ps
```

### 2. データベース接続テスト

```bash
cd /Users/sumikazuhisa/project/wordpress/wp-multi-subdomain.idemii.tech
php -r "require 'wp-config.php'; \$link = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, 3306); if (\$link) { echo 'Connection successful!'; mysqli_close(\$link); } else { echo 'Connection failed: ' . mysqli_connect_error(); }"
```

### 3. WordPressにアクセス

ブラウザで `http://localhost:8000` にアクセスしてください。

## トラブルシューティング

### データベース接続エラーが発生する場合

1. Dockerコンテナが起動しているか確認：
   ```bash
   docker ps | grep mt-mysql
   ```

2. コンテナが停止している場合は起動：
   ```bash
   cd /Users/sumikazuhisa/project/movabletype/study
   docker-compose up -d db
   ```

3. `.env`ファイルの`DB_HOST`が`127.0.0.1`になっているか確認：
   ```bash
   grep DB_HOST .env
   ```

### パスワードを変更する場合

1. Docker MySQLで新しいパスワードを設定：
   ```bash
   docker exec -i mt-mysql mysql -u root -proot <<EOF
   ALTER USER 'wp_user'@'%' IDENTIFIED BY 'new_password';
   ALTER USER 'wp_user'@'localhost' IDENTIFIED BY 'new_password';
   FLUSH PRIVILEGES;
   EOF
   ```

2. `.env`ファイルの`DB_PASSWORD`を更新：
   ```bash
   nano .env
   # DB_PASSWORD=new_password に変更
   ```

## 注意事項

- Docker MySQLコンテナが停止すると、WordPressにアクセスできなくなります
- データベースのデータはDockerボリューム（`db_data`）に保存されています
- 本番環境では、より強力なパスワードを使用してください

