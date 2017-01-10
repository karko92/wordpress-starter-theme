<?php

class EMuseumCore {

	public static function build_post_type () {
		$labels = array(
			'name' => _x('Artworks', 'post type general name'),
			'singular_name' => _x('Artwork', 'post type singular name'),
			'add_new' => _x('Add New', 'Artwork'),
			'add_new_item' => __('Add New Artwork'),
			'edit_item' => __('Edit Artwork'),
			'new_item' => __('New Artwork'),
			'view_item' => __('View Artwork'),
			'search_items' => __('Search Artworks'),
			'not_found' =>  __('No Artworks found'),
			'not_found_in_trash' => __('No Artworks found in Trash'),
			'parent_item_colon' => '',
			'menu_name' => 'Artworks'
			);
		$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'show_in_nav_menus' => false,
			'query_var' => true,
			'rewrite' => array('slug' => 'artwork'),
			'capability_type' => 'post',
			'has_archive' => true,
			'hierarchical' => false,
			'menu_position' => 29,
			'menu_icon' => 'dashicons-location',
			'supports' => array('title', 'page-attributes')
			);

		register_post_type('artwork', $args);
	}

	public static function parseCSV () {
		$file_name = dirname(__FILE__) . '/objects.csv';
		echo $file_name;
		echo "<hr>";
		$csv = new parseCSV($file_name);

		$results = array();
		foreach ($csv->data as $row)
			$results[] = self::create_or_update($row);

		return $results;
	}

	public static function create_or_update ($piece) {
		$piece = (object)$piece;
		$original_title = (string)$piece->Title;
		$piece->Title = EMuseum::clean_title( $piece->Title );

		$query = new WP_Query(array(
			'post_type'  => 'artwork',
			'post_title' => $piece->Title,
			'meta_query' => array(
				array(
					'key'     => 'artwork_object_number',
					'value'   => $piece->ObjectNumber,
					'compare' => '='
					)
				)
			));

		if (count($query->posts) > 0) {
			$post_id = $query->posts[0]->ID;
		} else {
			$post_id = wp_insert_post(array(
				'post_title'    => $piece->Title,
				'post_status'   => 'publish',
				'post_type'   => 'artwork'
				));

			update_post_meta($post_id, 'artwork_object_number', $piece->ObjectNumber);
			update_post_meta($post_id, 'artwork_original_title', $original_title);

			update_post_meta($post_id, 'artwork_emuseum_api_fetched', false);
			update_post_meta($post_id, 'artwork_emuseum_api_failed', false);
			update_post_meta($post_id, 'artwork_emuseum_api_response', false);

			update_post_meta($post_id, 'artwork_portfolio_api_fetched', false);
			update_post_meta($post_id, 'artwork_portfolio_api_failed', false);
			update_post_meta($post_id, 'artwork_portfolio_api_response', false);
		}

		return $post_id;
	}

	public static function verify_table () {
		global $wpdb;

		$count = $wpdb->get_results( "SHOW TABLES LIKE 'wp_emuseum_objects_mapping'" );
		if (count( $count ) < 1)
			$wpdb->query("CREATE TABLE `wp_emuseum_objects_mapping` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `title` varchar(255) DEFAULT NULL, `object_number` varchar(255) DEFAULT NULL, `object_id` varchar(255) DEFAULT NULL, `date` varchar(255) DEFAULT NULL, PRIMARY KEY (`id`), KEY `object_number` (`object_number`), KEY `object_id` (`object_id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
	}

}