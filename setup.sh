#!/bin/bash

set -e

echo "=========================================="
echo "WordPress マルチサイト環境セットアップ"
echo "=========================================="

# 色の定義
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 変数の定義
PROJECT_DIR="/Users/sumikazuhisa/project/wordpress/wp-multi-subdomain.idemii.tech"
APACHE_DOCUMENT_ROOT="${PROJECT_DIR}/public"
DB_NAME="wordpress_multisite_apache"
DB_USER="wp_user_apache"
DB_PASSWORD="wp_password_apache"

# 1. Homebrewの確認
echo -e "${YELLOW}[1/8] Homebrewの確認...${NC}"
if ! command -v brew &> /dev/null; then
    echo -e "${RED}エラー: Homebrewがインストールされていません。${NC}"
    echo "以下のコマンドでインストールしてください:"
    echo '/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"'
    exit 1
fi
echo -e "${GREEN}✓ Homebrew確認完了${NC}"

# 2. Apache, PHP, MySQLのインストール
echo -e "${YELLOW}[2/8] Apache, PHP, MySQLのインストール...${NC}"

# Apache
if ! brew list httpd &> /dev/null; then
    echo "Apacheをインストール中..."
    brew install httpd
else
    echo "Apacheは既にインストール済みです"
fi

# PHP (WordPress 6.8互換の最新安定版)
if ! brew list php@8.3 &> /dev/null && ! brew list php@8.2 &> /dev/null && ! brew list php@8.1 &> /dev/null; then
    echo "PHPをインストール中..."
    brew install php@8.3
    brew link php@8.3
else
    echo "PHPは既にインストール済みです"
    # 既存のPHPバージョンを確認
    PHP_VERSION=$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;' 2>/dev/null || echo "unknown")
    echo "現在のPHPバージョン: $PHP_VERSION"
fi

# MySQL (Docker環境を使用するため、Homebrewでのインストールはスキップ)
echo "MySQL: Docker環境を使用します (コンテナ名: mt-mysql)"

echo -e "${GREEN}✓ パッケージインストール完了${NC}"

# 3. ディレクトリ構造の作成
echo -e "${YELLOW}[3/8] ディレクトリ構造の作成...${NC}"
mkdir -p "${APACHE_DOCUMENT_ROOT}"
mkdir -p "${PROJECT_DIR}/config/apache"
mkdir -p "${PROJECT_DIR}/config/php"
mkdir -p "${PROJECT_DIR}/config/mysql"
mkdir -p "${PROJECT_DIR}/scripts"
echo -e "${GREEN}✓ ディレクトリ作成完了${NC}"

# 4. Docker MySQLの確認とデータベース作成
echo -e "${YELLOW}[4/8] Docker MySQLの確認とデータベース作成...${NC}"
if docker ps | grep -q "mt-mysql"; then
    echo "Docker MySQLは既に起動しています"
else
    echo "Docker MySQLを起動中..."
    docker start mt-mysql
    sleep 3
fi

# データベース作成スクリプトを実行
if [ -f "${PROJECT_DIR}/scripts/create-database.sh" ]; then
    bash "${PROJECT_DIR}/scripts/create-database.sh"
else
    echo -e "${YELLOW}警告: データベース作成スクリプトが見つかりません${NC}"
fi

# 5. 完了メッセージ
echo -e "${YELLOW}[5/8] セットアップ完了${NC}"
echo ""
echo -e "${GREEN}=========================================="
echo "セットアップが完了しました！"
echo "==========================================${NC}"
echo ""
echo "次のステップ:"
echo "1. Apache設定ファイルを確認・配置してください"
echo "2. /etc/hosts に以下を追加してください:"
echo "   127.0.0.1 localhost"
echo "   127.0.0.1 subA.localhost"
echo "   127.0.0.1 subB.localhost"
echo ""
echo "3. Apacheを起動してください:"
echo "   brew services start httpd"
echo ""
echo "4. WordPressをインストールしてください:"
echo "   ./scripts/install-wordpress.sh"
echo ""
