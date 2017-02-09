<?php

/**
 * Returns all public post types.
 *
 * @since 1.0.0
 * 
 * @return array Array with post types.
 */
function km_per_get_post_types() {
	$post_types = array();
	$post_types_obj = get_post_types( array( 'public' => true ), 'objects', 'and' );
	foreach ( (array) $post_types_obj as $key => $value ) {
		$post_types[$key] = esc_attr( $value->labels->menu_name );
	}
	return $post_types;
}

/**
 * Returns all user roles (filtered by capability).
 *
 * @since  1.0.0
 *
 * @param string $filter Capability to filter by.
 * @return array Array with roles.
 */
function km_per_get_roles( $filter = '' ) {
	$cap_key   = 'capabilities';
	$cap_roles = array();

	$roles = array_reverse( get_editable_roles() );
	foreach ( $roles as $key => $value ) {
		$has_cap = $filter ? false : true;
		if ( $filter ) {
			$has_cap = isset( $value[ $cap_key ][ $filter ] ) && $value[ $cap_key ][ $filter ];
		}

		if ( $has_cap  ) {
			$name = translate_user_role( $value['name'] );
			$cap_roles[ $key ] = $name;
		}
	}

	return $cap_roles;
}

/**
 * Adds and romoves capabilities depending on the settings.
 *
 * @since 1.0.0
 * 
 * @param  string $post_type Post type.
 * @param  array  $args      Plugin settings for that post type
 */
function km_per_update_capabilities( $post_type, $args ) {
	$roles      = km_per_get_roles();
	$args_roles = array_keys( $args['roles'] );
	$cap        = "edit_posts_km_per_restrictions_{$post_type}";

	foreach ( array_keys( (array)$roles ) as $role ) {
		$role_object = get_role( $role );
		if ( ! $role_object ) {
			continue;
		}

		if ( in_array( $role, $args_roles ) ) {
			$role_object->add_cap( $cap );
		} else {
			$role_object->remove_cap( $cap );
		}
	}
}

/**
 * Removes the post type capabilities set by this plugin.
 *
 * @since 1.0.0
 * 
 * @param  [type] $post_type Post type.
 */
function km_per_remove_capabilities( $post_type ) {
	km_per_update_capabilities( $post_type, array( 'roles' => array() ) );
}

/**
 * Returns and updates settings for a post type.
 *
 * @since 1.0.0
 * 
 * @param  string        $post_type Post type.
 * @param  array|boolean $update    Array with settings to update or false. 
 * @return array         Settings.
 */
function km_per_get_settings( $post_type, $update = false ) {

	if ( ! $post_type ) {
		return array();
	}

	$option_name = "km_per_post_edit_restrictions_{$post_type}";
	$defaults    = array(
		'roles'  => array(),
		'time'   => 30,
	);

	$old_settings = get_option( $option_name );
	if ( empty( $old_settings ) || ! is_array( $old_settings ) ) {
		$old_settings = $defaults;
	}

	// Remove non default values.
	$old_settings = array_intersect_key( $old_settings, $defaults );

	// Make sure default values exist.
	$old_settings = array_merge( $defaults, $old_settings );

	if ( $update && is_array( $update ) ) {
		// Remove non default values.
		$settings = array_intersect_key( $update, $defaults );

		// Make sure default values exist.
		$settings = array_merge( $defaults, $settings );

		// Update capebilities for this post type
		km_per_update_capabilities( $post_type, $settings );
	} else {
		$settings = $old_settings;
	}

	$time = absint( $settings['time'] );

	// Update option if it's not the same as the current settings
	if ( $old_settings != $settings ) {
		update_option( $option_name, $settings );
	}

	return $settings;
}
