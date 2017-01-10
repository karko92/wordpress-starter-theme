<?php

	class Artwork {
		public static function get ($id) {
			return new Artwork($id);
		}
		public static function update_from_post () {
		}

		public function __construct ($id) {
			$post = get_post($id);
			foreach (get_object_vars($post) as $key => $val)
				$this->{$key} = $val;

			$this->meta = TAILOR::format_meta($id);
		}

		public function assets () {
			return array_map(array(Portfolio, 'local_asset'), unserialize($this->meta->portfolio_assets));
		}

		public function description () {
			if (isset($this->meta->description_override))
				return $this->meta->description_override;
			return $this->meta->emuseum_description;
		}


	}