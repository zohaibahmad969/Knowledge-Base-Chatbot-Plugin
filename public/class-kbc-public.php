<?php

class KBC_Public {
    private $plugin_name;
    private $version;
    private $chatbot;
    private $calendly;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->chatbot = new KBC_Chatbot();
        $this->calendly = new KBC_Calendly();
    }

    public function enqueue_styles() {
        wp_enqueue_style(
            $this->plugin_name,
            plugin_dir_url(__FILE__) . 'css/kbc-public.css',
            array(),
            $this->version,
            'all'
        );

        // Enqueue Calendly styles if configured
        if ($this->calendly->is_configured()) {
            $this->calendly->enqueue_scripts();
        }
    }

    public function enqueue_scripts() {
        wp_enqueue_script(
            $this->plugin_name,
            plugin_dir_url(__FILE__) . 'js/kbc-public.js',
            array('jquery'),
            $this->version,
            false
        );

        // Localize script with settings
        wp_localize_script($this->plugin_name, 'kbcPublic', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('kbc_public_nonce'),
            'position' => get_option('kbc_chatbot_position', 'bottom-right'),
            'theme' => get_option('kbc_chatbot_theme', 'light'),
            'calendly_url' => get_option('kbc_calendly_url', '')
        ));
    }

    public function chatbot_shortcode($atts) {
        $atts = shortcode_atts(array(
            'position' => get_option('kbc_chatbot_position', 'bottom-right'),
            'theme' => get_option('kbc_chatbot_theme', 'light')
        ), $atts);

        ob_start();
        include plugin_dir_path(__FILE__) . 'partials/kbc-chatbot-display.php';
        return ob_get_clean();
    }

    public function ajax_chat() {
        check_ajax_referer('kbc_public_nonce', 'nonce');

        $message = sanitize_text_field($_POST['message']);
        $session_id = sanitize_text_field($_POST['session_id']);

        if (empty($message)) {
            wp_send_json_error('Message is required');
        }

        $response = $this->chatbot->process_message($message, $session_id);
        wp_send_json_success($response);
    }

    public function display_chatbot() {
        // Don't display in admin area
        if (is_admin()) {
            return;
        }

        // Check display method
        $display_method = get_option('kbc_display_method', 'automatic');
        if ($display_method === 'shortcode') {
            return;
        }

        // Check if current page is excluded
        $exclude_pages = get_option('kbc_exclude_pages', '');
        if (!empty($exclude_pages)) {
            $exclude_pages = array_map('trim', explode("\n", $exclude_pages));
            $current_url = $_SERVER['REQUEST_URI'];
            $current_id = get_the_ID();

            foreach ($exclude_pages as $exclude) {
                if (is_numeric($exclude) && $current_id == $exclude) {
                    return;
                }
                if (strpos($current_url, $exclude) !== false) {
                    return;
                }
            }
        }

        // Get display settings
        $position = get_option('kbc_chatbot_position', 'bottom-right');
        $theme = get_option('kbc_chatbot_theme', 'light');
        $icon = get_option('kbc_chatbot_icon', 'default');
        $color = get_option('kbc_chatbot_color', '#007bff');

        // Add inline styles for custom color
        $custom_css = "
            .kbc-chatbot-launcher-button {
                background-color: {$color} !important;
            }
            .kbc-chatbot-launcher-button:hover {
                background-color: " . $this->adjust_brightness($color, -20) . " !important;
            }
            .kbc-send-button {
                background-color: {$color} !important;
            }
            .kbc-send-button:hover {
                background-color: " . $this->adjust_brightness($color, -20) . " !important;
            }
            .kbc-light-theme .kbc-user-message .kbc-message-content {
                background-color: {$color} !important;
            }
        ";
        wp_add_inline_style($this->plugin_name, $custom_css);

        // Include the chatbot display template
        include plugin_dir_path(__FILE__) . 'partials/kbc-chatbot-display.php';
    }

    private function adjust_brightness($hex, $steps) {
        // Steps should be between -255 and 255. Negative = darker, positive = lighter
        $steps = max(-255, min(255, $steps));

        // Normalize into a six character long hex string
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) == 3) {
            $hex = str_repeat(substr($hex, 0, 1), 2) . str_repeat(substr($hex, 1, 1), 2) . str_repeat(substr($hex, 2, 1), 2);
        }

        // Split into three parts: R, G and B
        $color_parts = str_split($hex, 2);
        $return = '#';

        foreach ($color_parts as $color) {
            $color = hexdec($color); // Convert to decimal
            $color = max(0, min(255, $color + $steps)); // Adjust color
            $return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT); // Make two char hex code
        }

        return $return;
    }
} 