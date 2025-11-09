<?php

namespace RunthingsSecrets\Template;

if (!defined('WPINC')) {
    die;
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
