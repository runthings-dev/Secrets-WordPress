# Build Commands

## Fresh Clone Setup

```bash
composer install
```

## After Code Changes

```bash
composer dump-autoload
```

## Release Build

```bash
./bin/build-zip.sh
```

**Note:** No npm pipeline. Script runs `composer dump-autoload` automatically.

**Output:** `build/runthings-secrets.zip`
