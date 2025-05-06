<?php
if (!defined('ABSPATH')) {
    exit;
}

$position = get_option('kbc_chatbot_position', 'bottom-right');
$theme = get_option('kbc_chatbot_theme', 'light');
$icon = get_option('kbc_chatbot_icon', 'default');
?>

<div class="kbc-chatbot-container kbc-<?php echo esc_attr($position); ?> kbc-<?php echo esc_attr($theme); ?>-theme">
    <div class="kbc-chatbot-header">
        <h3>Knowledge Base Assistant</h3>
        <div class="kbc-chatbot-actions">
            <button class="kbc-minimize-button">_</button>
            <button class="kbc-close-button">×</button>
        </div>
    </div>
    
    <div class="kbc-chatbot-body">
        <div class="kbc-message-area">
            <div class="kbc-message kbc-bot-message">
                <div class="kbc-message-content">
                    Hello! I'm your Knowledge Base Assistant. How can I help you today?
                </div>
            </div>
        </div>
        
        <div class="kbc-chatbot-footer">
            <div class="kbc-typing-indicator">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
        <div class="kbc-input-area">
            <textarea class="kbc-input" placeholder="Type your message here..." rows="1"></textarea>
            <button class="kbc-send-button">Send</button>
        </div>
    </div>
    
</div>

<div class="kbc-chatbot-launcher kbc-<?php echo esc_attr($position); ?>">
    <button class="kbc-chatbot-launcher-button">
        <?php if ($icon === 'default') : ?>
          <img src="<?php echo KBC_PLUGIN_URL . 'public/images/chat-icon.png'; ?>" alt="Chat Icon" />

        <?php elseif ($icon === 'question') : ?>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                <path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v2h-2zm0 4h2v6h-2z"/>
            </svg>
        <?php else : ?>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                <path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v2h-2zm0 4h2v6h-2z"/>
            </svg>
        <?php endif; ?>
    </button>
</div>

<?php if ($this->calendly->is_configured()) : ?>
<div class="kbc-calendly-container" style="display: none;">
    <?php echo $this->calendly->get_scheduling_widget(); ?>
</div>
<?php endif; ?>

<script type="text/javascript">
jQuery(document).ready(function($) {
    var $container = $('.kbc-chatbot-container');
    var $launcher = $('.kbc-chatbot-launcher');
    var $messages = $('.kbc-chatbot-messages');
    var $input = $('.kbc-chatbot-input');
    var $send = $('.kbc-chatbot-send');
    var $typing = $('.kbc-chatbot-typing');
    var $calendly = $('.kbc-calendly-container');
    var sessionId = '';

    // Auto-resize textarea
    $input.on('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });

    // Send message
    function sendMessage() {
        var message = $input.val().trim();
        if (!message) return;

        // Add user message
        addMessage(message, 'user');
        $input.val('').height('auto');
        $typing.show();

        // Send to server
        $.ajax({
            url: kbc_public.ajax_url,
            type: 'POST',
            data: {
                action: 'kbc_chat',
                nonce: kbc_public.nonce,
                message: message,
                session_id: sessionId
            },
            success: function(response) {
                if (response.success) {
                    sessionId = response.data.session_id;
                    addMessage(response.data.response, 'bot');
                    
                    if (response.data.show_scheduling) {
                        $calendly.slideDown();
                    }
                } else {
                    addMessage('Sorry, I encountered an error. Please try again.', 'bot');
                }
            },
            error: function() {
                addMessage('Sorry, I encountered an error. Please try again.', 'bot');
            },
            complete: function() {
                $typing.hide();
            }
        });
    }

    // Add message to chat
    function addMessage(message, type) {
        var $message = $('<div class="kbc-chatbot-message kbc-' + type + '-message">');
        var $content = $('<div class="kbc-message-content">').text(message);
        $message.append($content);
        $messages.append($message);
        $messages.scrollTop($messages[0].scrollHeight);
    }

    // Event handlers
    $send.on('click', sendMessage);
    $input.on('keypress', function(e) {
        if (e.which === 13 && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    // Toggle chat
    $('.kbc-chatbot-launcher-button').on('click', function() {
        $container.slideDown();
        $launcher.hide();
    });

    $('.kbc-chatbot-minimize').on('click', function() {
        $container.slideUp();
        $launcher.show();
    });

    $('.kbc-chatbot-close').on('click', function() {
        $container.slideUp();
        $launcher.show();
    });

    // Initialize Calendly
    if (typeof Calendly !== 'undefined') {
        Calendly.initInlineWidget({
            url: '<?php echo esc_url(get_option('kbc_calendly_url')); ?>',
            parentElement: document.querySelector('.kbc-calendly-container'),
            prefill: {},
            utm: {}
        });
    }
});
</script> 