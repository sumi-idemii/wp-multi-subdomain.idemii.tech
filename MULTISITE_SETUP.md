# マルチサイト設定ガイド

## 現在の状況

`wp-config.php`で`WP_ALLOW_MULTISITE`は`true`に設定されていますが、`MULTISITE`は一時的にコメントアウトされています。

## マルチサイトを有効化する手順

### ステップ1: WordPressのインストールを完了

1. ブラウザで `http://localhost:8000` にアクセス
2. WordPressのインストールウィザードを完了
3. 管理画面にログイン

### ステップ2: マルチサイトの有効化

1. WordPress管理画面にログイン（`http://localhost:8000/wp-admin`）
2. **ツール** → **ネットワークの設置** にアクセス
3. ネットワークのタイプを選択：
   - **サブドメイン型** を選択（推奨）
   - または **サブディレクトリ型** を選択
4. ネットワーク名と管理者メールアドレスを確認
5. **インストール** をクリック

### ステップ3: wp-config.phpと.htaccessの更新

WordPressが自動的に生成したコードを`wp-config.php`と`.htaccess`に追加します。

**wp-config.phpへの追加:**

`/* That's all, stop editing! Happy publishing. */`の前に、以下のようなコードを追加：

```php
define( 'MULTISITE', true );
define( 'SUBDOMAIN_INSTALL', true );
define( 'DOMAIN_CURRENT_SITE', 'localhost' );
define( 'PATH_CURRENT_SITE', '/' );
define( 'SITE_ID_CURRENT_SITE', 1 );
define( 'BLOG_ID_CURRENT_SITE', 1 );
```

**注意**: 本番環境では`DOMAIN_CURRENT_SITE`を実際のドメインに変更してください。

**.htaccessの更新:**

WordPressが表示するリライトルールを`.htaccess`に追加します。

### ステップ4: ブラウザを再読み込み

設定を反映するため、ブラウザを再読み込みします。

### ステップ5: ネットワーク管理画面にアクセス

マルチサイトが有効化されると、ネットワーク管理画面が利用可能になります。

## トラブルシューティング

### エラー: "Table 'wp_blogs' doesn't exist"

このエラーは、`MULTISITE`が`true`に設定されているが、マルチサイトのデータベーステーブルが作成されていない場合に発生します。

**解決方法:**
1. `wp-config.php`で`MULTISITE`をコメントアウト（現在の状態）
2. WordPressのインストールを完了
3. 管理画面からマルチサイトを有効化

### エラー: "ネットワークの設置"が表示されない

**確認事項:**
- `WP_ALLOW_MULTISITE`が`true`に設定されているか
- すべてのプラグインを無効化しているか
- ブラウザのキャッシュをクリアしているか

### ローカル環境でのサブドメイン設定

ローカル開発環境でサブドメインを使用する場合、`/etc/hosts`ファイルを編集：

```bash
sudo nano /etc/hosts
```

以下の行を追加：

```
127.0.0.1 localhost
127.0.0.1 site1.localhost
127.0.0.1 site2.localhost
```

## 本番環境（Lightsail）での設定

本番環境では、`wp-config.php`の`DOMAIN_CURRENT_SITE`を実際のドメインに変更：

```php
define( 'DOMAIN_CURRENT_SITE', 'your-domain.com' );
```

また、DNSでワイルドカードレコードを設定：

```
*.your-domain.com.  IN  A  YOUR_SERVER_IP
```

