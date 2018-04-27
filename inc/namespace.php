<?php

namespace Pressbooks\Lti\Provider;

/**
 * Generate a globally unique identifier (GUID)
 *
 * @return string
 */
function globally_unique_identifier() {
	$option = 'pressbooks_lti_GUID';
	$guid = get_site_option( $option );
	if ( ! $guid ) {
		if ( function_exists( 'com_create_guid' ) === true ) {
			$guid = trim( com_create_guid(), '{}' );
		} else {
			$guid = sprintf( '%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand( 0, 65535 ), mt_rand( 0, 65535 ), mt_rand( 0, 65535 ), mt_rand( 16384, 20479 ), mt_rand( 32768, 49151 ), mt_rand( 0, 65535 ), mt_rand( 0, 65535 ), mt_rand( 0, 65535 ) );
		}
		update_site_option( $option, $guid );
	}
	return $guid;
}

/**
 * Processes a launch request from an LTI tool consumer
 *
 * Hooked into action: pb_do_format
 *
 * @param $format string
 */
function do_format( $format ) {
	$params = explode( '/', $format );
	$controller = array_shift( $params );
	$action = array_shift( $params );
	if ( 'lti' === $controller && \Pressbooks\Book::isBook() ) {
		$controller = new Controller();
		$controller->handleRequest( $action, $params );
		exit;
	}
}

/**
 * @return \Jenssegers\Blade\Blade
 */
function blade() {
	$views = __DIR__ . '/../templates';
	$cache = \Pressbooks\Utility\get_cache_path();
	$blade = new \Jenssegers\Blade\Blade( $views, $cache, new \Pressbooks\Container() );
	return $blade;
}

function admin_menu() {

	$parent_slug = \Pressbooks\Admin\Dashboard\init_network_integrations_menu();

	add_submenu_page(
		$parent_slug,
		__( 'LTI', 'pressbooks-lti-provider' ),
		__( 'LTI', 'pressbooks-lti-provider' ),
		'manage_network',
		'pb_lti_admin',
		function () {
			if ( ! class_exists( 'WP_List_Table' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
			}
			$table = new Table();
			$table->prepare_items();

			$message = '';
			if ( 'delete' === $table->current_action() ) {
				/* translators: 1: Number of consumers deleted */
				$message = '<div class="updated below-h2" id="message"><p>' . sprintf( __( 'Consumers deleted: %d', 'pressbooks-lti-provider' ), count( $_REQUEST['ID'] ) ) . '</p></div>';
			}
			echo '<div class="wrap">';
			echo $message;
			echo '<form id="pressbooks-lti-admin" method="GET">';
			echo '<input type="hidden" name="page" value="' . $_REQUEST['page'] . '" />';
			$table->display();
			echo '</form>';
			echo '</div>';
		}
	);
}
