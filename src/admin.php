<?php
class Post_Edit_Restrictions_Admin_Page {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}

	/**
	 * Adds a settings page for for all post types.
	 *
	 * @since 1.0
	 */
	public function admin_menu() {

		// Special post type pages.
		// Cannot use '?post_type=' for these post types
		$admin_pages = array(
			'post' => 'edit.php',
			'attachment' => 'upload.php'
		);

		foreach ( km_per_get_post_types() as $post_type => $label ) {

			$page = urlencode( $post_type );
			$url  = 'edit.php?post_type=' . $page;
			if ( isset( $admin_pages[ $post_type] ) ) {
				$url = $admin_pages[ $post_type];
			}

			$hook = add_submenu_page(
				$url,
				__( 'Edit Restrictions', 'post-edit-restrictions' ),
				__( 'Edit Restrictions', 'post-edit-restrictions' ),
				'manage_options',
				"{$page}-edit-restrictions",
				array( $this, 'admin_page' )
			);
		}
	}

	/**
	 * Displays the admin settings page.
	 *
	 * @since 1.0
	 */
	public function admin_page() {
		$current_user = wp_get_current_user();
		$post_type    = $this->get_current_post_type();

		$request = false;
		if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
			check_admin_referer( "post_edit_restrictions_{$post_type}" );
			$request = stripslashes_deep( $_POST );
		}

		$settings   = km_per_get_settings( $post_type, $request );
		$roles      = km_per_get_roles( 'edit_posts' );
		$time       = absint( (int) $settings['time'] );

		// Start admin page output
		echo '<div class="wrap">';
		echo '<h1>' . __( 'Ristrict Editing', 'post-edit-restrictions' ) . '</h1>';
		echo '<p>' . __( 'Restrict users from editing published posts after a time limit', 'post-edit-restrictions' ) . '</p>';
		if ( $roles ) {
			include 'admin-form.php';
		} else {
			echo '<p><strong>' . __( 'Error: Could not find any user roles', 'post-edit-restrictions' ) . '</strong></p>';
		}
		echo '</div>';
	}

	/**
	 * Returns the post type for the current admin page.
	 *
	 * @since 1.0
	 *
	 * @return string|bool Post type or false
	 */
	private function get_current_post_type() {
		$screen      = get_current_screen();
		$parent_base = isset( $screen->parent_base ) ? trim( $screen->parent_base ) : '';

		if ( ! in_array( $parent_base, array( 'upload', 'edit' ) ) ) {
			return false;
		}

		$post_type = isset( $screen->post_type ) ? trim( $screen->post_type ) : '';
		if ( $post_type ) {
			return $post_type;
		} else {
			return ( 'edit' === $parent_base ) ? 'post' : 'attachment';
		}

		return false;
	}
}

new Post_Edit_Restrictions_Admin_Page();
