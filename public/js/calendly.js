(function() {
    var script = document.createElement('script');
    script.src = 'https://assets.calendly.com/assets/external/widget.js';
    script.async = true;
    document.head.appendChild(script);

    window.Calendly = {
        initInlineWidget: function(options) {
            if (!options.url) {
                console.error('Calendly URL is required');
                return;
            }

            var widget = document.createElement('div');
            widget.className = 'calendly-inline-widget';
            widget.setAttribute('data-url', options.url);
            
            if (options.parentElement) {
                options.parentElement.appendChild(widget);
            } else {
                document.body.appendChild(widget);
            }

            // Add prefill data if provided
            if (options.prefill) {
                for (var key in options.prefill) {
                    widget.setAttribute('data-' + key, options.prefill[key]);
                }
            }

            // Add UTM parameters if provided
            if (options.utm) {
                for (var key in options.utm) {
                    widget.setAttribute('data-utm-' + key, options.utm[key]);
                }
            }
        }
    };
})(); 