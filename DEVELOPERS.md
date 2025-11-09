# Developer Documentation

This document provides technical documentation for developers who want to extend or customize the RunThings Secrets plugin.

## Table of Contents

- [Filters](#filters)
  - [Validation Warnings](#validation-warnings)
  - [UI Customization](#ui-customization)
  - [Delete Feature](#delete-feature)
- [Actions](#actions)
- [Template Overrides](#template-overrides)

## Filters

### Validation Warnings

The plugin includes validation warnings that appear when users set potentially insecure values. These can be customized or disabled using the following filters.

#### `runthings_secrets_expiration_warning_date`

Customize the date threshold for showing expiration warnings.

**Default:** 6 months from current date

**Parameters:**

- `$date_string` (string) - Date in Y-m-d format

**Returns:** (string) Date in Y-m-d format

**Example - Show warning for dates more than 3 months in the future:**

```php
add_filter('runthings_secrets_expiration_warning_date', function($date_string) {
    $warning_date = new DateTime('now', new DateTimeZone(wp_timezone_string()));
    $warning_date->add(new DateInterval('P3M')); // 3 months
    return $warning_date->format('Y-m-d');
});
```

**Added in:** v1.7.0

---

#### `runthings_secrets_max_views_warning_threshold`

Customize the view count threshold for showing warnings.

**Default:** 25 views

**Parameters:**

- `$threshold` (int) - Number of views

**Returns:** (int) Number of views

**Example - Show warning for more than 10 views:**

```php
add_filter('runthings_secrets_max_views_warning_threshold', function($threshold) {
    return 10;
});
```

**Added in:** v1.7.0

---

#### `runthings_secrets_show_expiration_warning`

Toggle visibility of the expiration date warning.

**Default:** true

**Parameters:**

- `$show` (bool) - Whether to show the warning

**Returns:** (bool) Whether to show the warning

**Example - Disable expiration warning:**

```php
add_filter('runthings_secrets_show_expiration_warning', '__return_false');
```

**Added in:** v1.7.0

---

#### `runthings_secrets_show_max_views_warning`

Toggle visibility of the max views warning.

**Default:** true

**Parameters:**

- `$show` (bool) - Whether to show the warning

**Returns:** (bool) Whether to show the warning

**Example - Disable max views warning:**

```php
add_filter('runthings_secrets_show_max_views_warning', '__return_false');
```

**Added in:** v1.7.0

---

### UI Customization

#### `runthings_secrets_copy_to_clipboard_icon`

Customize the copy to clipboard icon.

**Parameters:**

- `$asset_output` (string) - The icon HTML (SVG or img tag)
- `$context` (string) - Context where icon is used ('link-icon', 'snippet-icon', 'secret-icon')
- `$embed` (bool) - Whether SVG is embedded or using img tag

**Returns:** (string) Icon HTML

**Example - Use a custom icon for the link context:**

```php
add_filter('runthings_secrets_copy_to_clipboard_icon', function($asset_output, $context, $embed) {
    if ($context === 'link-icon') {
        return '<svg><!-- your custom SVG --></svg>';
    }
    return $asset_output;
}, 10, 3);
```

**Example - Use Font Awesome icon:**

```php
add_filter('runthings_secrets_copy_to_clipboard_icon', function($asset_output, $context, $embed) {
    return '<i class="fas fa-copy"></i>';
}, 10, 3);
```

**Added in:** v1.2.0

---

#### `runthings_secrets_copy_to_clipboard_icon_allowed_html`

Customize the allowed HTML tags for the copy to clipboard icon (used with wp_kses).

**Parameters:**

- `$allowed_html` (array) - Array of allowed HTML tags and attributes
- `$context` (string) - Context where icon is used

**Returns:** (array) Allowed HTML tags and attributes

**Example - Allow Font Awesome icons:**

```php
add_filter('runthings_secrets_copy_to_clipboard_icon_allowed_html', function($allowed_html, $context) {
    $allowed_html['i'] = array(
        'class' => array(),
        'aria-hidden' => array(),
    );
    return $allowed_html;
}, 10, 2);
```

**Default allowed HTML:**

```php
array(
    'svg' => array(
        'xmlns' => array(),
        'width' => array(),
        'height' => array(),
        'viewbox' => array(),
        'fill' => array(),
        'aria-hidden' => array(),
    ),
    'path' => array(
        'd' => array(),
        'fill' => array(),
    ),
    'img' => array(
        'src' => array(),
        'alt' => array(),
    ),
)
```

**Added in:** v1.2.0

---

### Delete Feature

#### `runthings_secrets_delete_button`

Customize the delete button HTML on the view secret page.

**Parameters:**

- `$default_button` (string) - Default delete button HTML
- `$secret` (object) - Secret object containing:
  - `uuid` (string) - Secret UUID
  - `allow_delete` (bool) - Whether deletion is allowed
  - Other secret properties

**Returns:** (string) Delete button HTML

**Example - Add custom CSS class:**

```php
add_filter('runthings_secrets_delete_button', function($default_button, $secret) {
    return str_replace('rs-delete-button', 'rs-delete-button my-custom-class', $default_button);
}, 10, 2);
```

**Example - Completely custom button:**

```php
add_filter('runthings_secrets_delete_button', function($default_button, $secret) {
    if (!$secret->allow_delete) {
        return '';
    }

    return sprintf(
        '<form method="post" class="my-delete-form">
            %s
            <input type="hidden" name="secret_id" value="%s">
            <input type="hidden" name="delete_now" value="1">
            <button type="submit" class="my-delete-btn">
                <i class="fas fa-trash"></i> %s
            </button>
        </form>',
        wp_nonce_field('runthings_secrets_delete', 'runthings_secrets_delete_nonce', true, false),
        esc_attr($secret->uuid),
        esc_html__('Remove Secret', 'runthings-secrets')
    );
}, 10, 2);
```

**Added in:** v1.8.0

---

## Actions

### `runthings_secrets_check_rate_limit`

Triggered before rendering each page to check rate limits.

**Parameters:**

- `$context` (string) - Context: 'add', 'created', or 'view'

**Example - Log rate limit checks:**

```php
add_action('runthings_secrets_check_rate_limit', function($context) {
    error_log("Rate limit check for context: {$context}");
}, 10, 1);
```

---

## Template Overrides

Templates can be overridden by copying them to your theme:

**Plugin location:** `wp-content/plugins/runthings-secrets/templates/`

**Theme location:** `wp-content/themes/your-theme/runthings-secrets/`

### Available Templates

- `add-secret-form.php` - The form for creating secrets
- `secret-created.php` - Success page after creating a secret
- `view-secret.php` - Page for viewing a secret
- `error.php` - Error page

### Template Data

Each template receives a `$context` object with relevant data. Check the template files for available properties.

### Template Versioning

Templates include version numbers. When the plugin updates a template, you'll receive a notice if your theme override is outdated.

## Support

For issues or feature requests, visit:
https://github.com/runthings-dev/Secrets-WordPress
