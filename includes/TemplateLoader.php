<?php

namespace RunthingsSecrets;

if (!defined('WPINC')) {
    die;
}

// Ensure the Gamajo_Template_Loader class is included
if (!class_exists('Gamajo_Template_Loader')) {
	include RUNTHINGS_SECRETS_PLUGIN_DIR . 'vendor/gamajo-template-loader/class-gamajo-template-loader.php';
}

class TemplateLoader extends \Gamajo_Template_Loader
{
		/**
		 * Prefix for filter names.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $filter_prefix = 'runthings_secrets';

		/**
		 * Directory name where custom templates for this plugin should be found in the theme.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $theme_template_directory = 'runthings-secrets';

		/**
		 * Reference to the root directory path of this plugin.
		 *
		 * @since 1.0.0
		 * @var string
		 */
	protected $plugin_directory = RUNTHINGS_SECRETS_PLUGIN_DIR;
}
