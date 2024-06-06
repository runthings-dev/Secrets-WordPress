<?php
/*
Secrets by runthings.dev

Copyright 2023-2024 Matthew Harris

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 3, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if (!defined('WPINC')) {
	die;
}

// Ensure the Gamajo_Template_Loader class is included
if (!class_exists('Gamajo_Template_Loader')) {
	include RUNTHINGS_SECRETS_PLUGIN_DIR . 'vendor/gamajo-template-loader/class-gamajo-template-loader.php';
}

if (!class_exists('runthings_secrets_Template_Loader')) {
	class runthings_secrets_Template_Loader extends Gamajo_Template_Loader
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
}
