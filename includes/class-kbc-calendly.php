<?php

class KBC_Calendly {
    private $calendly_url;
    private $scheduling_keywords;

    public function __construct() {
        $this->calendly_url = get_option('kbc_calendly_url', '');
        $this->scheduling_keywords = get_option('kbc_scheduling_keywords', array('schedule', 'book', 'meeting'));
    }

    public function init() {
        // Initialize any required components
    }

    public function enqueue_scripts() {
        if (empty($this->calendly_url)) {
            return;
        }

        wp_enqueue_script(
            'calendly-widget',
            'https://assets.calendly.com/assets/external/widget.js',
            array(),
            null,
            true
        );

        wp_enqueue_style(
            'kbc-calendly',
            KBC_PLUGIN_URL . 'public/css/calendly.css',
            array(),
            KBC_VERSION
        );
    }

    public function get_scheduling_widget() {
        if (empty($this->calendly_url)) {
            return '';
        }

        $widget_html = '<div class="kbc-calendly-widget">';
        $widget_html .= '<div class="calendly-inline-widget" data-url="' . esc_url($this->calendly_url) . '" style="min-width:320px;height:630px;"></div>';
        $widget_html .= '</div>';

        return $widget_html;
    }

    public function get_scheduling_button() {
        if (empty($this->calendly_url)) {
            return '';
        }

        $button_html = '<div class="kbc-calendly-button">';
        $button_html .= '<a href="' . esc_url($this->calendly_url) . '" class="calendly-button" target="_blank">';
        $button_html .= '<span>Schedule a Meeting</span>';
        $button_html .= '</a>';
        $button_html .= '</div>';

        return $button_html;
    }

    public function check_scheduling_keywords($message) {
        $message = strtolower($message);
        
        foreach ($this->scheduling_keywords as $keyword) {
            if (strpos($message, strtolower($keyword)) !== false) {
                return true;
            }
        }

        return false;
    }

    public function is_configured() {
        return !empty($this->calendly_url);
    }
} 