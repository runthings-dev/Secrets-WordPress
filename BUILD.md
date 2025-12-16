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

Update readme.md changelog.
Update readme.txt changelog, upgrade notice, and stable tag.

Update runthings-secrets.php version number, and define.
Update readme.txt stable tag.

Don't commit before running the build script (it will generate pot files)

```bash
./bin/build-zip.sh
```

**Note:** No npm pipeline. Script runs `composer dump-autoload` automatically.

**Output:** `build/runthings-secrets.zip`
