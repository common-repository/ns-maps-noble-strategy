<?php
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	global $post;
	global $wpdb;

	$image_src 	= '';
	$image_id 	= get_post_meta( $post->ID, '_image_id', true );
	$image_src 	= wp_get_attachment_url( $image_id );
	$strFile 	= get_post_meta($post->ID, $key = 'icon', true);
	$media_file = get_post_meta($post -> ID, $key = '_wp_attached_file', true);
	if (!empty($media_file)) {
	    $strFile = $media_file;
	}

	/* Imagem Icon */
	$icon = !empty(get_post_meta( $post->ID, 'icon', true )) ? get_post_meta( $post->ID, 'icon', true ) : plugin_dir_url( __DIR__).'css/images/icon_map.png';

	/* Tamanho do Icon */
	$icon_size = !empty(get_post_meta( $post->ID, 'icon_size', true )) ? get_post_meta( $post->ID, 'icon_size', true ) : '32';
	if(($icon_size != '16') || ($icon_size != '32') || ($icon_size != '64')){
		$icon_size = '32';
	}

	//* Zoom do Mapa */
	$zoom = !empty(get_post_meta( $post->ID, 'zoom', true )) ? get_post_meta( $post->ID, 'zoom', true ) : 7;
	if(($zoom < '0') || ($zoom > '18')){
		$zoom = '7';
	}
	
	$map_height = !empty(get_post_meta( $post->ID, 'map_height', true )) ? get_post_meta( $post->ID, 'map_height', true ) : '500';

	$newMarker = plugin_dir_url( __DIR__).'admin/css/images/icon_new_marker.png'; 
?>

<?php if ($post->post_status == 'publish') { ?>
	<script>
		var newMarker = <?php echo "'". $newMarker . "'"; ?>;
	</script>

	<div>
		<a href="https://www.noblestrategy.pt/" target="_blank"><img style="width: 100%;" src="<?php echo plugin_dir_url( __DIR__).'admin/css/images/ns-banner.gif'?>"></a>
	</div>

	<div class="split6">
		<div class="pad15">
			<div class="marker_new">
				<h1 class="title">Address Lookup:</h1>
				<hr>
				<div>
					<input type="text" name="addr[]" class="addr addr_0" data-id="0" value="" id="addr" style="width:100%" />
					<div class="results_0"></div>
				</div>
				<br/>

				<h1 class="title">Description:</h1>
				<hr>
				<div>
					<textarea name="desc[]" class="desc desc_0" id="desc" style="width:100%"></textarea>
				</div>
				<br/>

				<h1 class="title">Coordinates:</h1>
				<hr>
					<div class="split6">
						<div class="padr15">
							<label for="lat">Latitude:</label>
							<input type="number" step="0.0001" name="lat[]" class="lat_0" id="lat" style="width:100%;" value="">
						</div>
					</div>
					<div class="split6">
						<div class="padl15">
							<label for="long">Longitude:</label>
							<input type="number" step="0.0001" name="long[]" class="long_0" id="long" style="width:100%;" value="">
						</div>
					</div>
				<div class="clear"></div>
				<br/>
				<hr>
				<button id="add_marker" <?php if ($post->post_status != 'publish') echo 'disabled'; ?>><span class="dashicons dashicons-plus"></span> Add marker</button> <?php if ($post->post_status != 'publish') echo '<i>(*O mapa precisa de estar publicado)</i>'; ?>
			</div>

		</div>
	</div>
	<div class="split6">
		<div class="pad15">
			<div id="map" style="min-height:400px;max-width:650px;"></div>
		</div>
	</div>
	<div class="clear"></div>
<?php } else { echo '<i>(*O mapa precisa de estar publicado)</i>'; } ?>

	