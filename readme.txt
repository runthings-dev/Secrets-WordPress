=== RunThings Secrets ===
Contributors: rtpHarry
Tags: secrets, passwords, encryption, secure sharing, shortcode, block
Requires at least: 5.0
Tested up to: 5.8
Requires PHP: 7.2
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

A plugin for securely sharing secrets such as passwords. Generate a secret URL and share it with someone instead of leaving a password in a chat log or email thread.

== Description ==

RunThings Secrets is a WordPress plugin that allows you to securely share secrets such as passwords. Create a secret URL, and share it with someone instead of leaving a password in a chat log or email thread. Set the maximum views and expiration date, so that the secret doesn't lurk around forever.

= Features =

  * Limit by views and expiration date
  * Copy to clipboard button
  * Copy plain link, or with instructions and expiration terms
  * Selection of pages for 'Add Secret,' 'Secret Created,' and 'View Secret'
  * Spam protection with reCAPTCHA v3 support and score threshold
  * Integrate into your site using shortcodes or WordPress Block Editor blocks
  * Optionally enqueue form styles
  * Encryption of secrets when stored in the database
  * Overridable templates
  * Translatable

== Installation ==

1. Install the plugin and activate it.
2. Create pages for creating and displaying the secrets.
3. On an add secrets page, add the [runthings_secrets_add] shortcode or block.
4. On a secrets created page, add the [runthings_secrets_created] shortcode or block.
5. On a viewing page, add the [runthings_secrets_view] shortcode or block.
6. In the plugin options page, under Admin > Settings > RunThings Secrets, assign the pages.
7. Optionally, set up the reCAPTCHA v3 keys.
8. Optionally, set up your own encryption key in `wp-config.php` (see the encryption section below for full details).

== Screenshots ==

// Add screenshots of your plugin here, if applicable.

== Changelog ==

= 1.0.0 - 18th April 2024 =
* Initial public release
* Security review
* Improved default styling
* Copy to clipboard feature added to view secret page

= 0.5.0 - 29th March 2023 =
* Internal release

== Frequently Asked Questions ==

// Add FAQ here, if applicable.

== Encryption ==

As long as your server supports the PHP encryption library, Sodium, an encryption key is automatically generated and used to secure your secrets. However, you can set a custom encryption key in your `wp-config.php` file by adding a `define` statement.

This approach is more secure as it allows you to bring the key value from an environment variable or a third-party key storage service.

To set a custom encryption key in `wp-config.php`, add the following line:

define('RUNTHINGS_SECRETS_ENCRYPTION_KEY', 'your_custom_encryption_key');

Replace `your_custom_encryption_key` with your generated encryption key.

You can generate a new key in the options page of the plugin, which is under Admin > Settings > RunThings Secrets.

Note: Changing the encryption key will break the decryption of any existing secrets, making them unreadable.

== Background ==

It was inspired by sites like https://pwpush.com and https://github.com/unicalabs/agrippa.

I'm developing this for two reasons; one to have it in the WordPress ecosystem, so that it can be easily branded and integrated into sites,