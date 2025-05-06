<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <form method="post" action="options.php">
        <?php
        settings_fields($this->plugin_name);
        do_settings_sections($this->plugin_name);
        ?>

        <div class="kbc-settings-container">
            <div class="kbc-settings-section">
                <h2>AI Configuration</h2>
                
                <div class="kbc-form-group">
                    <label for="kbc-ai-provider">AI Provider</label>
                    <select id="kbc-ai-provider" name="kbc_ai_provider">
                        <option value="openai" <?php selected(get_option('kbc_ai_provider'), 'openai'); ?>>OpenAI</option>
                        <option value="vertex" <?php selected(get_option('kbc_ai_provider'), 'vertex'); ?>>Google Vertex AI</option>
                    </select>
                </div>

                <div class="kbc-form-group" id="kbc-openai-settings">
                    <label for="kbc-openai-api-key">OpenAI API Key</label>
                    <input type="password" id="kbc-openai-api-key" name="kbc_openai_api_key" value="<?php echo esc_attr(get_option('kbc_openai_api_key')); ?>">
                    <p class="description">Your OpenAI API key. Get it from <a href="https://platform.openai.com/account/api-keys" target="_blank">OpenAI</a>.</p>
                </div>

                <div class="kbc-form-group" id="kbc-vertex-settings" style="display: none;">
                    <label for="kbc-vertex-ai-credentials">Google Cloud Credentials (JSON)</label>
                    <textarea id="kbc-vertex-ai-credentials" name="kbc_vertex_ai_credentials" rows="5"><?php echo esc_textarea(get_option('kbc_vertex_ai_credentials')); ?></textarea>
                    <p class="description">Your Google Cloud service account credentials in JSON format.</p>
                </div>
            </div>

            <div class="kbc-settings-section">
                <h2>Calendly Integration</h2>
                
                <div class="kbc-form-group">
                    <label for="kbc-calendly-url">Calendly URL</label>
                    <input type="url" id="kbc-calendly-url" name="kbc_calendly_url" value="<?php echo esc_url(get_option('kbc_calendly_url')); ?>">
                    <p class="description">Your Calendly scheduling page URL.</p>
                </div>

                <div class="kbc-form-group">
                    <label for="kbc-scheduling-keywords">Scheduling Trigger Keywords</label>
                    <input type="text" id="kbc-scheduling-keywords" name="kbc_scheduling_keywords" value="<?php echo esc_attr(implode(', ', get_option('kbc_scheduling_keywords', array('schedule', 'book', 'meeting')))); ?>">
                    <p class="description">Comma-separated list of keywords that trigger the scheduling widget.</p>
                </div>
            </div>

            <div class="kbc-settings-section">
                <h2>Content Settings</h2>
                
                <div class="kbc-form-group">
                    <label>Content Types to Include</label>
                    <?php
                    $content_types = get_option('kbc_content_types', array('post', 'page'));
                    $post_types = get_post_types(array('public' => true), 'objects');
                    
                    foreach ($post_types as $post_type) {
                        $checked = in_array($post_type->name, $content_types) ? 'checked' : '';
                        echo '<label class="kbc-checkbox-label">';
                        echo '<input type="checkbox" name="kbc_content_types[]" value="' . esc_attr($post_type->name) . '" ' . $checked . '>';
                        echo esc_html($post_type->label);
                        echo '</label>';
                    }
                    ?>
                </div>

                <div class="kbc-form-group">
                    <label for="kbc-update-frequency">Update Frequency</label>
                    <select id="kbc-update-frequency" name="kbc_update_frequency">
                        <option value="hourly" <?php selected(get_option('kbc_update_frequency'), 'hourly'); ?>>Hourly</option>
                        <option value="daily" <?php selected(get_option('kbc_update_frequency'), 'daily'); ?>>Daily</option>
                        <option value="weekly" <?php selected(get_option('kbc_update_frequency'), 'weekly'); ?>>Weekly</option>
                    </select>
                </div>
            </div>

            <div class="kbc-settings-section">
                <h2>Display Settings</h2>
                
                <div class="kbc-form-group">
                    <label for="kbc-display-method">Display Method</label>
                    <select id="kbc-display-method" name="kbc_display_method">
                        <option value="automatic" <?php selected(get_option('kbc_display_method'), 'automatic'); ?>>Automatic Display</option>
                        <option value="shortcode" <?php selected(get_option('kbc_display_method'), 'shortcode'); ?>>Shortcode Only</option>
                        <option value="both" <?php selected(get_option('kbc_display_method'), 'both'); ?>>Both Methods</option>
                    </select>
                    <p class="description">Choose how the chatbot should be displayed on your website.</p>
                </div>

                <div class="kbc-form-group">
                    <label for="kbc-chatbot-position">Position</label>
                    <select id="kbc-chatbot-position" name="kbc_chatbot_position">
                        <option value="bottom-right" <?php selected(get_option('kbc_chatbot_position'), 'bottom-right'); ?>>Bottom Right</option>
                        <option value="bottom-left" <?php selected(get_option('kbc_chatbot_position'), 'bottom-left'); ?>>Bottom Left</option>
                        <option value="top-right" <?php selected(get_option('kbc_chatbot_position'), 'top-right'); ?>>Top Right</option>
                        <option value="top-left" <?php selected(get_option('kbc_chatbot_position'), 'top-left'); ?>>Top Left</option>
                    </select>
                </div>

                <div class="kbc-form-group">
                    <label for="kbc-chatbot-theme">Theme</label>
                    <select id="kbc-chatbot-theme" name="kbc_chatbot_theme">
                        <option value="light" <?php selected(get_option('kbc_chatbot_theme'), 'light'); ?>>Light</option>
                        <option value="dark" <?php selected(get_option('kbc_chatbot_theme'), 'dark'); ?>>Dark</option>
                    </select>
                </div>

                <div class="kbc-form-group">
                    <label for="kbc-chatbot-icon">Chatbot Icon</label>
                    <select id="kbc-chatbot-icon" name="kbc_chatbot_icon">
                        <option value="default" <?php selected(get_option('kbc_chatbot_icon'), 'default'); ?>>Default Chat Icon</option>
                        <option value="question" <?php selected(get_option('kbc_chatbot_icon'), 'question'); ?>>Question Mark</option>
                        <option value="help" <?php selected(get_option('kbc_chatbot_icon'), 'help'); ?>>Help Icon</option>
                    </select>
                </div>

                <div class="kbc-form-group">
                    <label for="kbc-chatbot-color">Button Color</label>
                    <input type="color" id="kbc-chatbot-color" name="kbc_chatbot_color" value="<?php echo esc_attr(get_option('kbc_chatbot_color', '#007bff')); ?>">
                </div>

                <div class="kbc-form-group">
                    <label for="kbc-exclude-pages">Exclude Pages</label>
                    <textarea id="kbc-exclude-pages" name="kbc_exclude_pages" rows="3"><?php echo esc_textarea(get_option('kbc_exclude_pages')); ?></textarea>
                    <p class="description">Enter page IDs or URLs (one per line) where the chatbot should not appear.</p>
                </div>
            </div>
        </div>

        <?php submit_button(); ?>
    </form>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    function toggleProviderSettings() {
        var provider = $('#kbc-ai-provider').val();
        if (provider === 'openai') {
            $('#kbc-openai-settings').show();
            $('#kbc-vertex-settings').hide();
        } else {
            $('#kbc-openai-settings').hide();
            $('#kbc-vertex-settings').show();
        }
    }

    $('#kbc-ai-provider').on('change', toggleProviderSettings);
    toggleProviderSettings();

    // Handle display method changes
    $('#kbc-display-method').on('change', function() {
        if ($(this).val() === 'shortcode') {
            $('.kbc-form-group:not(:first)').hide();
        } else {
            $('.kbc-form-group').show();
        }
    }).trigger('change');
});
</script> 