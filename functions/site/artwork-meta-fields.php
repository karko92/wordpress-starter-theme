<?php
	$artwork = Artwork::get( $post->ID );
?>

<div class="postbox acf-postbox">
	<div class="inside acf-fields -top">
		<div class="acf-field acf-field-text">
			<div class="acf-label">
				<label for="pretty-location-name">Description</label>
				<p class="description"><strong>Default: </strong> <?php echo $artwork->meta->emuseum_description ?></p>
			</div>
			<div class="acf-input">
				<div class="acf-input-wrap">
					<textarea rows="4" name="description"><?php echo $artwork->description(); ?></textarea>
				</div>
			</div>
		</div>

	</div>

	<div class="inside acf-fields -top">
		<div class="acf-field acf-field-asset">
			<?php foreach ($artwork->assets() as $asset) : ?>
				<img style="max-width: 100%" src="<?php echo $asset->url ?>">
			<?php endforeach; ?>
			
		</div>
	</div>
</div>

<pre>
	<?php print_r($artwork); ?>
</pre>