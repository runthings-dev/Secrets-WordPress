# Introduction

Securely share secrets with a time-limited URL, avoiding passwords in chats or emails.

RunThings Secrets is a WordPress plugin that allows you to securely share secrets such as passwords.

Create a secret URL, and share it with someone instead of leaving a password in a chat log or email thread.

Set the maximum views and expiration date, so that the secret doesn't lurk around forever.

# Features

- Limit access by views and expiration date to enhance security.
- Easy sharing with a copy-to-clipboard button, featuring a helpful tooltip.
- Options to copy a plain link or a link with instructions and expiration terms.
- Configurable pages for 'Add Secret,' 'Secret Created,' and 'View Secret' actions.
- Spam protection powered by reCAPTCHA v3, including a score threshold setting.
- Flexible integration using shortcodes or WordPress Block Editor blocks.
- Optional styling for forms can be enqueued as needed.
- Secure encryption of secrets when stored in the database.
- Customizable templates that can be overridden to fit your site’s design.
- Fully translatable.
- Timezone-aware expiration dates, synchronized with your site’s timezone settings.
- Object caching support, to improve performance.
- Customisable rate limiting, to prevent brute force attacks from bots.

# Usage

Follow these steps to install and configure the plugin:

1. **Install the Plugin**:

   - Download the plugin from the WordPress Plugin Directory or install it directly through the WordPress dashboard under `Plugins > Add New`.
   - Activate the plugin.

2. **Create Required Pages**:

   - Create three new pages in WordPress for adding, viewing, and displaying created secrets.

3. **Embed Shortcodes or Blocks**:

   - On your "Add Secrets" page, embed the `[runthings_secrets_add]` shortcode or use the corresponding block.
   - On the "Secret Created" page, add the `[runthings_secrets_created]` shortcode or block.
   - On the "View Secret" page, include the `[runthings_secrets_view]` shortcode or block.

4. **Configure Page Assignments**:

   - Navigate to `Admin > Settings > RunThings Secrets` in the WordPress dashboard.
   - Assign the newly created pages to their respective settings within the plugin options.

5. **Set Up reCAPTCHA v3** (Optional):

   - Obtain reCAPTCHA v3 keys from Google and enter them in the plugin’s settings to enable spam protection.

6. **Configure Custom Encryption Key** (Optional):

   - For enhanced security, define a custom encryption key in your `wp-config.php` file. For detailed instructions on setting up encryption, refer to the FAQ section "How does the encryption work?".

7. **HTTPS Recommendation**:
   - While the plugin can operate over HTTP, using HTTPS is recommended. HTTPS enables the modern copy-to-clipboard API, providing a better user experience. Running on HTTP uses a deprecated API and may result in a degraded experience.

# Download

Download and contribute issues at:

https://github.com/runthings-dev/Secrets-WordPress

# Encryption

As long as your server supports the PHP encryption library, Sodium, an encryption key is automatically generated and used to secure your secrets. However, you can set a custom encryption key in your `wp-config.php` file by adding a `define` statement.

This approach is more secure as it allows you to bring the key value from an environment variable or a third-party key storage service.

To set a custom encryption key in `wp-config.php`, add the following line:

```php
define('RUNTHINGS_SECRETS_ENCRYPTION_KEY', 'your_custom_encryption_key');
```

Replace `your_custom_encryption_key` with your generated encryption key.

You can generate a new key in the options page of the plugin, which is under Admin > Settings > RunThings Secrets.

Note: Changing the encryption key will break the decryption of any existing secrets, making them unreadable.

# Rate Limiting

here is basic rate limiting functionality included, enabled by default, to provide protection against brute force attacks by bots.

It depends on the `$_SERVER['REMOTE_ADDR']` variable, which may cause issues if your userbase is all within one company, or behind the same vpn, for example.

There are other headers which could be checked, but these can be spoofed by the bots, so are not secure.

You can increase the rate limits per feature (add, created, view), and make certain trusted user roles exempt from the limits.

You can also disable the feature entirely.

This is for advanced setups, where you might want to provide your own brute force protection solution, such as using WordFence, CloudFlare, or similar technologies.

If you have access to it, some web servers can also support setting up rate limiting via their config files, which would remove the overhead of WordPress loading for each bot request.

# Timezone

The timezone displayed in the "Add Secret" form is set to match your WordPress site's timezone settings. To change the displayed timezone, follow these steps:

1. **Navigate to your WordPress dashboard.** Go to the 'Settings' menu.
2. **Click on 'General'.** Here you will find the 'Timezone' setting.
3. **Select your desired timezone.** You can choose from a list of cities representing timezones or UTC time offsets. Once you select a new timezone, it will affect how times and dates are displayed across your entire WordPress site, including the "Add Secret" form.
4. **Save Changes.** After selecting your new timezone, be sure to click 'Save Changes' at the bottom of the page to apply your settings.

