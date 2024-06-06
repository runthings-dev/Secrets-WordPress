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

if (!class_exists('runthings_secrets_Sodium_Encryption')) {
    class runthings_secrets_Sodium_Encryption
    {
        private $key;

        protected static $single_instance = null;

        protected function __construct()
        {
            if (!$this->is_sodium_enabled()) {
                add_action('admin_notices', [$this, 'sodium_not_enabled_notice']);
                return;
            }

            if (!$this->is_key_defined()) {
                if (get_option('runthings_secrets_first_run_completed') == true) {
                    add_action('admin_notices', [$this, 'key_not_defined_notice']);
                } else {
                    add_option('runthings_secrets_first_run_completed', true, '', 'no');
                    $this->generate_and_store_key();
                }
            }

            $this->key = $this->get_encryption_key();
        }

        public static function get_instance()
        {
            if (self::$single_instance === null) {
                self::$single_instance = new self();
            }

            return self::$single_instance;
        }

        public function is_sodium_enabled()
        {
            return function_exists('sodium_crypto_secretbox');
        }

        public function is_key_defined()
        {
            return defined('RUNTHINGS_SECRETS_ENCRYPTION_KEY') || get_option('runthings_secrets_encryption_key');
        }

        public function is_encryption_enabled()
        {
            return $this->is_sodium_enabled() && $this->is_key_defined();
        }

        public function sodium_not_enabled_notice()
        {
            echo '<div class="notice notice-warning">';
            echo '<p>' . esc_html__('The Sodium library is not enabled on your hosting platform. Secrets saved will not be encrypted.', 'runthings-secrets') . '</p>';
            echo '</div>';
        }

        public function key_not_defined_notice()
        {
            $options_page_url = admin_url('options-general.php?page=runthings-secrets');
            echo '<div class="notice notice-warning"><p>' . sprintf(
                wp_kses(
                    /* translators: %s: URL link to the options page */
                    __('An encryption key is not defined. Generate a new key in the <a href="%s">options page</a>, under Encryption Key.', 'runthings-secrets'),
                    ['a' => ['href' => []]] // Allows only <a> tags with href attributes
                ),
                esc_url($options_page_url)
            ) . '</p></div>';
        }

        public function encrypt($plaintext)
        {
            if (!$this->is_encryption_enabled()) {
                return $plaintext;
            }

            $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
            $ciphertext = sodium_crypto_secretbox($plaintext, $nonce, $this->key);
            return $this->base64url_encode($nonce . $ciphertext);
        }

        public function decrypt($ciphertext_base64)
        {
            if (!$this->is_encryption_enabled()) {
                return $ciphertext_base64;
            }

            $decoded = $this->base64url_decode($ciphertext_base64);
            $nonce = substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
            $ciphertext = substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

            $plaintext = sodium_crypto_secretbox_open($ciphertext, $nonce, $this->key);

            if ($plaintext === false) {
                return '';
            }

            return $plaintext;
        }

        public function get_key_method()
        {
            if (defined('RUNTHINGS_SECRETS_ENCRYPTION_KEY')) {
                return __('Using define() method', 'runthings-secrets');
            } elseif (get_option('runthings_secrets_encryption_key')) {
                return __('Internal encryption key', 'runthings-secrets');
            } else {
                return __('ERROR: No key found', 'runthings-secrets');
            }
        }

        public function generate_and_store_key()
        {
            $key = $this->generate_key();
            $this->store_key($key);
        }

        public function generate_key()
        {
            $key = sodium_crypto_secretbox_keygen();
            return base64_encode($key);
        }

        private function store_key($key)
        {
            update_option('runthings_secrets_encryption_key', $key);
        }

        private function get_encryption_key()
        {
            if (defined('RUNTHINGS_SECRETS_ENCRYPTION_KEY')) {
                $encryption_key = constant('RUNTHINGS_SECRETS_ENCRYPTION_KEY');
            } else {
                $encryption_key = get_option('runthings_secrets_encryption_key');
            }

            return base64_decode($encryption_key);
        }

        private function base64url_encode($data)
        {
            return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
        }

        private function base64url_decode($data)
        {
            return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
        }
    }
}
