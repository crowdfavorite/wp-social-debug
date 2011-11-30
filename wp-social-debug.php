<?php
/*
Plugin Name: Social Debug
Description: When enabled, this will create a notice that will take you to a page with information you can send to support@crowdfavorite.com to help us debug Social issues.
Version: 1.0
Author: Crowd Favorite
Author URI: http://crowdfavorite.com/
*/
function wp_social_debug_notice() {
	echo '<div class="error"><p>To help debug Social, please send the output of this <a href="'.esc_url(admin_url('?wp_social_debug=true')).'">link</a> to <a href="mailto:support@crowdfavorite.com">support@crowdfavorite.com</a></p></div>';
}
add_action('admin_notices', 'wp_social_debug_notice');

function wp_social_debug_init() {
	if (isset($_GET['wp_social_debug'])) {
		echo 'PHP Version: '.phpversion().'<br />';
		echo 'Social Version: '.get_option('social_installed_version', 'NOT INSTALLED').'<br /><br />';

		$universal_accounts = get_option('social_accounts');
		if (!$universal_accounts) {
			$universal_accounts = 'NO UNIVERSAL ACCOUNTS';
		}
		else {
			$universal_accounts = htmlentities(serialize($universal_accounts));
		}
		echo 'Universal Accounts:<br />'.$universal_accounts.'<br /><br />';

		$personal_accounts = get_user_meta(get_current_user_id(), 'social_accounts', true);
		if (empty($personal_accounts)) {
			$personal_accounts = 'NO PERSONAL ACCOUNTS';
		}
		else {
			$personal_accounts = htmlentities(serialize($personal_accounts));
		}

		echo 'Personal Accounts:<br />'.$personal_accounts;

		exit;
	}
}
add_action('init', 'wp_social_debug_init', 1);
