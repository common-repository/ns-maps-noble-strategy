<?php
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	global $post;
	global $wpdb;

	$image_src 	= '';
	$script = '';
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
	if ( $icon_size != '16' && $icon_size != '32' && $icon_size != '64'){
		echo "<script>console.log('Icon Wrong Size');</script>";
		$icon_size = '32';
	}

	$script .= 'var new_icon_size = '.$icon_size.';';

	/* Zoom do Mapa */
	$zoom = !empty(get_post_meta( $post->ID, 'zoom', true )) ? get_post_meta( $post->ID, 'zoom', true ) : '7';
	if( $zoom < '1' || $zoom > '18' ){
		$zoom = '7';
	}
	

	$addr_center 		= !empty(get_post_meta( $post->ID, 'addr_center', true )) ? get_post_meta( $post->ID, 'addr_center', true ) : 'Portugal';
	$addr_center_lat 	= !empty(get_post_meta( $post->ID, 'addr_center_lat', true )) ? get_post_meta( $post->ID, 'addr_center_lat', true ) : '40.033265';
	$addr_center_lon 	= !empty(get_post_meta( $post->ID, 'addr_center_lon', true )) ? get_post_meta( $post->ID, 'addr_center_lon', true ) : '-7.8896263';
	

	/* Tamanho do Mapa */
	$map_height = !empty(get_post_meta( $post->ID, 'map_height', true )) ? get_post_meta( $post->ID, 'map_height', true ) : '250';
	if($map_height < '250'){
		$map_height = '250';
	}




	echo "<script>".$script."</script>";
?>
	
<b>Icon</b>
<div>
	<input type="text" name="ns_maps_icon" id="ns_maps_icon" value="<?php echo $icon; ?>" />
    <input id="upload_image_button" type="button" value="Upload">
    <input type = "hidden" name="img_txt_id" id="img_txt_id" value="" />
    <div style="text-align:center;margin-top:20px">
	    <img src="<?php echo $icon; ?>" style="width:40px;height:40px" id="preview_icon"/>
	</div>
</div>
<br>

<b>Icon Size: </b>
<div>
	<select name="icon_size" style="width:100%">
		<option disabled selected value>Selected size: <?php echo $icon_size?> x <?php echo $icon_size?> </option>
		<option value="16">16 x 16</option>
		<option value="32">32 x 32</option>
		<option value="64">64 x 64</option>
	</select>
</div>
<hr>
<br>

<b>Map size</b>
<div>
	<b>Height</b>
	<input type="number" name="map_height" id="map_height" min="250" value="<?php echo $map_height; ?>" /> px<br/>
	<label for="map_height"><i>(*Min: 250px)</i></label>
</div>
<hr>
<br>

<b>Map Center</b>
<div>
	<br>
	<b>Zoom</b>
	<input type="number" name="zoom" id="zoom" min=1 max=18 value="<?php echo $zoom; ?>"><br/>
	<label for="zoom"><i>(*Min: 1 - Max: 18 - Default: 7)</i></label>
</div>
<br>
<b>Address Center</b>
<div>
	<input type="text" name="addr_center" class="addr_center" value="<?php echo $addr_center; ?>" id="addr_center" style="width:100%" />
	<div class="results_center"></div>
	<i>(*Center map: Locality)</i>
	<input type="hidden" name="addr_center_lat" value="<?php echo $addr_center_lat; ?>">
	<input type="hidden" name="addr_center_lon" value="<?php echo $addr_center_lon; ?>">
	<input type="hidden" name="map_id" value="<?php echo $post->ID; ?>">
</div>
<br>
<hr>

	