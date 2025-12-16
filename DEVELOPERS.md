# Developer Documentation

This document provides technical documentation for developers who want to extend or customize the RunThings Secrets plugin.

## Table of Contents

- [Filters](#filters)
  - [Add Form Data](#add-form-data)
  - [Deprecated Filters](#deprecated-filters)
  - [UI Customization](#ui-customization)
  - [Delete Feature](#delete-feature)
- [Actions](#actions)
- [Template Overrides](#template-overrides)

## Filters

### Add Form Data

#### `runthings_secrets_add_form_data`

Filter the data passed to the add secret form template. This is the recommended way to customize form defaults and validation thresholds.

**Parameters:**

- `$data` (array) - Form data array with the following keys:
  - `default_expiration` (string) - Default expiration date in Y-m-d format
  - `default_max_views` (int) - Default max views value
  - `minimum_date` (string) - Minimum selectable date in Y-m-d format
  - `timezone` (string) - Site timezone string
  - `expiration_warning_date` (string) - Date threshold for expiration warning in Y-m-d format
  - `max_views_warning_threshold` (int) - View count threshold for warning
  - `show_expiration_warning` (bool) - Whether to show expiration warning
  - `show_max_views_warning` (bool) - Whether to show max views warning

**Returns:** (array) Modified data array

**Example - Customize warning thresholds:**

```php
add_filter('runthings_secrets_add_form_data', function($data) {
    // Show warning for dates more than 3 months in the future
    $warning_date = new DateTime('now', new DateTimeZone(wp_timezone_string()));
    $warning_date->add(new DateInterval('P3M'));
    $data['expiration_warning_date'] = $warning_date->format('Y-m-d');

    // Show warning for more than 10 views
    $data['max_views_warning_threshold'] = 10;

    return $data;
});
```

**Example - Disable all warnings:**

```php
add_filter('runthings_secrets_add_form_data', function($data) {
    $data['show_expiration_warning'] = false;
    $data['show_max_views_warning'] = false;
    return $data;
});
```

---

### Deprecated Filters

The following filters still work but are deprecated. Use `runthings_secrets_add_form_data` instead.

#### `runthings_secrets_show_expiration_warning`

**Deprecated in:** v1.9.0

Toggle visibility of the expiration date warning.

```php
// Old way (deprecated)
add_filter('runthings_secrets_show_expiration_warning', '__return_false');

// New way
add_filter('runthings_secrets_add_form_data', function($data) {
    $data['show_expiration_warning'] = false;
    return $data;
});
```

---

#### `runthings_secrets_show_max_views_warning`

**Deprecated in:** v1.9.0

Toggle visibility of the max views warning.

```php
// Old way (deprecated)
add_filter('runthings_secrets_show_max_views_warning', '__return_false');

// New way
add_filter('runthings_secrets_add_form_data', function($data) {
    $data['show_max_views_warning'] = false;
    return $data;
});
```

---

### UI Customization

#### `runthings_secrets_viewing_snippet`

Customize the viewing snippet text shown on the secret created page.

**Default:** "Get it from {url} (valid for {days} / {views})."

**Parameters:**

- `$viewing_snippet` (string) - The default snippet text
- `$secret` (object) - Secret object containing days_left, views_left, etc.
- `$viewing_url` (string) - The URL to view the secret

**Returns:** (string) Customized snippet text

**Example - Change the format:**

```php
add_filter('runthings_secrets_viewing_snippet', function($viewing_snippet, $secret, $viewing_url) {
    return sprintf(
        'Access your secret at %s - expires in %s or after %s views',
        $viewing_url,
        $secret->days_left,
        $secret->views_left
    );
}, 10, 3);
```

**Example - Add custom branding:**

```php
add_filter('runthings_secrets_viewing_snippet', function($viewing_snippet, $secret, $viewing_url) {
    return sprintf(
        '[ACME Corp] Secret link: %s (valid %s days, %s views)',
        $viewing_url,
        $secret->days_left,
        $secret->views_left
    );
}, 10, 3);
```

**Added in:** v1.8.0

---

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
