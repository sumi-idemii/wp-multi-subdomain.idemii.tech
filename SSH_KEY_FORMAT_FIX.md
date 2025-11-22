# SSH鍵の形式修正ガイド

## 現在の問題

GitHub Secretsの`SSH_KEY`に保存されているデータが、Base64エンコードされたデータのみで、`-----BEGIN`と`-----END`の行が欠けています。

現在の形式：
```
MIIEowIBAAKCAQEAwUOY79of47OIOc8BPhwW4FWukMiuhEdp2N...
```

正しい形式：
```
-----BEGIN RSA PRIVATE KEY-----
MIIEowIBAAKCAQEAwUOY79of47OIOc8BPhwW4FWukMiuhEdp2N...
（複数行のBase64データ）
-----END RSA PRIVATE KEY-----
```

## 解決方法

### 方法1: ローカルで新しいSSH鍵を生成（推奨）

```bash
# 新しいSSH鍵を生成
ssh-keygen -t rsa -b 4096 -C "github-actions" -f ~/.ssh/github_deploy_key -N ""

# 公開鍵をサーバーに追加
ssh-copy-id -i ~/.ssh/github_deploy_key.pub bitnami@your-lightsail-ip

# 秘密鍵を表示（GitHub Secretsにコピー）
cat ~/.ssh/github_deploy_key
```

出力例：
```
-----BEGIN OPENSSH PRIVATE KEY-----
b3BlbnNzaC1rZXktdjEAAAAABG5vbmUAAAAEbm9uZQAAAAAAAAABAAACFwAAAAdzc2gtcn
（複数行のデータ）
-----END OPENSSH PRIVATE KEY-----
```

この**全体**をGitHub Secretsの`SSH_KEY`にコピーしてください。

### 方法2: 既存のBase64データを修正

現在GitHub Secretsに保存されているBase64データを、正しい形式に変換します。

1. **GitHub SecretsからBase64データをコピー**
   - `MIIEowIBAAKCAQEAwUOY79of47OIOc8BPhwW4FWukMiuhEdp2N...`（全体）

2. **以下の形式に変換**

```
-----BEGIN RSA PRIVATE KEY-----
（Base64データを64文字ごとに改行）
-----END RSA PRIVATE KEY-----
```

3. **手動で変換する場合**

Base64データを64文字ごとに改行して、`-----BEGIN RSA PRIVATE KEY-----`と`-----END RSA PRIVATE KEY-----`で囲みます。

例：
```
-----BEGIN RSA PRIVATE KEY-----
MIIEowIBAAKCAQEAwUOY79of47OIOc8BPhwW4FWukMiuhEdp2NY93leJIQA4FZjf
gZ0uJTeKFJzeYurkSAvVn+D4ef5GSkwpr2Rl2gBv4zBlA09F2yDcYtDAJFBWmHxc
（続く...）
-----END RSA PRIVATE KEY-----
```

### 方法3: スクリプトを使用（macOS/Linux）

```bash
# スクリプトを実行
./fix_ssh_key.sh

# GitHub SecretsのBase64データを貼り付け
# スクリプトが正しい形式に変換して表示します
```

## GitHub Secretsの更新手順

1. GitHubリポジトリ → **Settings** → **Secrets and variables** → **Actions**
2. `SSH_KEY`を編集
3. 正しい形式のSSH鍵を貼り付け：
   - `-----BEGIN`で始まる
   - `-----END`で終わる
   - 改行を含む
4. **Update secret**をクリック

## 確認方法

ワークフローを再実行すると、以下のメッセージが表示されます：

- `✓ SSH key format: Valid (starts with BEGIN)` → 成功
- `✗ SSH key format: Invalid` → 失敗（形式を再確認）

## 注意事項

- **Base64エンコードは不要です**。通常の形式（`-----BEGIN`から`-----END`まで）で保存してください。
- 改行を削除したり、1行にまとめたりしないでください。
- 秘密鍵の**全体**をコピーしてください。

