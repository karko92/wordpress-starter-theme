<?php

require_once(dirname(__FILE__) . '/portfolio-utilities.php');
class Portfolio extends PortfolioUtilities {
	private static $base_url = "http://64.19.11.90:8090/api/v1";
	private static $api_token = "TOKEN-7db7bd02-bab3-4d19-8f60-6bc2c65c775a";
	private static $catalog_id = "05EFB8A1-8473-2833-2397-84D14835FF9B";

	public static function get_catalogs () {
		$url = self::build_url("/catalog/");
		return self::execute($url);
	}

	public static function init () {
		self::build_folder();
	}

	public static function get_galeries ($catalog_id) {
		$url = self::build_url("/catalog/" . $catalog_id . "/galleries/");
		return self::execute($url);
	}

	public static function get_assets ($object_id) {
		$url = self::build_url("/catalog/" . self::$catalog_id . "/asset/");
		$data = array(
			"pageSize" => 200000,
			"startingIndex" => 0,
			"term" => array(
				"operator" => "equalValue",
				"field" => "TMS ID",
				"values" => array($object_id)
			)
		);
		return self::execute($url, "POST", $data);
	}

	public static function get_images ($image) {
		if (!is_array($image))
			$image = array($image);

		foreach ($image as $img) {
			$url = self::build_url("/catalog/" . self::$catalog_id . "/asset/" . $img . "/preview/");

			$final_name = self::upload_dir() . "/$img";
			$file = self::upload_dir() . "/temp";

			$ch = curl_init($url);
			$fp = fopen($file, 'wb');
			curl_setopt($ch, CURLOPT_FILE, $fp);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_exec($ch);
			curl_close($ch);
			fclose($fp);

			$type = mime_content_type($file);
			list($type, $extension) = explode('/', $type);
			$final_name .= ".$extension";

			rename($file, $final_name);
		}
	}

	public static function get_galery_assets ($catalog_id, $gallery_id) {
		$path = '/catalog/' . $catalog_id . '/asset/_original/';
		$url = self::build_url($path);

		$query = self::url_query(array(
			"galleryId" => $gallery_id,
    	"pageSize" => 25
		));

		return self::execute($url . $query);
	}

	public static function test ($catalog_id) {
		$url = self::build_url("/catalog/" . $catalog_id . "/asset/");
		return self::execute($url);
	}

	protected static function build_url ($path) {
		$query_stuff = implode('', array(
			"?session=", self::$api_token,
			"&api_key=", self::$api_token
		));

		return self::$base_url . $path . $query_stuff;
	}

	protected static function url_query ($array) {
		return "&query=" . urlencode(json_encode($array));
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

	protected static function upload_dir () {
		$wp_uploads = wp_upload_dir();
		return $wp_uploads['basedir'] . '/portfolio_images';
	}

	protected static function build_folder () {
		$upload_dir = self::upload_dir();

		if (!file_exists($upload_dir))
			mkdir($upload_dir);
	}

	public static function local_asset ($asset_id) {
		$upload_dir = wp_upload_dir();
		return (object)array(
			"id" => $asset_id,
			"url" => $upload_dir['baseurl'] . "/portfolio_images/$asset_id.jpeg"
		);
	}

	public static function get_api_data ($object_id) {
		$portfolio = Portfolio::get_assets($object_id);

		$portfolio_assets = array_map(function ($asset) {
			return $asset->id;
		}, $portfolio->assets);

		Portfolio::get_images($portfolio_assets);

		$portfolio_meta = array(
			"assets" => array()
		);
		foreach ($portfolio->assets as $asset) {
			$portfolio_meta["assets"][] = $asset->id;
			$portfolio_meta["last_updated"] = $asset->attributes->{"Last Updated"}[0];
			$portfolio_meta["created"] = $asset->attributes->{"Created"}[0];
			$portfolio_meta["tms_artists_name"] = $asset->attributes->{"TMS Artist's Name"}[0];
			$portfolio_meta["tms_collection"] = $asset->attributes->{"TMS Collection"}[0];
			$portfolio_meta["updated_by"] = $asset->attributes->{"Updated By"}[0];
			$portfolio_meta["copyright"] = $asset->attributes->{"Copyright"}[0];
			$portfolio_meta["cataloged"] = $asset->attributes->{"Cataloged"}[0];
			$portfolio_meta["file_size"] = $asset->attributes->{"File Size"}[0];
			$portfolio_meta["file_description"] = $asset->attributes->{"File Description"}[0];
			$portfolio_meta["tms_dimensions"] = $asset->attributes->{"TMS Dimensions"}[0];
			$portfolio_meta["tms_credit_line"] = $asset->attributes->{"TMS Credit Line"}[0];
			$portfolio_meta["thumbnail_size"] = $asset->attributes->{"Thumbnail Size"}[0];
			$portfolio_meta["tms_artists_nationality"] = $asset->attributes->{"TMS Artist's Nationality"}[0];
			$portfolio_meta["tms_category"] = $asset->attributes->{"TMS Category"}[0];
			$portfolio_meta["tms_artists_dates"] = $asset->attributes->{"TMS Artist's Dates"}[0];
			$portfolio_meta["tms_medium"] = $asset->attributes->{"TMS Medium"}[0];
			$portfolio_meta["tms_culture"] = $asset->attributes->{"TMS Culture"}[0];
			$portfolio_meta["height"] = $asset->attributes->{"Height"}[0];
			$portfolio_meta["last_modified"] = $asset->attributes->{"Last Modified"}[0];
			$portfolio_meta["tms_object_dates"] = $asset->attributes->{"TMS Object Dates"}[0];
			$portfolio_meta["keywords"] = $asset->attributes->{"Keywords"}[0];
			$portfolio_meta["tms_object_title"] = $asset->attributes->{"TMS Object Title"}[0];
		}

		return $portfolio_meta;
	}
}

Portfolio::init();