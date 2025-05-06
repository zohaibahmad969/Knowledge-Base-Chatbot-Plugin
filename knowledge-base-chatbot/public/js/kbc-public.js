jQuery(document).ready(function($) {
    var $container = $('.kbc-chatbot-container');
    var $launcher = $('.kbc-chatbot-launcher');
    var $messageArea = $('.kbc-message-area');
    var $input = $('.kbc-input');
    var $sendButton = $('.kbc-send-button');
    var $typingIndicator = $('.kbc-typing-indicator');
    var $calendlyContainer = $('.kbc-calendly-container');
    var sessionId = '';
    var isScrolledToBottom = true;

    // Auto-resize textarea
    $input.on('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });

    // Check if scrolled to bottom
    $messageArea.on('scroll', function() {
        isScrolledToBottom = Math.abs($(this)[0].scrollHeight - $(this).scrollTop() - $(this).outerHeight()) < 1;
    });

    // Scroll to bottom
    function scrollToBottom() {
        if (isScrolledToBottom) {
            $messageArea.scrollTop($messageArea[0].scrollHeight);
        }
    }

    // Send message
    function sendMessage() {
        var message = $input.val().trim();
        if (!message) return;

        // Add user message
        addMessage(message, 'user');
        $input.val('').height('auto');
        $typingIndicator.show();
        scrollToBottom();

        // Send to server
        $.ajax({
            url: kbcPublic.ajax_url,
            type: 'POST',
            data: {
                action: 'kbc_chat',
                nonce: kbcPublic.nonce,
                message: message,
                session_id: sessionId
            },
            success: function(response) {
                if (response.success) {
                    sessionId = response.data.session_id;
                    addMessage(response.data.response, 'bot');
                    
                    if (response.data.show_scheduling) {
                        $calendlyContainer.slideDown();
                    }
                } else {
                    addMessage('Sorry, I encountered an error. Please try again.', 'bot');
                }
            },
            error: function() {
                addMessage('Sorry, I encountered an error. Please try again.', 'bot');
            },
            complete: function() {
                $typingIndicator.hide();
                scrollToBottom();
            }
        });
    }

    // Add message to chat
    function addMessage(message, type) {
        var $message = $('<div class="kbc-message kbc-' + type + '-message">');
        var $content = $('<div class="kbc-message-content">').text(message);
        $message.append($content);
        $messageArea.append($message);
        scrollToBottom();
    }

    // Event handlers
    $sendButton.on('click', sendMessage);
    
    $input.on('keypress', function(e) {
        if (e.which === 13 && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    // Toggle chat
    $('.kbc-chatbot-launcher-button').on('click', function() {
        $container.slideDown(300, function() {
            $(this).css('transform', 'translateY(0)');
        });
        $launcher.hide();
    });

    $('.kbc-minimize-button').on('click', function() {
        $container.css('transform', 'translateY(20px)');
        setTimeout(function() {
            $container.slideUp(300);
            $launcher.show();
        }, 300);
    });

    $('.kbc-close-button').on('click', function() {
        $container.css('transform', 'translateY(20px)');
        setTimeout(function() {
            $container.slideUp(300);
            $launcher.show();
        }, 300);
    });

    // Initialize Calendly
    if (typeof Calendly !== 'undefined' && kbcPublic.calendly_url) {
        Calendly.initInlineWidget({
            url: kbcPublic.calendly_url,
            parentElement: $calendlyContainer[0],
            prefill: {},
            utm: {}
        });
    }

    // Handle mobile view
    function handleMobileView() {
        if (window.innerWidth <= 768) {
            $container.css({
                'width': '100%',
                'height': '100%',
                'bottom': '0',
                'right': '0',
                'border-radius': '0'
            });
        } else {
            $container.css({
                'width': '350px',
                'height': '500px',
                'bottom': '20px',
                'right': '20px',
                'border-radius': '10px'
            });
        }
    }

    // Initial mobile view check
    handleMobileView();

    // Handle window resize
    $(window).on('resize', handleMobileView);
}); 