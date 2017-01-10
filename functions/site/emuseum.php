<?php
ini_set('memory_limit','-1');

require_once(dirname(__FILE__) . '/parse-csv.php');
require_once(dirname(__FILE__) . '/emuseum-helpers.php');

class EMuseum extends EMuseumHelpers{
	private static $base_url = "http://emuseum.museum.nelson-atkins.org:8081";

	public static function init () {
		// post type creation
		add_action( 'init', array('EMuseum', 'build_post_type') );

		// post meta management
		add_action('add_meta_boxes_artwork', array(EMuseum, 'register_meta'), 1);
		add_action( 'save_post', array( $this, 'save_metabox' ), 10, 2 );
	}

	public static function get_objects () {
		$result = json_decode(file_get_contents(dirname(__FILE__) . '/objects.json'));
		// $url = self::build_url("/objects/");

		// echo $url;echo "<hr>";

		// $result = self::execute($url);

		return $result->objects;
	}

	public static function get_object ($object_id) {
		$url = self::build_url("/objects/$object_id/");

		$result = self::execute($url);

		return $result;
	}

	public static function api_fetch_all_objects () {
		$objects = self::get_objects();
		EMuseum::verify_table();
		foreach ($objects as $object)
			EMuseum::store_map($object);
	}

	public static function get_map ($object_number) {
		global $wpdb;
		$query = "SELECT * FROM `wp_emuseum_objects_mapping` WHERE `object_number` = '{$object_number}'";
		$rows = $wpdb->get_results($query);

		if (empty($rows))
			return false;

		return $rows[0];
	}

	public static function store_map ($object) {
		global $wpdb;
		$query = "SELECT * FROM `wp_emuseum_objects_mapping` WHERE `object_id` = {$object->id->value}";

		$test = $wpdb->get_results($query);

		if (empty($test)) {
			$wpdb->insert(
				'wp_emuseum_objects_mapping',
				array(
					"title" => $object->title->value,
					"object_number" => $object->invno->value,
					"object_id" => $object->id->value,
					"date" => $object->displayDate->value
				)
			);
		} else {
			// not sure here
			echo 'update';
		}

	}

	protected static function build_url ($path, $page = false) {
		$url = self::$base_url . $path . "json";
		if ($page !== false)
			$url .= "?page=$page";

		return $url;
	}

	protected static function execute ($url, $method = "GET", $data = array()) {
		$ch = curl_init($url);

		if ($method == "POST") {
			$data_string = json_encode($data);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			    'Content-Type: application/json',
			    'Content-Length: ' . strlen($data_string))
			);
		}

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$result = curl_exec($ch);

		return json_decode( $result );
	}

	// public static function save_metabox( $post_id, $post ) {
	// 	// Check if user has permissions to save data.
	// 	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	// 	// Check if not an autosave.
	// 	if ( wp_is_post_autosave( $post_id ) ) return;

	// 	// Check if not a revision.
	// 	if ( wp_is_post_revision( $post_id ) ) return;

	// 	if (isset($_POST['pretty-address']))
	// 		update_post_meta($post_id, 'pretty_address', $_POST['pretty-address'] ?: '');

	// 	if (isset($_POST['pretty-phone']))
	// 		update_post_meta($post_id, 'pretty_phone', $_POST['pretty-phone'] ?: '');
	// }

	public function register_meta () {
		add_meta_box('artwork-data', 'Artwork Data Fields', array(EMuseum, 'add_meda_fields'), 'artwork', 'advanced', 'high');
	}

	public function add_meda_fields ($post) {
		include(dirname(__FILE__) . '/artwork-meta-fields.php');
	}
}

EMuseum::init();