#!/bin/bash
set -e

usage() {
    echo "Usage: $0 --release-branch <branch-name>"
    echo "Example: $0 --release-branch release/v1.0"
    exit 1
}

# サーバー限定チェック
# 本番期はどうする？ staging の内容を rsync する？
if [ "$DEPLOY_SERVER" != "staging" ]; then
    echo "Error: This script can only be run on the deploy server."
    exit 1
fi

# 引数解析
while [[ $# -gt 0 ]]; do
    case $1 in
        --release-branch)
            RELEASE_BRANCH="$2"
            shift 2
            ;;
        *)
            usage
            ;;
    esac
done

# 引数チェック
if [[ -z "$RELEASE_BRANCH" ]]; then
    usage
fi

# デプロイ先ディレクトリ
DEPLOY_DIR=~/web/booking

# ディレクトリがなければ clone
if [ ! -d "$DEPLOY_DIR/.git" ]; then
    echo "Cloning repository..."
    git clone git@github.com:<user>/<repo>.git "$DEPLOY_DIR"
fi

cd "$DEPLOY_DIR"

# リモート情報を更新
git fetch origin

# ブランチの切り替え（存在しなければ作成）
if git show-ref --quiet refs/heads/$RELEASE_BRA_
