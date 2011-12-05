<?php
/*
Plugin Name: Social Debug
Description: When enabled, this will create a notice that will take you to a page with information you can send to support@crowdfavorite.com to help us debug Social issues.
Version: 1.0
Author: Crowd Favorite
Author URI: http://crowdfavorite.com/
*/
function wp_social_debug_notice() {
	echo '<div class="error"><p>To help debug Social, please send the output of this <a href="'.esc_url(admin_url('?wp_social_debug=true')).'" target="_blank">link</a> to <a href="mailto:support@crowdfavorite.com">support@crowdfavorite.com</a></p></div>';
}
add_action('admin_notices', 'wp_social_debug_notice');

function wp_social_debug_init() {
	if (isset($_GET['wp_social_debug']) and current_user_can('manage_options')) {
		if (isset($_GET['post_id'])) {
			global $wpdb;

			$post_id = $_GET['post_id'];
			$post = $wpdb->get_results("
				SELECT *
				  FROM $wpdb->posts
				 WHERE ID = $post_id
			");

			if (is_array($post) and isset($post[0])) {
				$post = $post[0];

				echo '-- Post:<br /><br />';
				echo 'INSERT INTO '.$wpdb->posts.'(ID, post_author, post_date, post_date_gmt, post_content, post_title, post_excerpt, post_status, comment_status, ping_status, post_password, post_name, to_ping, pinged, post_modified, post_modified_gmt, post_content_filtered, post_parent, guid, menu_order, post_type, post_mime_type, comment_count)'.
					" VALUES('$post->ID', '$post->post_author', '$post->post_date', '$post->post_date_gmt', '$post->post_content', '$post->post_title', '$post->post_excerpt', '$post->post_status', '$post->comment_status', '$post->ping_status', '$post->post_password', '$post->post_name', '$post->to_ping', '$post->pinged', '$post->post_modified', '$post->post_modified_gmt', '$post->post_content_filtered', '$post->post_parent', '$post->guid', '$post->menu_order', '$post->post_type', '$post->post_mime_type', '$post->comment_count');<br />";

				$comment_ids = array();
				$comments = $wpdb->get_results("
					SELECT *
					  FROM $wpdb->comments
					 WHERE comment_post_ID = $post_id
				");
				echo '-- Comments:<br /><br />';
				foreach ($comments as $comment) {
					$comment_ids[] = $comment->comment_ID;

					echo 'INSERT INTO '.$wpdb->comments.'(comment_ID, comment_post_ID, comment_author, comment_author_email, comment_author_url, comment_author_IP, comment_date, comment_date_gmt, comment_content, comment_karma, comment_approved, comment_agent, comment_type, comment_parent, user_id)'.
						" VALUES('$comment->comment_ID', '$comment->comment_post_ID', '$comment->comment_author', '$comment->comment_author_email', '$comment->comment_auth_url', '$comment->comment_author_IP', '$comment->comment_date', '$comment->comment_date_gmt', '$comment->comment_content', '$comment->comment_karma', '$comment->comment_approved', '$comment->comment_agent', '$comment->comment_type', '$comment->comment_parent', '$comment->user_id');<br />";
				}

				$comment_ids = implode(',', $comment_ids);
				$results = $wpdb->get_results("
					SELECT *
					  FROM $wpdb->commentmeta
					 WHERE comment_id IN ($comment_ids)
				");
				foreach ($results as $result) {
					echo 'INSERT INTO '.$wpdb->commentmeta.'(comment_id, meta_key, meta_value)'." VALUES('$result->comment_id', '$result->meta_key', '$result->meta_value');<br />";
				}
			}
		}
		else {
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
		}

		exit;
	}
}
add_action('init', 'wp_social_debug_init', 1);
