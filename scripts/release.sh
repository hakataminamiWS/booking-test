#!/bin/bash
set -e

usage() {
    echo "Usage: $0 --commit <commit-or-tag> --release-version <release-branch-name>"
    echo "Example: $0 --commit main --release-version v1.0"
    exit 1
}

# 引数解析
while [[ $# -gt 0 ]]; do
    case $1 in
        --commit)
            TARGET_COMMIT="$2"
            shift 2
            ;;
        --release-version)
            RELEASE_VERSION="$2"
            shift 2
            ;;
        *)
            usage
            ;;
    esac
done

# 引数チェック
if [[ -z "$TARGET_COMMIT" || -z "$RELEASE_VERSION" ]]; then
    usage
fi

RELEASE_BRANCH="release/$RELEASE_VERSION"

# 現在のブランチを記録
CURRENT_BRANCH=$(git symbolic-ref --short HEAD)
echo "Current branch: $CURRENT_BRANCH"

# 1. 指定 commit / tag の存在チェック
if ! git rev-parse --verify "$TARGET_COMMIT" >/dev/null 2>&1; then
    echo "Error: commit or tag '$TARGET_COMMIT' does not exist."
    exit 1
fi

# 2. リリースブランチが既に存在する場合は停止
if git show-ref --quiet refs/heads/$RELEASE_BRANCH; then
    echo "Error: release branch '$RELEASE_BRANCH' already exists locally."
    exit 1
fi

# 3. リリースブランチ作成（git switch 版）
git switch -c $RELEASE_BRANCH $TARGET_COMMIT

# 4. ビルド作業（例: src -> lib）
rm -rf lib
mkdir lib
cp -r src/* lib/

# 5. ビルド成果物を追加
git add -f lib/*
git commit -m "Release $RELEASE_VERSION from $TARGET_COMMIT"

# 6. GitHub に push
git push -u origin $RELEASE_BRANCH

# 7. 元のブランチに戻る
git switch $CURRENT_BRANCH

echo "Release $RELEASE_BRANCH created from $TARGET_COMMIT and pushed to GitHub."
