# さくらレンタルサーバー デプロイ セットアップガイド

このドキュメントでは、GitHub Actions を使用したさくらレンタルサーバーへのデプロイに必要な前提条件と初期設定手順を説明します。

> **Note**: 現在は動作確認のため SQLite を使用していますが、将来的に MySQL へ移行予定です。

---

## 1. GitHub Secrets の設定

GitHub リポジトリ → Settings → Secrets and variables → Actions で以下を設定:

| Secret名 | 説明 | 例 |
|----------|------|-----|
| `SAKURA_SSH_HOST` | さくらサーバーのホスト名 | `xxxxx.sakura.ne.jp` |
| `SAKURA_SSH_USER` | SSH ユーザー名 | `net-get` |
| `SAKURA_SSH_PRIVATE_KEY` | SSH 秘密鍵の全内容 | `-----BEGIN OPENSSH...` |

---

## 2. SSH 鍵の準備

### 2.1 ローカルで鍵ペアを生成

```bash
ssh-keygen -t ed25519 -f ~/.ssh/sakura_deploy -C "github-actions" -N ""
```

### 2.2 さくらサーバーに公開鍵を登録

```bash
ssh-copy-id -i ~/.ssh/sakura_deploy.pub ユーザー名@ホスト名
```

### 2.3 秘密鍵を GitHub Secrets に登録

```bash
cat ~/.ssh/sakura_deploy
# 出力全体を SAKURA_SSH_PRIVATE_KEY にコピー
```

---

## 3. Staging 環境の初期設定

### 3.1 SSH 接続

```bash
ssh net-get@xxxxx.sakura.ne.jp
```

### 3.2 ディレクトリ構造の作成

```bash
cd /home/netget/staging-yoyaku

# storage ディレクトリ
mkdir -p storage/app/public/staff_profiles
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs

# bootstrap/cache
mkdir -p bootstrap/cache

# パーミッション設定
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### 3.3 symlink の作成

```bash
cd /home/netget/staging-yoyaku/public
ln -s ../storage/app/public storage

# 確認
ls -la storage
# 出力: storage -> ../storage/app/public
```

### 3.4 .env ファイルの作成

```bash
cd /home/netget/staging-yoyaku
nano .env
```

`.env` の内容（SQLite 版）:

```env
APP_NAME="Booking Staging"
APP_ENV=staging
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=Asia/Tokyo
APP_URL=https://staging-yoyaku.example.com

LOG_CHANNEL=daily

DB_CONNECTION=sqlite
DB_DATABASE=/home/netget/staging-yoyaku/database/database.sqlite

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

### 3.5 APP_KEY の生成

初回デプロイ後に実行:

```bash
cd /home/netget/staging-yoyaku
php artisan key:generate
```

### 3.6 データベースの準備（SQLite）

```bash
mkdir -p /home/netget/staging-yoyaku/database
touch /home/netget/staging-yoyaku/database/database.sqlite
chmod 664 /home/netget/staging-yoyaku/database/database.sqlite
```

### 3.7 マイグレーション（初回デプロイ後）

```bash
cd /home/netget/staging-yoyaku
php artisan migrate
```

---

## 4. Production 環境の初期設定

### 4.1 ディレクトリ構造の作成

Staging と同様の手順を `/home/netget/yoyaku/` で実行。

```bash
cd /home/netget/yoyaku

mkdir -p storage/app/public/staff_profiles
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p bootstrap/cache

chmod -R 775 storage
chmod -R 775 bootstrap/cache

cd public
ln -s ../storage/app/public storage
```

### 4.2 .env ファイルの作成

```bash
cd /home/net-get/yoyaku
nano .env
```

`.env` の内容（Production 用）:

```env
APP_NAME="Booking"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_TIMEZONE=Asia/Tokyo
APP_URL=https://yoyaku.example.com

LOG_CHANNEL=daily

DB_CONNECTION=sqlite
DB_DATABASE=/home/net-get/yoyaku/database/database.sqlite

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

### 4.3 データベースとマイグレーション

Staging と同様の手順を実行。

---

## 5. デプロイ後の確認事項

### 5.1 初回デプロイ後

1. APP_KEY が設定されているか確認
2. マイグレーションを実行
3. ブラウザでアクセスして動作確認
4. storage/logs にログが出力されるか確認

### 5.2 エラーが発生した場合

```bash
# ログを確認
tail -f /home/net-get/staging-yoyaku/storage/logs/laravel.log

# パーミッションを再設定
chmod -R 775 storage bootstrap/cache
```

---

## 6. 将来の MySQL 移行時の変更点

`.env` の DB 設定を以下に変更:

```env
DB_CONNECTION=mysql
DB_HOST=mysqlXXX.db.sakura.ne.jp
DB_PORT=3306
DB_DATABASE=データベース名
DB_USERNAME=ユーザー名
DB_PASSWORD=パスワード
```

マイグレーションを再実行:

```bash
php artisan migrate:fresh  # 注意: 全データ削除
# または
php artisan migrate        # 差分のみ
```
