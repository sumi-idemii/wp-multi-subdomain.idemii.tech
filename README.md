# MainSite Theme

WordPressマルチサイト用のメインサイトテーマです。

## セットアップ

このテーマディレクトリは独立したGitリポジトリとして管理されています。

### GitHubへの接続

```bash
cd wp-content/themes/MainSite
git remote -v
```

リモートリポジトリ: `https://github.com/sumi-idemii/wp-multi-subdomain.idemii.tech.git`

### 初回コミット

```bash
cd wp-content/themes/MainSite
git add .
git commit -m "Initial commit"
git push -u origin main
```

### 今後の更新

```bash
cd wp-content/themes/MainSite
git add .
git commit -m "Update theme"
git push
```

## 注意事項

- このテーマディレクトリのみがGitで管理されます
- WordPress本体や他のテーマはGit管理対象外です
- `.gitignore`で不要なファイルを除外しています

