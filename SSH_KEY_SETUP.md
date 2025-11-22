# SSH鍵の設定方法（トラブルシューティング）

## 問題

GitHub ActionsでSSH鍵認証が失敗する場合、SSH鍵の形式に問題がある可能性があります。

## 解決方法1: 通常のSSH鍵をそのまま使用

GitHub Secretsの`SSH_KEY`に、SSH秘密鍵をそのまま（改行を含めて）貼り付けてください：

```
-----BEGIN OPENSSH PRIVATE KEY-----
（秘密鍵の内容）
-----END OPENSSH PRIVATE KEY-----
```

## 解決方法2: Base64エンコードを使用（推奨）

SSH鍵をbase64エンコードして保存すると、改行の問題を回避できます。

### ローカルでSSH鍵をbase64エンコード

```bash
# SSH鍵をbase64エンコード
cat ~/.ssh/id_rsa | base64
# または
cat ~/.ssh/id_ed25519 | base64
```

### GitHub Secretsに設定

1. エンコードされた文字列をコピー
2. GitHubの **Settings > Secrets and variables > Actions** に移動
3. `SSH_KEY`を編集または新規作成
4. エンコードされた文字列を貼り付け

### ワークフローの動作

ワークフローは自動的にbase64デコードを試み、失敗した場合は通常の形式として扱います。

## SSH鍵の確認

### 正しい形式の確認

```bash
# ローカルでSSH鍵を確認
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

## サーバー側の公開鍵の確認

Lightsailサーバーで、公開鍵が正しく設定されているか確認：

```bash
ssh bitnami@your-lightsail-ip
cat ~/.ssh/authorized_keys
```

公開鍵が存在しない場合、追加：

```bash
# ローカルで公開鍵をコピー
cat ~/.ssh/id_rsa.pub
# または
cat ~/.ssh/id_ed25519.pub

# サーバー側で追加
echo "（公開鍵の内容）" >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
```

## トラブルシューティング

### エラー: `ssh: no key found`

- SSH鍵の形式が正しくない可能性があります
- Base64エンコードを試してください
- または、SSH鍵の前後に余分な空白や改行がないか確認してください

### エラー: `ssh: unable to authenticate`

- サーバー側の公開鍵が正しく設定されているか確認
- SSH鍵のパーミッションが正しいか確認（600）
- サーバーのSSH設定を確認

### エラー: `Permission denied (publickey)`

- 公開鍵がサーバーの`~/.ssh/authorized_keys`に正しく追加されているか確認
- サーバー側のSSH設定で公開鍵認証が有効になっているか確認

