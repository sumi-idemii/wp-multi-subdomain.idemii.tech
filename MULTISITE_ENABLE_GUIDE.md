# マルチサイト有効化ガイド（サブドメイン型）

## 現在の状態

- `WP_ALLOW_MULTISITE`は`true`に設定済み
- `MULTISITE`は`false`（まだ有効化されていない）

## マルチサイトを有効化する手順

### ステップ1: 管理画面にログイン

1. ブラウザで `http://localhost:8000/wp-admin` にアクセス
2. WordPress管理画面にログイン

### ステップ2: ネットワークの設置にアクセス

**重要**: 「設定 > ネットワーク」ではなく、**「ツール > ネットワークの設置」**にアクセスしてください。

1. 左メニューから **ツール** をクリック
2. **ネットワークの設置** をクリック

### ステップ3: ネットワークタイプを選択

1. **サブドメイン型** を選択
   - 例: `site1.localhost`, `site2.localhost`
2. ネットワーク名と管理者メールアドレスを確認
3. **インストール** をクリック

### ステップ4: wp-config.phpと.htaccessを更新

WordPressが自動的に生成したコードをコピーして、それぞれのファイルに追加します。

#### wp-config.phpへの追加

`/* That's all, stop editing! Happy publishing. */`の**前**に、以下のようなコードを追加：

```php
define( 'MULTISITE', true );
define( 'SUBDOMAIN_INSTALL', true );
define( 'DOMAIN_CURRENT_SITE', 'localhost' );
define( 'PATH_CURRENT_SITE', '/' );
define( 'SITE_ID_CURRENT_SITE', 1 );
define( 'BLOG_ID_CURRENT_SITE', 1 );
```

**注意**: 本番環境では`DOMAIN_CURRENT_SITE`を実際のドメインに変更してください。

#### .htaccessの更新

WordPressが表示するリライトルールを`.htaccess`に追加します。

### ステップ5: ブラウザを再読み込み

設定を反映するため、ブラウザを再読み込みします。

### ステップ6: ネットワーク管理画面にアクセス

マルチサイトが有効化されると、以下のメニューが表示されます：

- **ネットワーク管理** > **ダッシュボード**
- **ネットワーク管理** > **サイト**
- **ネットワーク管理** > **ユーザー**
- **ネットワーク管理** > **テーマ**
- **ネットワーク管理** > **プラグイン**
- **ネットワーク管理** > **設定**

また、各サイトの管理画面からも「設定 > ネットワーク」が表示されるようになります。

## トラブルシューティング

### 「ツール > ネットワークの設置」が表示されない

**確認事項:**
1. `WP_ALLOW_MULTISITE`が`true`に設定されているか
2. すべてのプラグインを無効化しているか
3. ブラウザのキャッシュをクリアしているか
4. 管理画面からログアウトして再度ログインしているか

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

### エラー: "Table 'wp_blogs' doesn't exist"

このエラーは、`MULTISITE`が`true`に設定されているが、マルチサイトのデータベーステーブルが作成されていない場合に発生します。

**解決方法:**
1. `wp-config.php`で`MULTISITE`を`false`に設定（現在の状態）
2. 「ツール > ネットワークの設置」からマルチサイトを有効化
3. WordPressが自動的にテーブルを作成します

## 本番環境（Lightsail）での設定

本番環境では、`wp-config.php`の`DOMAIN_CURRENT_SITE`を実際のドメインに変更：

```php
define( 'DOMAIN_CURRENT_SITE', 'your-domain.com' );
```

また、DNSでワイルドカードレコードを設定：

```
*.your-domain.com.  IN  A  YOUR_SERVER_IP
```

