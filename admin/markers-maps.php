<?php
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	
	global $post;
	global $wpdb;

	/* Style and Script */
	wp_enqueue_script( 'leaftletJSAdmin', plugin_dir_url( __FILE__ ).'js/admin-ns-maps.js', array( 'jquery' ) );
	
	wp_register_style( 'leafletCSSAdmin', plugin_dir_url( __FILE__ ).'css/style.css' );
	wp_enqueue_style( 'leafletCSSAdmin' );
?>

<?php if ($post->post_status == 'publish') { ?>
	<div class="all_markers">
		<?php ns_maps_see_all_markers($post->ID); ?>
	</div>
<?php } else { echo '<i>(*O mapa precisa de estar publicado)</i>'; }
?>

