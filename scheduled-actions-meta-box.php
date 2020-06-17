<?php
/**
 * Plugin Name: Scheduled Actions Meta Box
 * Plugin URI: https://shopplugins.com
 * Description: Add a meta box to Subscription post types to view upcoming scheduled actions.
 * Version: 0.0.1
 * Author: Shop Plugins
 * Author URI: https://shopplugins.com
 * Text Domain: scheduled-actions-meta-box
 */

namespace ShopPlugins\ScheduledActionsMetaBox;

if ( is_admin() ) {
	add_action( 'add_meta_boxes', __NAMESPACE__ . '\add_meta_boxes', 30 );
}

/**
 * Add metabox to shop_subscription pages.
 *
 * @hook `add_meta_boxes`
 */
function add_meta_boxes() {
	global $current_screen, $post_ID;

	add_meta_box( 'subscription_scheduled_actions', __( 'Scheduled Actions', 'scheduled-actions-meta-box' ), __NAMESPACE__ . '\display_meta_box', 'shop_subscription', 'normal', 'low' );
}

/**
 * Add scheduled actions meta box to Edit Subscription page.
 */
function display_meta_box() {
	global $post;

	if ( ! class_exists( 'ActionScheduler_Store' ) ) {
		return;
	}

	$scheduled_actions = as_get_scheduled_actions(
		array(
			'args'     => array( 'subscription_id' => absint( $post->ID ) ),
			'status'   => \ActionScheduler_Store::STATUS_PENDING,
			'per_page' => -1,
			'order'    => 'ASC',
		)
	);

	if ( ! empty( $scheduled_actions ) ) {
		echo '<ol>';
		foreach ( $scheduled_actions as $action_id => $action ) {

			$schedule = $action->get_schedule();

			if ( ! $schedule->next() ) {
				continue;
			}

			$next_scheduled_date_string = $schedule->next()->format( 'Y-m-d H:i:s' ) . ' GMT';
			$action_display_string      = sprintf( '%s - %s - %s', $next_scheduled_date_string, $action->get_hook(), wp_json_encode( $action->get_args() ) );

			printf( '<li class="default_sa">%s</li>', esc_html( $action_display_string ) );
		}
		echo '</ol>';
	} else {
		echo 'There are currently no pending scheduled actions for this subscription';
	}
}