# リポジトリ構成について

## 現在の状況

現在のリポジトリにはWordPressコア全体（約118MB）が含まれています。

## 推奨される構成

Lightsailに既にWordPressがインストールされている場合、以下の構成が推奨されます：

```
wp-multi-subdomain.idemii.tech/
├── wp-content/
│   └── themes/
│       └── [カスタムテーマ]/  ← このディレクトリのみをGitで管理
├── .gitignore
├── README.md
└── [その他のドキュメント]
```

## メリット

1. **リポジトリサイズの削減**: WordPressコア（約100MB以上）を除外
2. **既存インストールとの整合性**: Lightsailの既存WordPressと競合しない
3. **管理の簡素化**: カスタムテーマのみを管理
4. **デプロイの簡素化**: テーマディレクトリのみをコピー

## 注意事項

- WordPressコアファイルは既にLightsailに存在するため、Gitで管理する必要はありません
- `wp-config.php`と`.htaccess`も既存のものを使用します
- カスタムテーマのみを開発・管理します

