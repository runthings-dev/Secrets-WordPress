# Introduction

A plugin for securely sharing secrets such as passwords.

Generate a secret url, and share that with somebody, instead of leaving a
password in a chat log, or email thread.

Set the maximum views / expiration date, so that the secret doesn't lurk around
forever.

# Features

- Limit by views and expiration date
- Copy to clipboard button
- Copy plain link, or with instructions and expiration terms
- Selection of pages for 'Add Secret,' 'Secret Created,' and 'View Secret'
- Spam protection with reCAPTCHA v3 support and score threshold
- Integrate into your site using shortcodes, or WordPress Block Editor blocks
- Optionally enqueue form styles
- Encryption of secrets when stored in the database
- Overridable templates
- Translatable

# Usage

Install the plugin and activate it.

Create pages for creating and displaying the secrets.

On an add secrets page, add the `[runthings_secrets_add]` shortcode, or block.

On a secrets created page, add the `[runthings_secrets_created]` shortcode, or
block.

On a viewing page, add the `[runthings_secrets_view]` shortcode, or block.

In the plugin options page, under Admin > Settings > RunThings Secrets, assign
the pages.

Optionally, set up the reCAPTCHA v3 keys.

Optionally, set up your own encryption key in `wp-config.php` (see the
encryption section below for full details).

# Download

Download and contribute issues at:

https://github.com/rtpHarry/Secrets-Wordpress

# Encryption

As long as your server supports the PHP encryption library, Sodium, an
encryption key is automatically generated and used to secure your secrets.

However, you can set a custom encryption key in your `wp-config.php` file by
adding a `define` statement.

This approach is more secure as it allows you to bring the key value from an
environment variable or a third-party key storage service.

To set a custom encryption key in `wp-config.php`, add the following line:

```php
define('RUNTHINGS_SECRETS_ENCRYPTION_KEY', 'your_custom_encryption_key');
```

Replace `your_custom_encryption_key` with your generated encryption key.

You can generate a new key in the options page of the plugin, which is under
Admin > Settings > RunThings Secrets

Note: Changing the encryption key will break the decryption of any existing
secrets, making them unreadable.

# Changelog

## 1.0.0 - 18th April 2024

- Initial public release
- Security review
- Improved default styling
- Copy to clipboard feature added to view secret page

## 0.5.0 - 29th March 2023

- Internal release

# Background

It was inspired by sites like https://pwpush.com and
https://github.com/unicalabs/agrippa

I'm developing this for two reasons; one to have it in the WordPress ecosystem,
so that it can be easily branded and integrated into sites, and two, as an
experiment, to lean as much as I can on AI to write bits of the code.

So far I'm using ChatGTP Plus, with a mixture of GTP 3.5 and 4.

# Licence

This plugin is licenced under GPL 3, and is free to use on personal and
commercial projects.

# Thanks

SVG Icon from https://www.svgrepo.com/svg/389087/clipboard-copy

# Author

Built by Matthew Harris of runthings.dev, copyright 2023-2024.

https://runthings.dev/
