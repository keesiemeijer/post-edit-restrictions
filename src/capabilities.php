<?php
/* Filter user capabilities */
add_filter( 'user_has_cap', 'km_per_restrict_editing_published_posts', 10, 3 );


/**
 * Set time restricting capability depending on the plugin's settings.
 * 
 * @param array   $allcaps An array of all the user's capabilities.
 * @param array   $cap     Actual capabilities for meta capability.
 * @param array   $args    Optional parameters passed to has_cap(), typically object ID.
 * @return array           An array of all the user's capabilities.
 */
function km_per_restrict_editing_published_posts( $allcaps, $cap, $args ) {

	$post_caps = array( 'edit_post', 'delete_post' );
	if ( ! in_array( $args[0], $post_caps ) ) {
		return $allcaps;
	}

	$post = get_post( $args[2] );
	if ( ! $post ) {
		return $allcaps;
	}

	$post_status = 'publish';
	if( 'attachment' === $post->post_type ){
		$post_status = 'inherit';
	}
	
	if ( $post_status !== $post->post_status ) {
		return $allcaps;
	}

	$cap_str     = "edit_posts_km_per_restrictions_{$post->post_type}";
	$restriction = isset( $allcaps[ $cap_str ] ) && $allcaps[ $cap_str ];
	if ( ! $restriction ) {
		return $allcaps;
	}

	$settings   = km_per_get_settings( $post->post_type );
	$time       = absint( $settings['time'] );
	$restricted = strtotime( $post->post_date ) < strtotime( "-$time minute" );

	// Disallow editing when time is set to zero or the time limit is up.
	if ( ! $time || $restricted ) {
		return $allcaps[ $cap[0] ] = false;
	}

	return $allcaps;
}
