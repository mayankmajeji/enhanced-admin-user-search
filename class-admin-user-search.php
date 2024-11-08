<?php

/**
 * Plugin Name: Enhanced Admin User Search
 * Plugin URI: https://wordpress.org/plugins/enhanced-admin-user-search/
 * Description: Enhances the WordPress admin user search functionality to allow searching by first name, last name, full name, display name, and user ID.
 * Version: 1.0.1
 * Author: Mayank Majeji
 * Author URI: https://mayankmajeji.com/
 * License: GPLv2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: enhanced-admin-user-search
 * Requires at least: 5.0
 * Requires PHP: 7.0
 *
 * Enhanced Admin User Search is distributed under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Enhanced Admin User Search is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Enhanced Admin User Search. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package Enhanced Admin User Search
 * @author Mayank Majeji
 * @version 1.0.0
 */

if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

/**
 * Enhanced Admin User Search
 *
 * @package    WP Enhanced Admin User Search.
 * @author     Mayank Majeji.
 */
if (! class_exists('WP_Enhanced_Admin_User_Search')) {

	class WP_Enhanced_Admin_User_Search
	{

		public function __construct()
		{
			// Add action only if in admin area.
			if (is_admin()) {
				add_action('pre_user_query', array($this, 'wp_enhanced_admin_user_search'));
			}
		}

		public function wp_enhanced_admin_user_search($user_search)
		{
			global $wpdb;

			// Ensure that required objects and functions are available.
			if (! isset($user_search->query_vars['search']) || ! method_exists($user_search, 'query_where')) {
				return;
			}

			// Get the search term from the query.
			$search_term = $user_search->query_vars['search'];

			// Check if search term is not empty.
			if (! empty($search_term)) {

				// Trim and remove the wildcard character.
				$search_term = trim($search_term, '*');

				// Escape the search term.
				$search_term = esc_sql($wpdb->esc_like($search_term));

				// Modify the query to include first name, last name, and user ID.
				$user_search->query_from .= " LEFT JOIN {$wpdb->usermeta} AS firstname_meta ON {$wpdb->users}.ID = firstname_meta.user_id AND firstname_meta.meta_key = 'first_name' ";
				$user_search->query_from .= " LEFT JOIN {$wpdb->usermeta} AS lastname_meta ON {$wpdb->users}.ID = lastname_meta.user_id AND lastname_meta.meta_key = 'last_name' ";

				// Update the WHERE clause to include search by first name, last name, and user ID
				$user_search->query_where = str_replace(
					'WHERE 1=1',
					"WHERE 1=1 AND (
                    {$wpdb->users}.user_login LIKE '%{$search_term}%' OR 
                    {$wpdb->users}.user_email LIKE '%{$search_term}%' OR 
                    {$wpdb->users}.display_name LIKE '%{$search_term}%' OR 
                    firstname_meta.meta_value LIKE '%{$search_term}%' OR 
                    lastname_meta.meta_value LIKE '%{$search_term}%' OR 
                    {$wpdb->users}.ID LIKE '%{$search_term}%'
                )",
					$user_search->query_where
				);
			}
		}
	}

	// Initialize the plugin.
	new WP_Enhanced_Admin_User_Search();
}
