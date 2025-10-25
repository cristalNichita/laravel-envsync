# ⚙️ Laravel EnvSync

[![Packagist Version](https://img.shields.io/packagist/v/fragly/laravel-envsync.svg?color=brightgreen)](https://packagist.org/packages/fragly/laravel-envsync)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![Laravel](https://img.shields.io/badge/Laravel-10%2B-red.svg)](https://laravel.com)
[![Downloads](https://img.shields.io/packagist/dt/fragly/laravel-security-tools.svg?style=for-the-badge&color=brightgreen)](https://packagist.org/packages/fragly/laravel-envsync)
[![License](https://img.shields.io/github/license/cristalNichita/laravel-envsync.svg?style=for-the-badge)](https://github.com/cristalNichita/laravel-envsync/blob/main/LICENSE)
[![Sponsor](https://img.shields.io/badge/Sponsor-Patreon-ff424d?logo=patreon&style=for-the-badge)](https://www.patreon.com/c/FraglyDev)

> Compare and safely synchronize your `.env` with `.env.example`.  
> Perfect for **CI/CD pipelines**, **team development**, and **clean environments**.

## Contents

- [Features](#-features)
- [Installation](#-installation)
- [Usage](#-usage)
- [Example workflow (CI/CD)](#-example-workflow-cicd)
- [Configuration](#-configuration)
- [Local development (testing the package)](#-local-development-testing-the-package)
- [About the Author](#-about-the-author)
- [License](#-license)

## 🚀 Features

✅ Compares `.env.example` ↔ `.env`  
✅ Shows **missing**, **extra**, and **same-as-default** variables  
✅ Auto-syncs `.env` with `.env.example`  
✅ Makes safe **backups** before changes  
✅ Ignores keys or patterns (`APP_KEY`, `LOG_*`, etc.)  
✅ Perfect for automation (non-interactive mode for CI)

---

## 🧩 Installation
```bash
composer require fragly/laravel-envsync --dev
```
The serviceprovider is auto-discovered by Laravel.

## 🧠 Usage
### 🔍 Compare
```bash
php artisan env:diff
```
### Example output:

```
> Checking .env consistency
! Missing in .env:
+ MAIL_ENCRYPTION
+ REDIS_PASSWORD

>> Extra in .env:
+ OLD_DEBUG_FLAG

!! Same-as-default values (check you configured them):
+ APP_NAME
+ APP_ENV
+ APP_DEBUG

Summary: missing=2, extra=1, same-as-default=3
```

### JSON output (for CI)
```bash
php artisan env:diff --json
```
### Filter or hide sections
```bash
php artisan env:diff --only=missing
php artisan env:diff --hide-defaults
php artisan env:diff --ignore=APP_KEY,LOG_*,REDIS_*
```

### 🔄 Sync
Synchronize .env with .env.example.
```bash
php artisan env:sync
```

### Options
| Option        | Description                                                 |
| ------------- | ----------------------------------------------------------- |
| `--empty`     | Add missing variables with empty values instead of defaults |
| `--prune`     | Remove keys not present in `.env.example`                   |
| `--no-backup` | Skip creating `.env.bak.*` backup                           |
| `--yes`       | Non-interactive (for CI/CD)                                 |
| `--json`      | Output result in JSON                                       |
| `--ignore=`   | Comma-separated ignore list (same syntax as `env:diff`)     |

### Example
```bash
php artisan env:sync --prune --yes --ignore=APP_KEY,LOG_*
```

## 🧰 Example workflow (CI/CD)
In your pipeline:
```bash
composer install
php artisan env:sync --yes --prune
php artisan config:cache
```
Keeps environment variables always in sync automatically

## ⚙️ Configuration
You can create `.envsyncignore` file in the project root to store patterns ignored in both commands:
```
APP_KEY
LOG_*
MAIL_*
REDIS_*
```

## 🧪 Local development (testing the package)
If you want to test this package locally without publishing to Packagist:

```json
// in your project's composer.json
{
  "repositories": [
    {
      "type": "path",
      "url": "../package-destination"
    }
  ]
}
```
Then install it:
```bash
composer require fragly/laravel-envsync:@dev
```
Composer will symlink your package for instant updates.

## 🧑‍💻 About the Author
Fragly Dev — Building tools for modern Laravel & Next.js developers.
Follow for more developer utilities, security helpers, and SaaS-ready boilerplates.

## 📜 License
This package is open-sourced software licensed under the MIT license.

---

<details>
<summary>🔍 SEO Keywords</summary>

laravel env sync, laravel .env compare, laravel .env validator, laravel .env example check,  
laravel environment sync, laravel environment tool, laravel environment manager,  
laravel ci cd tools, laravel devops utilities, laravel config checker,  
laravel configuration sync, laravel dotenv helper, laravel dotenv checker,  
laravel deployment tools, laravel config diff, laravel config audit,  
laravel .env fixer, laravel production best practices,  
laravel environment consistency, laravel config manager, laravel dotenv sync,  
laravel automation tools, laravel artisan env command, fragly env sync, fragly laravel package

</details>

<!--  
SEO: laravel envsync, laravel envsync package, laravel .env sync tool, laravel environment compare,  
laravel config diff tool, laravel ci cd environment, laravel devops config,  
laravel dotenv check, laravel deployment config, laravel auto env sync, fragly envsync, fragly.net packages  
-->