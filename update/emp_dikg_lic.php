<?php

define( 'DIKG_AUTHOR', 'Alex Brinkman' );
define( 'DIKG_STORE_URL', 'https://elegantmarketplace.com' ); 
define( 'DIKG_PRODUCT_NAME', 'Divi Icon King' ); 
define( 'DIKG_PRODUCT_ID', '402061');

if( !class_exists( 'EDD_SL_Plugin_Updater' ) ) {
	// load our custom updater
	include( dirname( DIKG_PLUGIN_FILE ) . '/update/EDD_SL_Plugin_Updater.php' );
}

function dikg_prod_updater() {

	// retrieve our license key from the DB
	$dikg_license = trim( get_option('dikg_license_key') );
	
	// setup the updater
	$edd_updater = new EDD_SL_Plugin_Updater( DIKG_STORE_URL, DIKG_PLUGIN_FILE, array(
			'version' 	=> DIKG_VERSION, 		// current version number
			'license' 	=> $dikg_license, 		// license key (used get_option above to retrieve from DB)
			'item_name' => DIKG_PRODUCT_NAME, 	// name of this plugin
			'item_id'	=> DIKG_PRODUCT_ID,		// ID of your EMP product as shown in EMP dashboard
			'author' 	=> DIKG_AUTHOR,  		// author of this plugin
			'beta'		=> false,
		)
	);

}
add_action( 'admin_init', 'dikg_prod_updater');

function dikg_activate_license() {

	// listen for our activate button to be clicked
	if( isset( $_POST['dikg_activate'] ) ) {

		// run a quick security check
	 	if( ! check_admin_referer( 'dikg_nonce', 'dikg_nonce' ) )
			return; // get out if we didn't click the Activate button

		register_setting( DIKG_SETTINGS, 'dikg_license_status' );

		// retrieve the license from the database
		$license = ( isset( $_POST['dikg_license_key'] ) && $_POST['dikg_license_key'] ) ? trim( $_POST['dikg_license_key'] ) : trim( get_option( 'dikg_license_key' ) );

		// data to send in our API request
		$api_params = array(
			'edd_action' => 'activate_license',
			'license'    => $license,
			'item_name'  => urlencode( DIKG_PRODUCT_NAME ), // the name of our product in EDD
			'url'        => home_url()
		);

		// Call the custom API.
		$response = wp_remote_post( DIKG_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

			if ( is_wp_error( $response ) ) {
				$message = $response->get_error_message();
			} else {
				$message = __( 'An error occurred, please try again.' );
			}

		} else {

			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			if ( false === $license_data->success ) {

				switch( $license_data->error ) {

					case 'expired' :

						$message = sprintf(
							__( 'Your license key expired on %s.' ),
							date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
						);
						break;

					case 'revoked' :

						$message = __( 'Your license key has been disabled.' );
						break;

					case 'missing' :

						$message = __( 'Invalid license.' );
						break;

					case 'invalid' :
					case 'site_inactive' :

						$message = __( 'Your license is not active for this URL.' );
						break;

					case 'item_name_mismatch' :

						$message = sprintf( __( 'This appears to be an invalid license key for %s.' ), DIKG_PRODUCT_NAME );
						break;

					case 'no_activations_left':

						$message = __( 'Your license key has reached its activation limit.' );
						break;

					default :

						$message = __( 'An error occurred, please try again.' );
						break;
				}

			}

		}

		// Check if anything passed on a message constituting a failure
		if ( ! empty( $message ) ) {
			$base_url = admin_url( 'options-general.php?page=' . DIKG_SETTINGS );
			$redirect = add_query_arg( array( 'sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );

			wp_redirect( $redirect );
			exit();
		}

		// $license_data->license will be either "valid" or "invalid"
		update_option( 'dikg_license_status', $license_data->license );
		update_option( 'dikg_license_key', $license );

		wp_redirect( admin_url( 'options-general.php?page=' . DIKG_SETTINGS ) );
		exit();
	}
}
add_action('admin_init', 'dikg_activate_license');

function dikg_deactivate_license() {

	// listen for our activate button to be clicked
	if( isset( $_POST['dikg_deactivate'] ) ) {

		// run a quick security check
	 	if( ! check_admin_referer( 'dikg_nonce', 'dikg_nonce' ) )
			return; // get out if we didn't click the Activate button

		// retrieve the license from the database
		$license = trim( get_option( 'dikg_license_key' ) );

		// data to send in our API request
		$api_params = array(
			'edd_action' => 'deactivate_license',
			'license'    => $license,
			'item_name'  => urlencode( DIKG_PRODUCT_NAME ), // the name of our product in EDD
			'url'        => home_url()
		);

		// Call the custom API.
		$response = wp_remote_post( DIKG_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

			if ( is_wp_error( $response ) ) {
				$message = $response->get_error_message();
			} else {
				$message = __( 'An error occurred, please try again.' );
			}

			$base_url = admin_url( 'options-general.php?page=' . DIKG_SETTINGS );
			$redirect = add_query_arg( array( 'sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );

			wp_redirect( $redirect );
			exit();
		}

		// decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		// $license_data->license will be either "deactivated" or "failed"
		if( $license_data->license == 'deactivated' ) {
			delete_option( 'dikg_license_status' );
		}

		wp_redirect( admin_url( 'options-general.php?page=' . DIKG_SETTINGS ) );
		exit();

	}
}
add_action('admin_init', 'dikg_deactivate_license');

/**
 * This is a means of catching errors from the activation method above and displaying it to the customer
 */
function dikg_admin_notices() {
	if ( isset( $_GET['sl_activation'] ) && ! empty( $_GET['message'] ) ) {

		switch( $_GET['sl_activation'] ) {

			case 'false':
				$message = urldecode( $_GET['message'] );
				?>
				<div class="error">
					<p><?php echo $message; ?></p>
				</div>
				<?php
				break;

			case 'true':
			default:
				// Developers can put a custom success message here for when activation is successful if they way.
				break;

		}
	}
}
add_action( 'admin_notices', 'dikg_admin_notices' );