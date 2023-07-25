<?php 

/**
 * Plugin Name: Spell Checker
 * Description: Plugin for spell checking posts and pages.
 */

// Include the necessary files
require_once 'vendor/autoload.php';

use PhpSpellcheck\Spellchecker\Hunspell;

// Enqueue scripts and styles
function spell_check_enqueue_scripts() {
    wp_enqueue_script('spell-check', plugin_dir_url(__FILE__) . 'js/spell-check.js', array('jquery'), '1.0', true);
}
add_action('admin_enqueue_scripts', 'spell_check_enqueue_scripts');

// Add spell check button to the WordPress dashboard
function spell_check_add_dashboard_button() {
    add_dashboard_page('Spell Check', 'Spell Check', 'manage_options', 'spell-check', 'spell_check_dashboard');
}
add_action('admin_menu', 'spell_check_add_dashboard_button');

// Spell check all posts or pages and display the results on the dashboard page
function spell_check_dashboard() {
    // Render the spell check dashboard page
    include(plugin_dir_path(__FILE__) . 'templates/spell-check-dashboard.php');
}

function RemoveShortcodes($beginning, $end, $string) {
  $beginningPos = strpos($string, $beginning);
  $endPos = strpos($string, $end);
  if ($beginningPos === false || $endPos === false) {return $string;}
  $textToDelete = substr($string, $beginningPos, ($endPos + strlen($end)) - $beginningPos);
  return RemoveShortcodes($beginning, $end, str_replace($textToDelete, '', $string));
}

// AJAX action for performing the spell check
function spell_check_ajax_action() {
   

    // Create the Hunspell spell checker
    $spellChecker = Hunspell::create();

    // Specify the language to use for spell checking
    $language = 'en_US';

    // Get the current page number
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;

    // Set the number of posts per page
    $posts_per_page = 3;

    // Check the posts or pages for spelling errors
    $posts = get_posts(array(
        'post_type' => array('post', 'page'),
        'post_status' => 'publish',
        'numberposts' => $posts_per_page,
        'offset' => ($page - 1) * $posts_per_page,
    ));

    $spell_check_results = '<table class="spell-check-table">';
    $spell_check_results .= '<tr><th>Title</th><th>Misspelled Word</th><th>Actions</th></tr>';

    foreach ($posts as $post) {
		
		 //$content = RemoveShortcodes('/\[\/?et_pb.*?\]/', '', $post->post_content);
        	$content = apply_filters('the_content', $post->post_content);
		
		$content = preg_replace('/<style\b[^<]*(?:(?!<\/style>)<[^<]*)*<\/style>/i', '', $content);

        // Spell check the content
        $misspellings = $spellChecker->check(utf8_encode($content), ['en_US']);

        if (!empty($misspellings)) {
            $spell_check_results .= '<tr>';
            $spell_check_results .= '<td>' . $post->post_title . '</td>';
            $spell_check_results .= '<td>';
			
			$hasMisspellings = false;

            foreach ($misspellings as $misspelling) {
                $word = $misspelling->getWord();
                if (strlen($word) > 3) {
                    $spell_check_results .= '<div>Misspelled word: ' . $word . '</div>';
                    //$spell_check_results .= '<div>Suggestions: ' . implode(', ', $misspelling->getSuggestions()) . '</div>';
					$hasMisspellings = true;
                }
            }

            $spell_check_results .= '</td>';
            $spell_check_results .= '<td class="spell-check-suggestions">';
            $spell_check_results .= '<button class="ignore-button button-secondary">Add To Dictionary</button>';
            $spell_check_results .= '<button class="edit-button button-primary">Edit ' . ($post->post_type === 'post' ? 'Post' : 'Page') . '</button>';
            $spell_check_results .= '</td>';
            $spell_check_results .= '</tr>';
			if (!$hasMisspellings) {
                $spell_check_results .= '<tr class="empty-row">';
                $spell_check_results .= '<td colspan="4">No misspellings found</td>';
                $spell_check_results .= '</tr>';
            }
        }
    }

    $spell_check_results .= '</table>';

    // Return the spell check results
    wp_send_json_success($spell_check_results);
}
add_action('wp_ajax_spell_check', 'spell_check_ajax_action');
add_action('wp_ajax_nopriv_spell_check', 'spell_check_ajax_action');
