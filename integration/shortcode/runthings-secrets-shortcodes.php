<?php
/*
Secrets by runthings.dev

Copyright 2023 Matthew Harris

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

class runthings_secrets_Shortcodes_Integration
{
    private $view_renderer;
    private $add_renderer;

    public function __construct($view_renderer, $add_renderer)
    {
        $this->view_renderer = $view_renderer;
        $this->add_renderer = $add_renderer;

        $this->register_shortcodes();
    }

    private function register_shortcodes()
    {
        add_shortcode('runthings_secrets', [$this, 'add_secret_shortcode']);
        add_shortcode('runthings_secrets_view', [$this, 'view_secret_shortcode']);
    }

    public function add_secret_shortcode()
    {
        return $this->add_renderer->render();
    }

    public function view_secret_shortcode()
    {
        return $this->view_renderer->render();
    }
}