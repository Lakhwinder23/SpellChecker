jQuery(document).ready(function($) {
    var spellCheckButton = $('#spell-check-start');
    var spellCheckProgress = $('#spell-check-progress');
    var spellCheckResults = $('#spell-check-results');

    // Handle the spell check button click event
    spellCheckButton.on('click', function(e) {
		console.log('button clicked');
        e.preventDefault();

        // Disable the button to prevent multiple clicks
        spellCheckButton.attr('disabled', 'disabled');
        spellCheckProgress.html('Spell check in progress...');

        // Start the spell check process
        performSpellCheck(1);
    });

    // Perform the spell check process recursively
    function performSpellCheck(page) {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'spell_check',
                page: page
            },
            success: function(response) {
                spellCheckResults.append(response.data);
                spellCheckProgress.html('Spell check progress: ' + (page * 30) + ' pages checked.');

                // Continue the spell check process for the next page
                if (response.success && response.data.length > 0) {
                    performSpellCheck(page + 1);
                } else {
                    spellCheckButton.removeAttr('disabled');
                    spellCheckProgress.html('Spell check complete!');
                }
            },
            error: function(error) {
                console.log('Error: ' + error);
                spellCheckButton.removeAttr('disabled');
                spellCheckProgress.html('An error occurred during the spell check process.');
            }
        });
    }
});
