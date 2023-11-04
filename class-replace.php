<?php

class Replace {
	private $page_slug = 'word-changer';

	public function init() {
		// Register admin styles and scripts.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin' ) );

		// Add menu item to dashboardAdd menu item to dashboard.
		add_action( 'admin_menu', array( $this, 'add_menu_item' ) );

		// Some methods for ajax.
		add_action( 'wp_ajax_nopriv_search_form', array( $this, 'search_form' ) );
		add_action( 'wp_ajax_search_form', array( $this, 'search_form' ) );

		// Some methods for ajax.
		add_action( 'wp_ajax_nopriv_replace_form', array( $this, 'replace_form' ) );
		add_action( 'wp_ajax_replace_form', array( $this, 'replace_form' ) );
	}

	public function enqueue_admin() {
		$current_screen = get_current_screen();

		if ( $current_screen && 'toplevel_page_' . $this->page_slug === $current_screen->id ) {
			wp_enqueue_style( 'replace-styles', plugin_dir_url( __FILE__ ) . 'assets/styles.css', array(), '1.0' );
			wp_enqueue_script( 'replace-scripts', plugin_dir_url( __FILE__ ) . 'assets/scripts.js', array(), '1.0', true );
			wp_localize_script( 'replace-scripts', 'front_vars', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
		}
	}

	public function add_menu_item() {
		$name = 'Word changer';

		add_menu_page( $name, $name, 'edit_posts', $this->page_slug, array( $this, 'menu_item_template' ) );
	}

	public function menu_item_template() {
		include 'templates/template.php';
	}

	public function search_form() {
		if ( ! empty( $_POST['keyword'] ) && wp_verify_nonce( $_POST['_wpnonce'] ) ) {
			$keyword = sanitize_text_field( $_POST['keyword'] );

			global $wpdb;
			$result = array();

			// Get posts with post_title, contains keyword.
			$result['post_titles'] = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT ID, post_title FROM $wpdb->posts WHERE post_title LIKE '%s' and post_type = 'post'",
					'%' . $wpdb->esc_like( $keyword ) . '%'
				)
			);

			// Get posts with post_content, contains keyword.
			$result['post_contents'] = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT ID, post_content FROM $wpdb->posts WHERE post_content LIKE '%s' and post_type = 'post'",
					'%' . $wpdb->esc_like( $keyword ) . '%'
				)
			);

			if ( is_yoast_activated() ) {
				// Get posts with _yoast_wpseo_metadesc, contains keyword.
				$result['post_metatitle'] = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT post_id, meta_value
						    FROM $wpdb->postmeta
						    WHERE meta_key = '_yoast_wpseo_title'
						    and meta_value LIKE '%s'",
						'%' . $wpdb->esc_like( $keyword ) . '%'
					)
				);

				// Get posts with _yoast_wpseo_metadesc, contains keyword.
				$result['post_metadesc'] = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT post_id, meta_value
						    FROM $wpdb->postmeta
						    WHERE meta_key = '_yoast_wpseo_metadesc'
						    and meta_value LIKE '%s'",
						'%' . $wpdb->esc_like( $keyword ) . '%'
					)
				);
			}

			echo wp_json_encode( $result );

			wp_die();
		} else {
			return null;
		}
	}

	public function replace_form() {
		if ( ! empty( $_POST['new-value'] ) && ! empty( $_POST['old_val'] ) && ! empty( $_POST['change_field'] ) && isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) ) ) {
			// New and old values
			$new_value = sanitize_text_field( $_POST['new-value'] );
			$old_val   = sanitize_text_field( $_POST['old_val'] );


			$change_fields = [
				'title'            => 'post_title',
				'content'          => 'post_content',
				'meta-title'       => '_yoast_wpseo_title',
				'meta-description' => '_yoast_wpseo_metadesc',
			];

			// Field to change
			$change_field = sanitize_text_field( $_POST['change_field'] );

			// If we haven't those types - exit from query
			$database_col = $change_fields[ $change_field ];
			if ( ! $database_col ) {
				return null;
			}

			global $wpdb;
			$query = '';
			if ( $change_field === 'title' || $change_field === 'content' ) {
				// Update words query for title & content
				$query = $wpdb->prepare(
					"UPDATE $wpdb->posts 
					    SET {$database_col} = REPLACE({$database_col}, %s, %s) 
					    WHERE post_type = 'post' AND {$database_col} LIKE %s",
					$old_val,
					$new_value,
					'%' . $wpdb->esc_like( $old_val ) . '%'
				);
			} elseif ( $change_field === 'meta-title' || $change_field === 'meta-description' ) {
				// Update words query for meta-title & meta-description
				$query = $wpdb->prepare(
					"UPDATE {$wpdb->postmeta} AS postmeta
					    INNER JOIN {$wpdb->posts} AS posts ON postmeta.post_id = posts.ID
					    SET postmeta.meta_value = REPLACE(postmeta.meta_value, %s, %s)
					    WHERE posts.post_type = 'post'
					    AND postmeta.meta_key = %s
					    AND postmeta.meta_value LIKE %s",
					$old_val,
					$new_value,
					$database_col,
					'%' . $wpdb->esc_like( $old_val ) . '%'
				);
			}

			$result = $wpdb->query( $query );
			echo $result;


			wp_die();

		} else {
			return null;
		}
	}
}
