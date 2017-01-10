<?php
	// $objects = EMuseum::api_fetch_all_objects();
	$objects = EMuseum::do_cron();
	// $objects = EMuseum::parseCSV();
	echo "<pre>";
	print_r($objects);
	echo "</pre>";
	exit;die;
?>