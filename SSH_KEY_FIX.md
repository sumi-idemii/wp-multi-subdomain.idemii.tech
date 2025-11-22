# SSH鍵の設定修正ガイド

## 問題

現在、GitHub Secretsの`SSH_KEY`が正しい形式で保存されていない可能性があります。

## 解決方法

### ステップ1: ローカルでSSH鍵を確認

```bash
# 既存のSSH鍵を確認
cat ~/.ssh/id_rsa
# または
cat ~/.ssh/id_ed25519
```

出力は以下のようになっている必要があります：

```
-----BEGIN OPENSSH PRIVATE KEY-----
（複数行の秘密鍵データ）
-----END OPENSSH PRIVATE KEY-----
```

または

```
-----BEGIN RSA PRIVATE KEY-----
（複数行の秘密鍵データ）
-----END RSA PRIVATE KEY-----
```

### ステップ2: GitHub Secretsを更新

1. **GitHubリポジトリ** → **Settings** → **Secrets and variables** → **Actions** に移動

2. **`SSH_KEY`を編集**（または削除して新規作成）

3. **秘密鍵全体をコピー＆ペースト**
   - `-----BEGIN`から`-----END`まで**全て**を含める
   - **改行も含めて**そのまま貼り付ける
   - 前後に余分な空白や文字を入れない

4. **保存**

### ステップ3: 新しいSSH鍵を生成する場合（推奨）

既存の鍵に問題がある場合は、新しい鍵を生成してください：

```bash
# 新しいSSH鍵を生成（パスフレーズなし）
ssh-keygen -t ed25519 -C "github-actions-deploy" -f ~/.ssh/github_deploy_key -N ""

# 公開鍵をサーバーに追加
ssh-copy-id -i ~/.ssh/github_deploy_key.pub bitnami@your-lightsail-ip

# 秘密鍵を表示（GitHub Secretsにコピー）
cat ~/.ssh/github_deploy_key
```

### ステップ4: 接続テスト

ローカルで接続をテスト：

```bash
ssh -i ~/.ssh/github_deploy_key bitnami@your-lightsail-ip
```

接続できれば成功です。

## 注意事項

- **Base64エンコードは不要です**。通常の形式（改行を含む）でそのまま保存してください。
- 秘密鍵の**全体**をコピーしてください（`-----BEGIN`から`-----END`まで）。
- 改行を削除したり、1行にまとめたりしないでください。

## 確認方法

ワークフローを再実行すると、以下のメッセージが表示されます：

- `✓ SSH key format: Valid (starts with BEGIN)` → 成功
- `✗ SSH key format: Invalid` → 失敗（Secretsを再確認）

