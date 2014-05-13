<?php
/*
Plugin Name: Login Info Tracker
Description: Track some info about when users last logged in
Version: 0.1
Author: Katherine Semel
*/

if ( ! class_exists( 'Login_Info_Tracker' ) ) {
	class Login_Info_Tracker {

	    function Login_Info_Tracker() {
			// Store info from a user's last login as user meta
	        add_action( 'wp_login', array( $this, 'user_last_login' ), 10, 2 );

	        // Admin panels
        	add_action( 'admin_menu', array( $this, 'add_settings_menu' ) );
		}

		function user_last_login( $user_login, $user ){
	    	// Store timestamp of a user's last login as user meta
	        update_user_meta( $user->ID, '_last_login', time() );

	        // Also track the last browser they used
	        if ( isset($_SERVER['HTTP_USER_AGENT']) ) {
	            update_user_meta( $user->ID, '_last_browser', addslashes($_SERVER['HTTP_USER_AGENT']) );
	        }
	    }

	    function add_settings_menu() {
	        // Settings Panel
	        $page_title = 'Last Logins';
	        $menu_title = 'Last Logins';
	        $capability = 'manage_options';
	        $menu_slug  = 'display_last_logins';

	        add_management_page( $page_title, $menu_title, $capability, $menu_slug, array( $this, 'display_last_logins' ) );
	    }

	    function display_last_logins() {
	        ?>
	        <div class="wrap">
	            <h2>Last Logins</h2>

	            <table class="wp-list-table widefat">
	            <tr>
	                <th>User</th>
	                <th>Last Login</th>
	                <th>Last Browser</th>
                </tr>
	            <?php
	            	global $wpdb;

	            	$last_logins = $wpdb->get_results( 'SELECT
	            			user_login,
	            			FROM_UNIXTIME( logintime.meta_value, "%Y-%m-%d %H:%i:%s" ) as login_time,
	            			browser.meta_value as user_browser
						FROM ' . $wpdb->prefix . 'users
							JOIN ' . $wpdb->prefix . 'usermeta as logintime ON logintime.user_id = ID
							JOIN ' . $wpdb->prefix . 'usermeta as browser ON browser.user_id = ID
						WHERE logintime.meta_key = "_last_login"
							AND browser.meta_key = "_last_browser"
						ORDER BY logintime.meta_value DESC;'
					);

					foreach ( $last_logins as $user ) {
						echo '<tr>';
	                    echo '<td>'. $user->user_login .'</td>';
	                    echo '<td>'. $user->login_time .'</td>';
	                    echo '<td>'. $user->user_browser .'</td>';
	                    echo '</tr>';
	                }
	            ?>
	        	</table>

	        </div>
	        <?php
	    }
	}

	$Login_Info_Tracker = new Login_Info_Tracker();
}
