<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'kbc_knowledge_base';
$items_per_page = 20;
$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$offset = ($current_page - 1) * $items_per_page;

// Get total items
$total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

// Get items for current page
$items = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $table_name ORDER BY last_updated DESC LIMIT %d OFFSET %d",
    $items_per_page,
    $offset
));

// Calculate total pages
$total_pages = ceil($total_items / $items_per_page);
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="kbc-knowledge-container">
        <div class="kbc-knowledge-header">
            <div class="kbc-search-box">
                <input type="text" id="kbc-search-input" placeholder="Search knowledge base...">
                <button id="kbc-search-button" class="button">Search</button>
            </div>
            <button id="kbc-export-knowledge" class="button">Export Knowledge Base</button>
        </div>

        <div class="kbc-knowledge-table-container">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Content ID</th>
                        <th>Content Preview</th>
                        <th>Last Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)) : ?>
                        <tr>
                            <td colspan="6">No knowledge base entries found.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($items as $item) : ?>
                            <tr>
                                <td><?php echo esc_html($item->id); ?></td>
                                <td><?php echo esc_html($item->content_type); ?></td>
                                <td><?php echo esc_html($item->content_id); ?></td>
                                <td><?php echo esc_html(wp_trim_words($item->content_text, 20)); ?></td>
                                <td><?php echo esc_html($item->last_updated); ?></td>
                                <td>
                                    <button class="button kbc-view-item" data-id="<?php echo esc_attr($item->id); ?>">View</button>
                                    <button class="button kbc-delete-item" data-id="<?php echo esc_attr($item->id); ?>">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1) : ?>
            <div class="kbc-pagination">
                <?php
                echo paginate_links(array(
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'prev_text' => __('&laquo;'),
                    'next_text' => __('&raquo;'),
                    'total' => $total_pages,
                    'current' => $current_page
                ));
                ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div id="kbc-item-modal" class="kbc-modal" style="display: none;">
    <div class="kbc-modal-content">
        <span class="kbc-modal-close">&times;</span>
        <h2>Knowledge Base Entry</h2>
        <div class="kbc-modal-body">
            <div class="kbc-modal-field">
                <label>Type:</label>
                <span id="kbc-modal-type"></span>
            </div>
            <div class="kbc-modal-field">
                <label>Content ID:</label>
                <span id="kbc-modal-content-id"></span>
            </div>
            <div class="kbc-modal-field">
                <label>Content:</label>
                <div id="kbc-modal-content"></div>
            </div>
            <div class="kbc-modal-field">
                <label>Metadata:</label>
                <pre id="kbc-modal-meta"></pre>
            </div>
            <div class="kbc-modal-field">
                <label>Last Updated:</label>
                <span id="kbc-modal-last-updated"></span>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // View item
    $('.kbc-view-item').on('click', function() {
        var id = $(this).data('id');
        
        $.ajax({
            url: kbc_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'kbc_get_knowledge_item',
                nonce: kbc_admin.nonce,
                id: id
            },
            success: function(response) {
                if (response.success) {
                    var item = response.data;
                    $('#kbc-modal-type').text(item.content_type);
                    $('#kbc-modal-content-id').text(item.content_id);
                    $('#kbc-modal-content').text(item.content_text);
                    $('#kbc-modal-meta').text(JSON.stringify(JSON.parse(item.content_meta), null, 2));
                    $('#kbc-modal-last-updated').text(item.last_updated);
                    $('#kbc-item-modal').show();
                } else {
                    alert('Failed to get item: ' + response.data);
                }
            }
        });
    });

    // Delete item
    $('.kbc-delete-item').on('click', function() {
        if (!confirm('Are you sure you want to delete this item?')) {
            return;
        }

        var button = $(this);
        var id = button.data('id');
        
        $.ajax({
            url: kbc_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'kbc_delete_knowledge_item',
                nonce: kbc_admin.nonce,
                id: id
            },
            success: function(response) {
                if (response.success) {
                    button.closest('tr').fadeOut(function() {
                        $(this).remove();
                    });
                } else {
                    alert('Failed to delete item: ' + response.data);
                }
            }
        });
    });

    // Close modal
    $('.kbc-modal-close').on('click', function() {
        $('#kbc-item-modal').hide();
    });

    // Search
    $('#kbc-search-button').on('click', function() {
        var search = $('#kbc-search-input').val();
        window.location.href = addQueryParam(window.location.href, 's', search);
    });

    // Export
    $('#kbc-export-knowledge').on('click', function() {
        window.location.href = kbc_admin.ajax_url + '?action=kbc_export_knowledge&nonce=' + kbc_admin.nonce;
    });

    function addQueryParam(url, key, value) {
        var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
        var separator = url.indexOf('?') !== -1 ? "&" : "?";
        if (url.match(re)) {
            return url.replace(re, '$1' + key + "=" + value + '$2');
        } else {
            return url + separator + key + "=" + value;
        }
    }
});
</script> 