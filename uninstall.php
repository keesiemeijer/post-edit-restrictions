<?php

// Uninstall script.

if ( ! defined( 'ABSPATH' ) && ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit ();
}

global $wpdb;

if ( is_multisite() ) {
	global $wpdb;
	$blogs = $wpdb->get_results( "SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A );
	if ( $blogs ) {
		foreach ( (array) $blogs as $blog ) {
			switch_to_blog( $blog['blog_id'] );
			km_per_delete_restriction_options();
			km_per_remove_restriction_capabilities();
		}
		restore_current_blog();
	}
} else {
	km_per_delete_restriction_options();
	km_per_remove_restriction_capabilities();
}


function km_per_remove_restriction_capabilities() {
	if ( function_exists( 'get_editable_roles' ) ) {
		$roles = get_editable_roles();
		foreach ( $roles as $role => $value ) {
			$role_object = get_role( $role );

			if ( ! isset( $role_object->capabilities ) ) {
				continue;
			}

			foreach ( array_keys( $role_object->capabilities ) as $cap ) {
				// Removes capabilities that start with 'edit_posts_km_per_restrictions_'.
				if ( 0 === strpos( $cap, "edit_posts_km_per_restrictions_" ) ) {
					$role_object->remove_cap( $cap );
				}
			}
		}
	}
}

function km_per_delete_restriction_options() {
	global $wpdb;
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'km_per_post_edit_restrictions_%'" );
}
