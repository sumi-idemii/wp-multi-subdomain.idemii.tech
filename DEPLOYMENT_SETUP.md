# GitHub Actions デプロイ設定ガイド（完全版）

## 概要

このガイドでは、GitHub Actionsを使用してWordPressテーマをLightsailサーバーにデプロイする方法を説明します。

## 前提条件

- LightsailサーバーへのSSHアクセス権限
- GitHubリポジトリへの書き込み権限

## ステップ1: SSH鍵の生成

### ローカルでSSH鍵を生成

```bash
# 新しいSSH鍵を生成（パスフレーズなし推奨）
ssh-keygen -t rsa -b 4096 -C "github-actions-deploy" -f ~/.ssh/github_deploy_key -N ""

# 生成されたファイルを確認
ls -la ~/.ssh/github_deploy_key*
```

これにより、以下の2つのファイルが生成されます：
- `~/.ssh/github_deploy_key`（秘密鍵）
- `~/.ssh/github_deploy_key.pub`（公開鍵）

## ステップ2: サーバーに公開鍵を追加

### 方法1: ssh-copy-idを使用（推奨）

```bash
ssh-copy-id -i ~/.ssh/github_deploy_key.pub bitnami@your-lightsail-ip
```

### 方法2: 手動で追加

```bash
# 公開鍵の内容を表示
cat ~/.ssh/github_deploy_key.pub

# サーバーにSSH接続
ssh bitnami@your-lightsail-ip

# サーバー側で実行
mkdir -p ~/.ssh
chmod 700 ~/.ssh
echo "（公開鍵の内容を貼り付け）" >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
```

## ステップ3: GitHub Secretsの設定

### 秘密鍵をBase64エンコード

```bash
# 方法1: Base64エンコード（推奨）
base64 ~/.ssh/github_deploy_key

# 方法2: 通常の形式（改行を含む）
cat ~/.ssh/github_deploy_key
```

### GitHub Secretsに追加

1. GitHubリポジトリの **Settings** → **Secrets and variables** → **Actions** に移動
2. **New repository secret** をクリック
3. 以下のSecretsを追加：

#### SSH_KEY
- **Name**: `SSH_KEY`
- **Secret**: 
  - Base64エンコードした場合: エンコードされた文字列全体を貼り付け
  - 通常の形式の場合: 秘密鍵全体（`-----BEGIN`から`-----END`まで、改行を含む）を貼り付け

#### SSH_HOST
- **Name**: `SSH_HOST`
- **Secret**: LightsailサーバーのIPアドレス（例: `54.248.193.178`）

#### SSH_USER
- **Name**: `SSH_USER`
- **Secret**: SSHユーザー名（例: `bitnami`）

#### SSH_REMOTE_PATH
- **Name**: `SSH_REMOTE_PATH`
- **Secret**: WordPressのインストールパス（例: `/opt/bitnami/wordpress`）

#### SSH_PORT（オプション）
- **Name**: `SSH_PORT`
- **Secret**: SSHポート番号（デフォルト: `22`）

## ステップ4: 接続テスト

### ローカルで接続テスト

```bash
# SSH接続をテスト
ssh -i ~/.ssh/github_deploy_key bitnami@your-lightsail-ip

# 接続できれば成功
```

## ステップ5: ワークフローの確認

ワークフローファイル（`.github/workflows/deploy.yml`）は既に設定済みです。

## トラブルシューティング

### エラー: `ssh: no key found`

**原因**: SSH鍵の形式が正しくない

**解決方法**:
1. 秘密鍵が正しい形式か確認：
   ```bash
   head -1 ~/.ssh/github_deploy_key
   # 出力: -----BEGIN OPENSSH PRIVATE KEY----- または -----BEGIN RSA PRIVATE KEY-----
   ```

2. Base64エンコードを使用する場合：
   ```bash
   base64 ~/.ssh/github_deploy_key | pbcopy  # macOS
   # GitHub Secretsに貼り付け
   ```

3. 通常の形式を使用する場合：
   - 秘密鍵全体をコピー（改行を含む）
   - `-----BEGIN`から`-----END`まで全て含める

### エラー: `Permission denied (publickey)`

**原因**: サーバー側の公開鍵が正しく設定されていない

**解決方法**:
1. サーバー側で公開鍵を確認：
   ```bash
   ssh bitnami@your-lightsail-ip
   cat ~/.ssh/authorized_keys
   ```

2. 公開鍵が存在しない場合、追加：
   ```bash
   # ローカルで公開鍵をコピー
   cat ~/.ssh/github_deploy_key.pub
   
   # サーバー側で追加
   echo "（公開鍵の内容）" >> ~/.ssh/authorized_keys
   chmod 600 ~/.ssh/authorized_keys
   ```

### エラー: `ssh: handshake failed`

**原因**: SSH鍵の認証に失敗

**解決方法**:
1. SSH鍵のパーミッションを確認：
   ```bash
   ls -la ~/.ssh/github_deploy_key
   # 出力: -rw------- (600)
   ```

2. サーバー側のSSH設定を確認：
   ```bash
   ssh bitnami@your-lightsail-ip
   sudo cat /etc/ssh/sshd_config | grep -i "PubkeyAuthentication"
   # 出力: PubkeyAuthentication yes
   ```

## ベストプラクティス

1. **SSH鍵の管理**
   - デプロイ専用のSSH鍵を使用
   - 定期的に鍵をローテーション

2. **セキュリティ**
   - パスフレーズなしの鍵を使用（GitHub Actionsで自動化する場合）
   - サーバー側でSSH鍵のアクセスを制限

3. **デバッグ**
   - GitHub Actionsのログを確認
   - ローカルで接続テストを実施

## 参考リンク

- [GitHub Actions公式ドキュメント](https://docs.github.com/ja/actions)
- [SSH鍵の生成と管理](https://docs.github.com/ja/authentication/connecting-to-github-with-ssh/generating-a-new-ssh-key-and-adding-it-to-the-ssh-agent)

