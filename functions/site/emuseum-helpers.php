<?php

require_once(dirname(__FILE__) . '/emuseum-core.php');

class EMuseumHelpers extends EMuseumCore {

	public static function clean_title ($title) {
		$string = str_replace(' ', '-', wp_strip_all_tags($title));

		$clean = preg_replace('/[^A-Za-z0-9\-]/', '', $string);

		return str_replace('-', ' ', $clean);
	}

	public static function do_cron () {
		$query = new WP_Query(array(
			'post_type'  => 'artwork',
			'posts_per_page' => 10,
			'meta_query' => array(
				array(
					'key'     => 'artwork_emuseum_api_fetched',
					'value'   => false,
					'compare' => '='
					)
				)
			));

		foreach ($query->posts as $piece) {
			EMuseum::run_api_updates($piece);
		}

		if (!empty($query->posts))
			echo "<script>window.location.reload()</script>";

		return $results;
	}

	protected static function get_api_data ($object_id) {
		$emuseum = EMuseum::get_object($object_id);
		return array(
			'primary_media' => $emuseum->primaryMedia->value,
			'depth' => $emuseum->depth->value,
			'credit_line' => $emuseum->creditline->value,
			'display_date' => $emuseum->displayDate->value,
			'description' => $emuseum->description->value,
			'medium' => $emuseum->medium->value,
			'title' => $emuseum->title->value,
			'classification' => $emuseum->classification->value,
			'dimensions' => $emuseum->dimensions->value,
			'height' => $emuseum->height->value
		);
	}

	protected static function run_api_updates ($piece) {
		$piece->meta = TAILOR::format_meta($piece->ID);
		$object = EMuseum::get_map($piece->meta->artwork_object_number);

		if ($object == false) {
			update_post_meta($post_id, 'artwork_emuseum_api_failed', true);
			update_post_meta($piece->ID, 'artwork_emuseum_api_fetched', true);
			update_post_meta($post_id, 'artwork_portfolio_api_failed', true);
			update_post_meta($piece->ID, 'artwork_portfolio_api_fetched', true);
			update_post_meta($post_id, 'artwork_api_error', 'cannot locate object_id in emuseum');
			echo "cannot locate object_id in emuseum";
			return;
		}

		// eMuseum API
		$emuseum_data = EMuseum::get_api_data($object->object_id);

		echo "<pre>";
		print_r($emuseum_data);
		echo "</pre>";

		if (!empty($emuseum_data)) {
			foreach ($emuseum_data as $key => $value)
				update_post_meta($piece->ID, "emuseum_$key", $value);

			update_post_meta($piece->ID, 'artwork_emuseum_api_failed', false);
		} else {
			update_post_meta($piece->ID, 'artwork_emuseum_api_failed', true);
		}

		update_post_meta($piece->ID, 'artwork_emuseum_api_fetched', true);


		// Portfolio API
		$portfolio_data = Portfolio::get_api_data($object->object_id);

		if (!empty($portfolio_data)) {
			foreach ($portfolio_data as $key => $value)
				update_post_meta($piece->ID, "portfolio_$key", $value);
			update_post_meta($piece->ID, 'artwork_portfolio_api_failed', false);
		} else {
			update_post_meta($piece->ID, 'artwork_portfolio_api_failed', true);
		}

		update_post_meta($piece->ID, 'artwork_portfolio_api_fetched', true);
	}
}