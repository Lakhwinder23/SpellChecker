<div class="wrap">
    <h1>Spell Check</h1>
    <p>Click the button below to start the spell check process.</p>
    <button id="spell-check-start" class="button button-primary">Start Spell Check</button>
    <div id="spell-check-progress"></div>
    <div id="spell-check-results"></div>
</div>

<style>
    .spell-check-table {
        border-collapse: collapse;
        width: 100%;
        margin-top: 20px;
    }
    
    .spell-check-table th,
    .spell-check-table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }
    
    .spell-check-table th {
        background-color: #f2f2f2;
    }
    
    .spell-check-suggestions {
        display: flex;
        align-items: center;
    }
    
    .spell-check-suggestions .ignore-button,
    .spell-check-suggestions .edit-button {
        margin-left: 10px;
    }
</style>

<script>
    jQuery(document).ready(function($) {
        $('#spell-check-start').on('click', function() {
            $('#spell-check-progress').html('Spell check in progress...');
            $('#spell-check-results').empty();

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'spell_check',
                    _wpnonce: '<?php echo wp_create_nonce('spell_check_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        $('#spell-check-progress').html('Spell check completed.');
                        $('#spell-check-results').html(response.data);
                    } else {
                        $('#spell-check-progress').html('Spell check failed.');
                    }
                },
                error: function() {
                    $('#spell-check-progress').html('Spell check failed.');
                }
            });
        });
    });
</script>
