<?php
/**
* Plugin Name: Passwordless Authentication
* Plugin URI: http://www.cozmsolabs.com
* Description: Shortcode base login form. Enter an email/username and get a one time link via email that will automatically log you in. 
* Version: 1.0
* Author: Cozmoslabs, sareiodata
* Author URI: http:/www.cozmoslabs.com
* License: GPL2
*/
/* Copyright Cozmoslabs.com 
 
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.
 
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/
 
// Start writing code after this line!


/**
 * Definitions
 *
 *
 */
define( 'PASSWORDLESS_AUTH_VERSION', '1.0' );
define( 'WPA_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . dirname( plugin_basename( __FILE__ ) ) );
define( 'WPA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WPA_TRANSLATE_DIR', WPA_PLUGIN_DIR.'/translation' );
define( 'WPA_TRANSLATE_DOMAIN', 'passwordless' );

/**
 * Function that creates the "Basic Information" submenu page
 *
 * @since v.2.0
 *
 * @return void
 */
function wpa_register_basic_info_submenu_page() {
	add_submenu_page( 'users.php', __( 'Passwordless Authentication', 'passwordless' ), __( 'Passwordless Auth', 'passwordless' ), 'manage_options', 'passwordless-auth', 'wpa_basic_info_content' );
}
add_action( 'admin_menu', 'wpa_register_basic_info_submenu_page', 2 );

/**
 * Function that adds content to the "Passwordless Auth" submenu page
 *
 * @since v.1.0
 *
 * @return string
 */
function wpa_basic_info_content() {
?>
	<div class="wrap wpa-wrap wpa-info-wrap">
		<div class="wpa-badge <?php echo PASSWORDLESS_AUTH_VERSION; ?>"><?php printf( __( 'Version %s' ), PASSWORDLESS_AUTH_VERSION ); ?></div>
		<h1><?php printf( __( '<strong>Passwordless Authentication</strong> <small>v.</small>%s', 'passwordless' ), PASSWORDLESS_AUTH_VERSION ); ?></h1>
		<p class="wpa-info-text"><?php printf( __( 'A front-end login form without a password.', 'passwordless' ) ); ?></p>
		<hr />
		<h2 class="wpa-callout"><?php _e( 'One time password for WordPress', 'passwordless' ); ?></h2>
		<div class="wpa-row wpa-2-col">
			<div>
				<h3><?php _e( '[passwordless-login] shortcode', 'passwordless' ); ?></h3>
				<p><?php _e( 'Just place <strong class="nowrap">[passwordless-login]</strong> shortcode in a page or a widget and you\'re good to go.', 'passwordless' ); ?></p>
			</div>
			<div>
				<h3><?php _e( 'An alternative to passwords', 'passwordless'  ); ?></h3>
				<p><?php _e( 'Passwordless Authentication <strong>dose not</strong> replace the default login functionality in WordPress. Instead you can have the two work in parallel.', 'passwordless' ); ?></p>
				<p><?php _e( 'Join the discussion here: <a href="http://www.cozmoslabs.com/">WordPress Passwordless Authentication</a>', 'passwordless' ); ?></p>
			</div>
		</div>
		<hr/>
		<div>
			<h3><?php _e( 'Take control of the login and registration process.', 'passwordless' );?></h3>
			<p><?php _e( 'Improve upon Passwordless Authentication using the free <a href="https://wordpress.org/plugins/profile-builder/">Profile Builder</a> plugin:', 'passwordless' ); ?></p>
			<div class="wpa-row wpa-3-col">
				<div><p><?php _e('Front-End registration, edit profile and login forms.', 'passwordless'); ?></p></div>
				<div><p><?php _e('Drag and drop to reorder / remove default user profile fields.', 'passwordless'); ?></p></div>
				<div><p><?php _e('Allow users to log in with their username or email.', 'passwordless'); ?></p></div>
				<div><p><?php _e('Enforce minimum password length and minimum password strength.', 'passwordless'); ?></p></div>
				<div><p><?php _e('Custom redirects, including that of the default WordPress login (available in the Pro version only) ', 'passwordless'); ?></p></div>
			</div>
			<p><a href="https://wordpress.org/plugins/profile-builder/" class="button button-primary button-large"><?php _e( 'Learn More About Profile Builder', 'passwordless' ); ?></a></p>
		</div>
	</div>
<?php
}


/**
 * Add scripts and styles to the back-end
 *
 * @since v.1.0
 *
 * @return void
 */
function wpa_print_script( $hook ){
	if ( ( $hook == 'users_page_passwordless-auth' ) ){
		wp_enqueue_style( 'wpa-back-end-style', WPA_PLUGIN_URL . 'assets/style-back-end.css', false, PROFILE_BUILDER_VERSION );
	}
}
add_action( 'admin_enqueue_scripts', 'wpa_print_script' );

/**
 * Add scripts and styles to the front-end
 *
 * @since v.1.0
 *
 * @return void
 */
function wpa_add_plugin_stylesheet() {
	if (  file_exists( WPA_PLUGIN_DIR . '/assets/style-front-end.css' )  ){
		wp_register_style( 'wpa_stylesheet', WPA_PLUGIN_URL . 'assets/style-front-end.css' );
		wp_enqueue_style( 'wpa_stylesheet' );
	}
}
add_action( 'wp_print_styles', 'wpa_add_plugin_stylesheet' );

/**
 * Shortcode for the passwordless login form
 *
 * @since v.1.0
 *
 * @return html
 */
function wpa_front_end_login(){
	ob_start();
	$account = ( isset( $_POST['user_email_username']) ) ? $account = $_POST['user_email_username'] : false;
	$nonce = ( isset( $_POST['nonce']) ) ? $nonce = $_POST['nonce'] : false;
	$error_token = ( isset( $_GET['wpa_error_token']) ) ? $error_token = $_GET['wpa_error_token'] : false;

	$sent_link = wpa_send_link($account, $nonce);

	if( $account && !is_wp_error($sent_link) ){
		echo '<p class="wpa-box wpa-success">'. __('Please check your email. You will soon receive an email with a login link.', 'passwordless') .'</p>';
	} elseif ( is_user_logged_in() ) {
		$current_user = wp_get_current_user();
		echo '<p class="wpa-box wpa-alert">'.sprintf(__( 'You are currently logged in as %1$s. %2$s', 'profilebuilder' ), '<a href="'.$authorPostsUrl = get_author_posts_url( $current_user->ID ).'" title="'.$current_user->display_name.'">'.$current_user->display_name.'</a>', '<a href="'.wp_logout_url( $redirectTo = wpa_curpageurl() ).'" title="'.__( 'Log out of this account', 'passwordless' ).'">'. __( 'Log out', 'passwordless').' &raquo;</a>' ) . '</p><!-- .alert-->';
	} else {
		if ( is_wp_error($sent_link) ){
			echo '<p class="wpa-box wpa-error">' . $sent_link->get_error_message() . '</p>';
		}
		if( $error_token ) {
			echo '<p class="wpa-box wpa-error">' . __('Your token has probably expired. Please try again.', 'passwordless') . '</p>';
		}
	?>
	<form name="wpaloginform" id="wpaloginform" action="" method="post">
		<p>
			<label for="user_email_username"><?php _e('Login with email or username') ?><br />
			<input type="text" name="user_email_username" id="user_email_username" class="input" value="<?php echo esc_attr( $account ); ?>" size="25" /></label>
			<input type="submit" name="wpa-submit" id="wpa-submit" class="button-primary" value="<?php esc_attr_e('Log In'); ?>" />
		</p>
		<?php do_action('wpa_login_form'); ?>
		<?php wp_nonce_field( 'wpa_passwordless_login_request', 'nonce', false ) ?>

	</form>
<?php
	}

	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}
add_shortcode( 'passwordless-login', 'wpa_front_end_login' );

add_filter('widget_text', 'do_shortcode');

/**
 * Checks to see if an account is valid. Either email or username
 *
 * @since v.1.0
 *
 * @return bool / WP_Error
 */
function wpa_valid_account( $account ){

	if( is_email($account) && email_exists( $account ) ){
		return $account;
	}

	if( !is_email( $account ) && username_exists( $account ) ){
		$user = get_user_by('login', $account);
		if($user){
			return $user->data->user_email;
		}
	}
	return new WP_Error( 'invalid_account', __( "The username or email you provided do not exit. Please try again.", "passwordless" ) );
}

/**
 * Sends an email with the unique login link.
 *
 * @since v.1.0
 *
 * @return bool / WP_Error
 */
function wpa_send_link( $email_account = false, $nonce = false ){
	if ( $email_account  == false ){
		return false;
	}
	$valid_email = wpa_valid_account( $email_account  );
	$errors = new WP_Error;
	if (is_wp_error($valid_email)){
		$errors->add('invalid_account', $valid_email->get_error_message());
	} else{
		$blog_name = get_bloginfo( 'name' );
		$unique_url = wpa_generate_url( $valid_email , $nonce );
		$subject = apply_filters('wpa_email_subject', __("Login at $blog_name"));
		$message = apply_filters('wpa_email_message', __("Login at $blog_name by visiting this url: $unique_url"), $unique_url);
		$sent_mail = wp_mail( $valid_email, $subject, $message);

		if ( !$sent_mail ){
			$errors->add('email_not_sent', __('There was a problem sending your email. Please try again or contact an admin.'));
		}
	}
	$error_codes = $errors->get_error_codes();

	if (empty( $error_codes  )){
		return false;
	}else{
		return $errors;
	}
}

/**
 * Generates unique URL based on UID and nonce
 *
 * @since v.1.0
 *
 * @return string
 */
function wpa_generate_url( $email = false, $nonce = false ){
	if ( $email  == false ){
		return false;
	}
	/* get user id */
	$user = get_user_by( 'email', $email );
	$token = wpa_create_onetime_token( 'wpa_'.$user->ID );

	$arr_params = array( 'wpa_error_token', 'uid', 'token', 'nonce' );
	$url = remove_query_arg( $arr_params, wpa_curpageurl() );
	$url .= "?uid=$user->ID&token=$token&nonce=$nonce";

	return $url;
}

/**
 * Automatically logs in a user with the correct nonce
 *
 * @since v.1.0
 *
 * @return string
 */
add_action( 'init', 'wpa_autologin_via_url' );
function wpa_autologin_via_url(){
	if( isset( $_GET['token'] ) && isset( $_GET['uid'] ) && isset( $_GET['nonce'] ) ){
		$uid = $_GET['uid'];
		$token  = $_REQUEST['token'];
		$nonce  = $_REQUEST['nonce'];

		$token_transient = get_transient('wpa_' . $uid);

		$arr_params = array( 'uid', 'token', 'nonce' );
		$current_page_url = remove_query_arg( $arr_params, wpa_curpageurl() );

		if ( ! hash_equals($token_transient, $token) || ! wp_verify_nonce( $nonce, 'wpa_passwordless_login_request' ) ){
			wp_redirect( $current_page_url . '?wpa_error_token=true' );
			exit;
		} else {
			wp_set_auth_cookie( $uid );
			delete_transient( 'wpa_' . $uid );
			wp_redirect( $current_page_url );
			exit;
		}
	}
}

/**
 * Create a nonce like token that you only use once based on transients
 *
 *
 * @since v.1.0
 *
 * @return string
 */
function wpa_create_onetime_token( $action = -1 ) {
	$time = time();
	$string = '_wpa_nonce_' . $action . $time;
	$hash  = wp_hash( $string );
	set_transient( $action , $hash, 60*10 ); // adjust the lifetime of the transient. Currently 10 min.
	return $hash;
}

/**
 * Returns the current page URL
 *
 * @since v.1.0
 *
 * @return string
 */
function wpa_curpageurl() {
	$pageURL = 'http';

	if ((isset($_SERVER["HTTPS"])) && ($_SERVER["HTTPS"] == "on"))
		$pageURL .= "s";

	$pageURL .= "://";

	if ($_SERVER["SERVER_PORT"] != "80")
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];

	else
		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];

	return $pageURL;
}


/**
 * Add notices on plugin activation.
 *
 * @since v.1.0
 *
 * @return string
 */

include_once("inc/wpa.class.notices.php");
$learn_more_notice = new WPA_Add_Notices(
	'wpa_learn_more',
	sprintf( __( '<p>Use [passwordless-login] shortcode in your pages or widgets. %1$sLearn more.%2$s  %3$sDismiss%4$s</p>', 'profilebuilder'), "<a href='users.php?page=passwordless-auth&wpa_learn_more_dismiss_notification=0'>", "</a>", "<a href='". add_query_arg( 'wpa_learn_more_dismiss_notification', '0' ) ."' class='wpa-dismiss-notification' style='float:right;margin-left:20px;'> ", "</a>" ),
	'updated',	'',	''
);