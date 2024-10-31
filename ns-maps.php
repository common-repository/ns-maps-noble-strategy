<?php 
/*
	Plugin Name: NS Maps (Noble Strategy)
	Plugin URI: https://www.noblestrategy.com
	Description: Mapa para Wordpress desenvolvido por Noble Strategy
	Author: Noble Strategy
	Version: 1.0
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function ns_maps_scripts() {
	wp_register_style( 'leaftletcss', plugin_dir_url( __FILE__ ).'css/leaflet.css' );
	wp_enqueue_style( 'leaftletcss' );
	
	wp_register_style( 'leaftletcsscustom', plugin_dir_url( __FILE__ ).'css/style.css' );
	wp_enqueue_style( 'leaftletcsscustom' );

	wp_enqueue_script( 'leaftletjs', plugin_dir_url( __FILE__ ).'js/leaflet.js', array( 'jquery' ) );
	wp_enqueue_script('ns-maps', plugin_dir_url( __FILE__ ).'js/ns-maps.js', array( 'jquery' ));
}
add_action( 'wp_enqueue_scripts', 'ns_maps_scripts' );
add_action( 'admin_enqueue_scripts', 'ns_maps_scripts' );
add_action( 'admin_init','ns_maps_scripts');



function ns_maps_post_type() {
	$labels = array(
		'name'               => __( 'NS Maps' ),
		'singular_name'      => __( 'NS Map' ),
		'add_new'            => __( 'Add New Map' ),
		'add_new_item'       => __( 'Add New Map' ),
		'edit_item'          => __( 'Edit Map' ),
		'new_item'           => __( 'Add New Map' ),
		'view_item'          => __( 'View Map' ),
		'search_items'       => __( 'Search Map' ),
		'not_found'          => __( 'No maps found' ),
		'not_found_in_trash' => __( 'No maps found in trash' )
	);

	$supports = array(
		'title',
		'editor',
		'thumbnail',
		'comments',
		'revisions',
	);

	$args = array(
		'labels'               => $labels,
		'supports'             => $supports,
		'public'               => true,
		'capability_type'      => 'post',
		'rewrite'              => array( 'slug' => 'ns_maps' ),
		'has_archive'          => true,
		'menu_position'        => 30,
		'menu_icon'            => 'dashicons-location',
		'register_meta_box_cb' => 'ns_maps_add_page',
	);
	register_post_type( 'ns_maps', $args );
}
add_action( 'init', 'ns_maps_post_type' );



function ns_maps_admin_scripts() {
	wp_enqueue_script('media-upload');
	wp_enqueue_script('thickbox');
}



function ns_maps_admin_styles() {
	wp_enqueue_style('thickbox');
}
add_action('admin_print_scripts', 'ns_maps_admin_scripts');
add_action('admin_print_styles', 'ns_maps_admin_styles');



/* Add Meta Box */
function ns_maps_add_page() {
	ns_maps_admin_scripts();
	ns_maps_admin_styles();
	add_meta_box(
		'ns_map_settings',
		'Map Settings',
		'ns_map_settings',
		'ns_maps',
		'side',
		'default'
	);
	add_meta_box(
		'ns_map_add_markers',
		'Map Add Markers',
		'ns_map_add_markers',
		'ns_maps',
		'normal',
		'default'
	);
	add_meta_box(
		'ns_map_markers',
		'Map Markers',
		'ns_map_markers',
		'ns_maps',
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes', 'ns_maps_add_page' );



/* Display Settings Map */
function ns_map_settings() {
    global $post;
    // Use nonce for verification to secure data sending
    wp_nonce_field( basename( __FILE__ ), 'ns_maps_fields' );
    include('admin/settings-maps.php');
}



/* Display Add Markers Map */
function ns_map_add_markers() {
    global $post;
    // Use nonce for verification to secure data sending
    wp_nonce_field( basename( __FILE__ ), 'ns_maps_fields' );
    include('admin/add-markers-maps.php');
}



/* Display Markers Map */
function ns_map_markers() {
    global $post;
    // Use nonce for verification to secure data sending
    wp_nonce_field( basename( __FILE__ ), 'ns_maps_fields' );
    include('admin/markers-maps.php');
}



/* Remove Comments */
add_action( 'init', 'remove_ns_maps_comment' );
function remove_ns_maps_comment() {
	remove_post_type_support( 'ns_maps', 'comments' );
    remove_post_type_support( 'ns_maps', 'editor' );
}



function ns_maps_my_remove_wp_seo_meta_box() {
	remove_meta_box('wpseo_meta', 'ns_maps', 'normal');
}
add_action('add_meta_boxes', 'ns_maps_my_remove_wp_seo_meta_box', 100);



/* Save Post Type */
function save_ns_maps_meta( $post_id, $post ) {
	// Return if the user doesn't have edit permissions.
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return $post_id;
	}
	// Verify this came from the our screen and with proper authorization,
	// because save_post can be triggered at other times.
	if ( ! isset( $_POST['ns_maps_icon']) || ! isset( $_POST['zoom']) || ! wp_verify_nonce( $_POST['ns_maps_fields'], basename(__FILE__) ) ) {
		return $post_id;
	}
	// Now that we're authenticated, time to save the data.
	// This sanitizes the data from the field and saves it into an array $events_meta.
	
	/* Icon */
	$ns_maps_meta['icon'] = sanitize_text_field( $_POST['ns_maps_icon'] );

	/* Tamanho do Icon */
	$selected_icon_size = sanitize_text_field( $_POST['icon_size'] );
	$ns_maps_meta['icon_size'] = substr($selected_icon_size, 0, 2);

	/* Zoom do Mapa */
	$ns_maps_meta['zoom'] = sanitize_text_field( $_POST['zoom'] );
	if ( $ns_maps_meta['zoom'] < '1'  || $ns_maps_meta['zoom'] > '18' ){
		$ns_maps_meta['zoom'] = '7';
	}

	/* Centro do Mapa */
	$ns_maps_meta['addr_center'] 	 = sanitize_text_field( $_POST['addr_center'] );
	$ns_maps_meta['addr_center_lat'] = sanitize_text_field( $_POST['addr_center_lat'] );
	$ns_maps_meta['addr_center_lon'] = sanitize_text_field( $_POST['addr_center_lon'] );
	
	/* Tamanho do Mapa*/
	$ns_maps_meta['map_height'] = sanitize_text_field( $_POST['map_height'] );

	// Cycle through the $events_meta array.
	// Note, in this example we just have one item, but this is helpful if you have multiple.
	foreach ( $ns_maps_meta as $key => $value ) :
		// Don't store custom data twice
		if ( 'revision' === $post->post_type ) {
			return;
		}
		if ( get_post_meta( $post_id, $key, false ) ) {
			// If the custom field already has a value, update it.
			update_post_meta( $post_id, $key, $value );
		} else {
			// If the custom field doesn't have a value, add it.
			add_post_meta( $post_id, $key, $value);
		}
		if ( ! $value ) {
			// Delete the meta key if there's no value
			delete_post_meta( $post_id, $key );
		}
	endforeach;
}
add_action( 'save_post', 'save_ns_maps_meta', 1, 2 );



/* Display shortcode field in table */
add_filter( 'manage_ns_maps_posts_columns', 'set_custom_edit_ns_maps_columns' );
function set_custom_edit_ns_maps_columns($columns) {
    $columns['shortcode'] = __( 'Shortcode', 'Shortcode' );
    return $columns;
}



add_action( 'manage_ns_maps_posts_custom_column' , 'custom_ns_maps_column', 10, 2 );
function custom_ns_maps_column( $column, $post_id ) {
    switch ( $column ) {
    	case 'shortcode' :
        	if ( $post_id )
                echo '[ns_maps id='.$post_id.']';
            else
                _e( 'Unable to get author(s)', 'your_text_domain' );
            break;
    }
}



/* Shortcode */
function ns_maps( $atts ){

	//Mapa FrontOffice
	
	/* Icon */
	$icon = !empty(get_post_meta( $atts['id'], 'icon', true )) ? get_post_meta( $atts['id'], 'icon', true ) : plugin_dir_url( __FILE__).'css/images/icon_map.png';
	
	/* Tamanho do Icon */
	$icon_size = !empty(get_post_meta( $atts['id'], 'icon_size', true )) ? get_post_meta( $atts['id'], 'icon_size', true ) : '32';
	if ( $icon_size != '16' && $icon_size != '32' && $icon_size != '64'){
		echo "<script>console.log('Icon Wrong Size')</script>";
		$icon_size = '32';
	}

	/* Zoom no load do mapa */
	$zoom = !empty(get_post_meta( $atts['id'], 'zoom', true )) ? get_post_meta( $atts['id'], 'zoom', true ) : '7';
	if( $zoom < '0' || $zoom > '18' ){
		$zoom = '7';
	}

	/* Local do load do mapa */
	$addr_center = !empty(get_post_meta( $atts['id'], 'addr_center', true )) ? get_post_meta( $atts['id'], 'addr_center', true ) : 'Portugal';

	/* Coordenadas do centro do mapa */
	$addr_center_lat = !empty(get_post_meta( $atts['id'], 'addr_center_lat', true )) ? get_post_meta( $atts['id'], 'addr_center_lat', true ) : '40.033265';
	$addr_center_lon = !empty(get_post_meta( $atts['id'], 'addr_center_lon', true )) ? get_post_meta( $atts['id'], 'addr_center_lon', true ) : '-7.8896263';

	/* Tamanho do mapa - Largura * Altura */
	$map_height = !empty(get_post_meta( $atts['id'], 'map_height', true )) ? get_post_meta( $atts['id'], 'map_height', true ) : '250';
	if($map_height < '250'){
		$map_height = '250';
	}

	$script = '';

  	global $wpdb;
	$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}posts WHERE post_type = 'ns_maps_marker' and post_status = 'publish' and post_parent = ".$atts['id'], OBJECT );

	//echo $map_height;

	
	echo "<div id='map".esc_html($atts['id'])."' style='min-height: 250px; height:".esc_html($map_height)."px; width: 100%;'></div>";

	$script .= '
	<script type="text/javascript">
	var startlat 	= '. esc_html($addr_center_lat) .';
	var startlon 	= '. esc_html($addr_center_lon) .';
	var startzoom 	= '. esc_html($zoom) .';

	var options = {
		center: [startlat, startlon],
 		zoom: startzoom
	};

	var map = L.map("map'. esc_html($atts['id']) .'", options);

	var nzoom = 12;

	L.tileLayer("http://{s}.tile.osm.org/{z}/{x}/{y}.png", {
		attribution: ""
	}).addTo(map);

	map.attributionControl.setPrefix("<span>&copy Noble Strategy</span>");

	var LeafIcon = L.Icon.extend({
    	options: {
        	iconSize: ['. esc_html($icon_size) .', '. esc_html($icon_size) .']
    	}
	});

	var greenIcon = new LeafIcon({iconUrl: "'.$icon.'"});

	var ns_markers = [';

	foreach ($results as $marker_options) {
		$latitude 	= get_post_meta($marker_options->ID, 'lat', true);
		$longitude 	= get_post_meta($marker_options->ID, 'lon', true);
		$address 	= get_post_meta($marker_options->ID, 'addr', true);
		$desc 		= (get_post_meta($marker_options->ID, 'desc', true) != '' ? get_post_meta($marker_options->ID, 'desc', true) : 'Marker');
		
		$script .= '["'.$address.'", '.$latitude.', '.$longitude.', "'.$desc.'"],';
	}
					
	$script .= '];

	for(var i = 0; i < ns_markers.length; i++) {
		var myMarker = new L.marker([ns_markers[i][1],ns_markers[i][2]], {
			icon: greenIcon
		})
		.bindPopup(ns_markers[i][3])
		.addTo(map);
		var lng_dir = ns_markers[i][1];
		var lat_dir = ns_markers[i][2];
	}
	</script>';

	return $script;
}
add_shortcode( 'ns_maps', 'ns_maps' );



/* Ajax */
add_action('wp_ajax_add_new_marker', 'ns_maps_ajax_add_new_marker');
function ns_maps_ajax_add_new_marker(){

	$post_id 	= sanitize_text_field( $_POST['id'] );
	$lat 		= sanitize_text_field( $_POST['lat'] );
	$lon 		= sanitize_text_field( $_POST['lon'] );
	$addr 		= sanitize_text_field( $_POST['addr'] );
	$desc 		= sanitize_text_field( $_POST['desc'] );

	$marker_id = wp_insert_post(array('post_type' => 'ns_maps_marker', 'post_parent' => $post_id, 'post_status' => 'publish'));

	if ( ($post_id != null) && ($lat != '') && ($lon != '') && ($addr != '') ){
		add_post_meta($marker_id, 'lat', $lat);
		add_post_meta($marker_id, 'lon', $lon);
		add_post_meta($marker_id, 'addr', $addr);
		add_post_meta($marker_id, 'desc', $desc);
		echo 'Success';
		wp_die();
	} else {
		echo 'Error';
		wp_die();
	}
}



add_action('wp_ajax_save_marker', 'ns_maps_ajax_save_marker');
function ns_maps_ajax_save_marker(){

	$post_id 	= sanitize_text_field( $_POST['id'] );
	$lat 		= sanitize_text_field( $_POST['lat'] );
	$lon 		= sanitize_text_field( $_POST['lon'] );
	$addr 		= sanitize_text_field( $_POST['addr'] );
	$desc 		= sanitize_text_field( $_POST['desc'] );

	//$marker_id = wp_insert_post(array('post_type' => 'ns_maps_marker', 'post_parent' => $post_id, 'post_status' => 'publish'));
	update_post_meta($post_id, 'lat', $lat);
	update_post_meta($post_id, 'lon', $lon);
	update_post_meta($post_id, 'addr', $addr);
	update_post_meta($post_id, 'desc', $desc);
    echo 'Atualizado com sucesso!';
    wp_die();
}



add_action('wp_ajax_remove_marker', 'ns_maps_ajax_remove_marker');
function ns_maps_ajax_remove_marker(){

	$post_id = sanitize_text_field( $_POST['id'] );
	wp_delete_post($post_id);
    echo 'Apagado';
    wp_die();
}



/* Functions */
function ns_maps_see_all_markers($post_id) {
	global $wpdb;
	$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}posts WHERE post_type = 'ns_maps_marker' and post_status = 'publish' and post_parent = ".$post_id, OBJECT );
	//var_dump($results);



	if ($results > 0) {
		foreach ($results as $marker) {
			$latitude 		= get_post_meta($marker->ID, 'lat', true);
			$longitude 		= get_post_meta($marker->ID, 'lon', true);
			$address 		= get_post_meta($marker->ID, 'addr', true);
			$description 	= get_post_meta($marker->ID, 'desc', true);
			
			$init_lat 	= $latitude;
			$init_lng 	= $longitude;
			$init_addr 	= $address;

			wp_add_inline_script( 'leaftletJSAdmin', 'var init_lat = '.$init_lat.';');
			wp_add_inline_script( 'leaftletJSAdmin', 'var init_lng = '.$init_lng.';');
			wp_add_inline_script( 'leaftletJSAdmin', 'var init_addr = '.$init_addr.';');
			
			?>
				
			<div class="marker_<?php echo $marker->ID; ?> split6">
				<div class="pad15">
					<div style="background:#eee; padding:15px; border-radius:3px;">
						<h1 class="title">Address Lookup</h1>
						<hr>
						<div>
							<input type="text" name="addr[]" class="addr_bo addr addr_<?php echo $marker->ID; ?>" data-id="<?php echo $marker->ID; ?>" value="<?php echo $address; ?>" size="58"/>
							<div class="results_<?php echo $marker->ID; ?>"></div>
						</div>
						<br>
							
						<h1 class="title">Description:</h1>
						<hr>
						<div>
							<textarea name="desc[]" class="desc_<?php echo $marker->ID; ?>" data-id="<?php echo $marker->ID; ?>" id="desc" style="width:100%"><?php echo $description; ?></textarea>
						</div>
						<br>

						<h1 class="title">Coordinates</h1>
						<hr>
						<div class="split6">
							<div class="padr15">
								<label for="lat">Latitude:</label>
								<input type="text" name="lat[]" class="lat lat_<?php echo $marker->ID; ?>" size=12 value="<?php echo $latitude; ?>">
							</div>
						</div>

						<div class="split6">
							<div class="padl15">
								<label for="long">Longitude:</label>
								<input type="text" name="long[]" class="long long_<?php echo $marker->ID; ?>" size=12 value="<?php echo $longitude; ?>">
							</div>
						</div>
						<div class="clear"></div>
						<br>
						<hr>
						<div class="split6">
							<div class="padr15">
								<button class="save_marker" data-id="<?php echo $marker->ID; ?>"><span class="dashicons dashicons-yes"></span> Save</button>
							</div>
						</div>
						<div class="split6">
							<div class="padl15">
								<button class="remove_marker" data-id="<?php echo $marker->ID; ?>"><span class="dashicons dashicons-no"></span> Remove</button>
							</div>
						</div>
						<div class="clear"></div>
					</div>
				</div>
			</div>
		<? } ?>
		<div class="clear"></div>
		<script>
			var ns_markers = [
				<?php
					foreach ($results as $marker_options) {
						$latitude 		= get_post_meta($marker_options->ID, 'lat', true);
						$longitude 		= get_post_meta($marker_options->ID, 'lon', true);
						$address 		= get_post_meta($marker_options->ID, 'addr', true);
						$description 	= (get_post_meta($marker_options->ID, 'desc', true) != '' ? get_post_meta($marker_options->ID, 'desc', true) : 'Marker #'.$marker_options->ID);
				?>
					['<?=$address?>', '<?=$latitude?>', '<?=$longitude?>', '<?=$description?>', '<?=$marker_options->ID?>'],
					<? } ?>
			];
		</script>
		<?php
	}
}
?>