Remember, the timezone setting not only affects the display of dates in the "Add Secret" form but also how the expiration dates are processed within the plugin.

# Third-Party Services

This plugin uses the following third-party service:

## Google reCAPTCHA v3

This plugin can make use of Google reCAPTCHA v3 to prevent spam and abuse. Google reCAPTCHA is used to ensure that the requests are made by humans and not by bots.

- **Service URL:** [Google reCAPTCHA](https://www.google.com/recaptcha)
- **Terms of Use:** [Google reCAPTCHA Terms of Use](https://www.google.com/recaptcha/terms)
- **Privacy Policy:** [Google Privacy Policy](https://policies.google.com/privacy)

When you enable reCAPTCHA in the plugin settings, it will send user data, such as the user's IP address and other data, to Google for verification.

# Changelog

## 1.6.0 - 9th July 2024

- Bugfix - Remove hardcoded plugin folder name

## 1.5.1 - 7th June 2024

- Add readme files back into the release archive.

## 1.5.0 - 7th June 2024

- Full review of plugin to enhance data validation and security
- Add third-party services section to the readme and admin panel
- Prevent direct access to template files, bumping version numbers up

## 1.4.1 - 29th May 2024

- Bugfix - build script was excluding json files eg block.json and broke the blocks

## 1.4.0 - 21st May 2024

- Implemented dev tooling - `./bin/makepot.sh`
- Implemented dev tooling - `./bin/build-zip.sh`
- Replaced po and mo files with pot file

## 1.3.0 - 1st May 2024

- Update author meta field to be in line with WordPress Directory user name
- Update cron schedule to be at 00:15 daily, instead of daily from time of install
- Bump secret-created and view-secret template versions to 1.2.0
- Add view secret link to secret-created template
- Add abbr with timezone for expiration date on secret-created and view-secret templates
- Update all date code to use datetime class and explicit timezones
- Bump add-secret-form template version to 1.2.0
- Add secret template - rename current date to minimum date, and add 1 day to it

## 1.2.0 - 30th Apr 2024

- Rate limits - Implement optional rate limiting to stop bots
- Rate limits - Allow custom rate limits per feature (add, created, view)
- Rate limits - Allow rate limit exceptions for trusted roles
- i18n - make strings in js file translatable
- Added local formatted and GMT formatted date strings to the secret object passed down to templates, as `$context->secret->formatted_expiration` and `$context->secret->formatted_expiration_gmt`
- Updated templates `secret-created` and `view-secret` to use new date strings
- Implemented out of date template version check notifications
- Added filter - copy to clipboard icon filter as `runthings_secrets_copy_to_clipboard_icon` to allow customisation of icon asset
- Added filter - copy to clipboard allowed html filter to securely output the asset via wp_kses, using `runthings_secrets_copy_to_clipboard_icon_allowed_html`
- Updated templates `secret-created` and `view-secret` to use new filtered copy to clipboard icons
- Bug - Wrong table column name was causing cron task to fail
- Bumped minimum WordPress version to 6.2 to get %i table placeholder support in wpdb prepare()
- Bumped tested up to version to 6.5
- Security - ensured all output is correctly sanitized
- Implement support for WP object caching
- i18n - add translators strings
- Add version numbers to enqueued assets
- Removed external cdn dependency for select2 script
- Bug - Encoding of posted secret value corrupted some passwords
- PHPCS coding standards compliance

## 1.1.0 - 23rd April 2024

- Implement tooltips on copy to clipboard buttons

## 1.0.0 - 18th April 2024

- Initial public release
- Security review
- Improved default styling
- Copy to clipboard feature added to view secret page

## 0.5.0 - 29th March 2023

- Internal release

# Background

It was inspired by sites like https://pwpush.com and https://github.com/unicalabs/agrippa.

I'm developing this to have it in the WordPress ecosystem, so that it can be easily branded and integrated into sites.

# Licence

This plugin is licenced under GPL 3, and is free to use on personal and
commercial projects.

# Thanks

Copy To Clipboard - SVG Icon from https://www.svgrepo.com/svg/389087/clipboard-copy (MIT Licence)

Plugin Icon - Secret by Side Project, on Noun Project, from https://thenounproject.com/browse/icons/term/secret/ (CC BY 3.0)

# Author

Built by Matthew Harris of runthings.dev, copyright 2023-2024.

https://runthings.dev/wordpress-plugins/secrets/
