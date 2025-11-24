# GitHub Actions SSHデプロイ設定ガイド

## エラーの原因

YAMLファイルで複数行の文字列を直接記述する場合、適切な記法（`|`や`>`）を使う必要があります。また、**SSH秘密鍵を直接コードに書くのはセキュリティ上非常に危険**です。

## Secretsの設定

GitHubリポジトリの **Settings > Secrets and variables > Actions > New repository secret** で以下のSecretsを設定してください。

### 必須のSecrets

#### 1. `SSH_HOST`
- **Name**: `SSH_HOST`
- **Secret**: LightsailサーバーのIPアドレス
- **例**: `54.248.193.178`

#### 2. `SSH_USER`
- **Name**: `SSH_USER`
- **Secret**: SSH接続用のユーザー名
- **例**: `bitnami`

#### 3. `SSH_KEY`
- **Name**: `SSH_KEY`
- **Secret**: SSH秘密鍵（プライベートキー）の**全体**
- **取得方法**:
  ```bash
  # ローカルでSSH鍵を確認
  cat ~/.ssh/id_rsa
  # または
  cat ~/.ssh/id_ed25519
  ```
- **重要**: 
  - 秘密鍵の内容全体をコピー（`-----BEGIN OPENSSH PRIVATE KEY-----`から`-----END OPENSSH PRIVATE KEY-----`まで）
  - 改行も含めてそのまま貼り付けてください

#### 4. `SSH_REMOTE_PATH`
- **Name**: `SSH_REMOTE_PATH`
- **Secret**: WordPressのインストールパス
- **例**: `/opt/bitnami/wordpress`

### オプションのSecrets

#### 5. `SSH_PORT`（オプション）
- **Name**: `SSH_PORT`
- **Secret**: SSH接続のポート番号
- **デフォルト**: `22`（設定しない場合は22が使用されます）

## Secrets設定の手順

1. GitHubリポジトリにアクセス
2. **Settings** → **Secrets and variables** → **Actions**
3. **New repository secret** をクリック
4. 各Secretを追加：

```
Secret 1:
Name: SSH_HOST
Secret: 54.248.193.178

Secret 2:
Name: SSH_USER
Secret: bitnami

Secret 3:
Name: SSH_KEY
Secret: -----BEGIN OPENSSH PRIVATE KEY-----
（秘密鍵の内容全体を貼り付け）
-----END OPENSSH PRIVATE KEY-----

Secret 4:
Name: SSH_REMOTE_PATH
Secret: /opt/bitnami/wordpress
```

## SSH鍵の準備

### 既存のSSH鍵を使用する場合

```bash
# ローカルで秘密鍵を確認
cat ~/.ssh/id_rsa
# または
cat ~/.ssh/id_ed25519
```

### 新しいSSH鍵を生成する場合

```bash
# SSH鍵を生成
ssh-keygen -t ed25519 -C "github-actions-deploy" -f ~/.ssh/github_actions_deploy

# 公開鍵をLightsailサーバーに追加
ssh-copy-id -i ~/.ssh/github_actions_deploy.pub bitnami@your-lightsail-ip

# 秘密鍵の内容をコピー（GitHub Secretsに設定）
cat ~/.ssh/github_actions_deploy
```

### Lightsailサーバーで公開鍵を設定

```bash
# LightsailにSSH接続
ssh bitnami@your-lightsail-ip

# 公開鍵を追加
mkdir -p ~/.ssh
chmod 700 ~/.ssh
echo "（公開鍵の内容）" >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
```

## ワークフローの動作

1. `main`ブランチにプッシュされると自動的にデプロイが実行されます
2. `wp-content/themes/`ディレクトリの内容がサーバーに転送されます
3. ファイルのパーミッションが自動的に設定されます

## デプロイ先の確認

デプロイ先のパスは以下のようになります：

```
{SSH_REMOTE_PATH}/wp-content/themes/
```

例：`SSH_REMOTE_PATH`が`/opt/bitnami/wordpress`の場合
```
/opt/bitnami/wordpress/wp-content/themes/
```

## セキュリティの注意事項

1. **秘密鍵の保護**: 秘密鍵は絶対にコードに直接書かないでください
2. **Secretsの管理**: SecretsはGitHub上で暗号化されて保存されます
3. **鍵のローテーション**: 定期的にSSH鍵を更新することを推奨します

## トラブルシューティング

### SSH接続エラーが発生する場合

1. **SSH_HOST**が正しいか確認
2. **SSH_KEY**が正しく設定されているか確認（改行を含む全体）
3. LightsailのセキュリティグループでSSH（ポート22）が許可されているか確認
4. サーバー上で公開鍵が正しく設定されているか確認

### パーミッションエラーが発生する場合

- `sudo`コマンドが使用できるユーザーか確認
- または、パーミッション設定のステップを削除して手動で設定

