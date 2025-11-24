#!/bin/bash
# SSH鍵を正しい形式に変換するスクリプト

echo "SSH鍵の形式を修正します..."
echo ""

# GitHub SecretsからコピーしたBase64データを入力
read -p "GitHub SecretsのSSH_KEYの内容を貼り付けてください（Base64データのみ）: " BASE64_DATA

# Base64デコード
DECODED=$(echo "$BASE64_DATA" | base64 -d 2>/dev/null)

if [ $? -eq 0 ]; then
    echo ""
    echo "✓ Base64デコード成功"
    echo ""
    echo "デコードされたデータの最初の100文字:"
    echo "$DECODED" | head -c 100
    echo ""
    echo ""
    
    # BEGIN/ENDがあるか確認
    if echo "$DECODED" | grep -q "BEGIN"; then
        echo "✓ 正しい形式のSSH鍵が検出されました"
        echo ""
        echo "=== 以下をGitHub SecretsのSSH_KEYにコピーしてください ==="
        echo "$DECODED"
        echo "=== ここまで ==="
    else
        echo "✗ BEGIN/ENDが見つかりません。RSA鍵として処理します。"
        echo ""
        echo "=== 以下をGitHub SecretsのSSH_KEYにコピーしてください ==="
        echo "-----BEGIN RSA PRIVATE KEY-----"
        echo "$BASE64_DATA" | fold -w 64
        echo "-----END RSA PRIVATE KEY-----"
        echo "=== ここまで ==="
    fi
else
    echo "✗ Base64デコードに失敗しました"
    echo "通常の形式のSSH鍵として処理します。"
    echo ""
    echo "=== 以下をGitHub SecretsのSSH_KEYにコピーしてください ==="
    echo "-----BEGIN RSA PRIVATE KEY-----"
    echo "$BASE64_DATA" | fold -w 64
    echo "-----END RSA PRIVATE KEY-----"
    echo "=== ここまで ==="
fi

