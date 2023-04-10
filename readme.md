# Introduction
A plugin for securely sharing secrets such as passwords.

Generate a secret url, and share that with somebody, instead of leaving a 
password in a chat log, or email thread.

Set the maximum views / expiration date, so that the secret doesn't lurk around
forever.

# Features
  - Selection of pages for 'Add Secret,' 'Secret Created,' and 'View Secret'
  - Spam protection with reCAPTCHA v3 support and score threshold
  - Integrate into your site using shortcodes, or WordPress Block Editor blocks
  - Optionally enqueue form styles
  - Encryption of secrets when stored in the database
  - Overridable templates
  - Translatable

# Usage
Install the plugin and activate it.

Create a page for adding secrets, and a page for viewing the secrets.

On the adding secrets page, add the [runthings_secrets] shortcode.

On the viewing page, add the [runthings_secrets_view] shortcode.

In the plugin options page, assign the viewing page.

** Not yet developed ** - for now just edit the link url in 
`templates/secret-created.php`

# Download
Download and contribute issues at:

https://github.com/rtpHarry/Secrets-Wordpress

# Changelog
0.5.0 - 25th August 2021
  - Initial release

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
Built by Matthew Harris of runthings.dev, copyright 2023.

https://runthings.dev/