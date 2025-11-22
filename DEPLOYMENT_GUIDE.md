# Lightsailデプロイガイド

## 重要な注意事項

このリポジトリには**WordPressコアファイルは含まれていません**。Lightsailに既にWordPressがインストールされている場合、以下の点に注意してください。

## 現在のリポジトリ構成

リポジトリには以下のファイルのみが含まれています：
- ドキュメントファイル（README.md、SETUP_GUIDE.mdなど）
- セットアップスクリプト（setup.sh、database-setup.sql）
- 環境変数テンプレート（env.example）

**WordPressコアファイル（wp-admin、wp-includes、wp-*.php）は含まれていません。**

## 安全なデプロイ方法

### 方法1: テーマディレクトリのみをデプロイ（推奨）

既存のWordPressインストールを保護するため、テーマディレクトリのみをデプロイします：

```bash
# LightsailにSSH接続
ssh bitnami@your-lightsail-ip

# WordPressのテーマディレクトリに移動
cd /opt/bitnami/wordpress/wp-content/themes

# リポジトリをクローン（別のディレクトリに）
cd /tmp
git clone https://github.com/sumi-idemii/wp-multi-subdomain.idemii.tech.git

# カスタムテーマがある場合のみコピー
# 例: カスタムテーマが my-theme の場合
# cp -r wp-multi-subdomain.idemii.tech/wp-content/themes/my-theme /opt/bitnami/wordpress/wp-content/themes/
```

### 方法2: ドキュメントのみをデプロイ

ドキュメントやスクリプトのみが必要な場合：

```bash
# LightsailにSSH接続
ssh bitnami@your-lightsail-ip

# 作業ディレクトリを作成
mkdir -p ~/wordpress-docs
cd ~/wordpress-docs

# リポジトリをクローン
git clone https://github.com/sumi-idemii/wp-multi-subdomain.idemii.tech.git
```

### 方法3: 既存WordPressディレクトリに直接クローン（非推奨）

⚠️ **警告**: この方法は既存のWordPressファイルを上書きする可能性があります。

```bash
# 既存のWordPressディレクトリに直接クローンするのは避けてください
# 以下のコマンドは実行しないでください：
# cd /opt/bitnami/wordpress
# git clone https://github.com/sumi-idemii/wp-multi-subdomain.idemii.tech.git .
```

## 既存WordPressへの影響

### 安全な点

1. **WordPressコアファイルは含まれていない**
   - `wp-admin/`、`wp-includes/`、`wp-*.php`はリポジトリに含まれていません
   - 既存のWordPressコアファイルは影響を受けません

2. **設定ファイルは除外されている**
   - `wp-config.php`と`.htaccess`は`.gitignore`に含まれています
   - 既存の設定ファイルは上書きされません

### 注意が必要な点

1. **カスタムテーマがある場合**
   - カスタムテーマをリポジトリに追加した場合、デプロイ時に既存のテーマと競合する可能性があります
   - テーマ名が同じ場合は上書きされる可能性があります

2. **デプロイ方法**
   - 既存のWordPressディレクトリに直接`git clone`や`git pull`を実行すると、空のディレクトリが作成される可能性があります
   - 適切なデプロイ方法を選択してください

## 推奨されるデプロイ手順

### ステップ1: リポジトリの確認

```bash
# ローカルでリポジトリの内容を確認
git ls-files
```

### ステップ2: バックアップの作成

```bash
# Lightsailで既存のWordPressをバックアップ
cd /opt/bitnami/wordpress
sudo tar -czf ~/wordpress-backup-$(date +%Y%m%d).tar.gz .
```

### ステップ3: 安全なデプロイ

```bash
# テーマディレクトリのみをデプロイする場合
cd /opt/bitnami/wordpress/wp-content/themes
# カスタムテーマを手動でコピーまたはシンボリックリンクを作成
```

## トラブルシューティング

### 既存のWordPressが動作しなくなった場合

1. バックアップから復元：
   ```bash
   cd /opt/bitnami/wordpress
   sudo tar -xzf ~/wordpress-backup-YYYYMMDD.tar.gz
   ```

2. ファイルのパーミッションを確認：
   ```bash
   sudo chown -R bitnami:daemon /opt/bitnami/wordpress
   sudo find /opt/bitnami/wordpress -type d -exec chmod 755 {} \;
   sudo find /opt/bitnami/wordpress -type f -exec chmod 644 {} \;
   ```

## まとめ

- ✅ リポジトリにはWordPressコアファイルが含まれていないため、既存のWordPressは基本的に安全です
- ✅ `wp-config.php`と`.htaccess`は除外されているため、設定ファイルは保護されています
- ⚠️ デプロイ方法に注意し、既存のWordPressディレクトリに直接クローンしないでください
- ⚠️ カスタムテーマを追加する場合は、テーマ名の競合に注意してください

