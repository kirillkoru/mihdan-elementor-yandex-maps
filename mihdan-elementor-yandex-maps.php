<?php
/**
 * Plugin Name: Mihdan: Elementor Yandex Maps
 * Description: Elementor Yandex Maps Widget - Easily add multiple address pins onto the same map with support for different map types (Road Map/Satellite/Hybrid/Terrain) and custom map style. Freely edit info window content of your pins with the standard Elementor text editor. And many more custom map options.
 * Plugin URI:  https://www.kobzarev.com/
 * Version:     1.1.0
 * Author:      Mikhail Kobzarev
 * Author URI:  https://www.kobzarev.com/
 * Text Domain: mihdan-elementor-yandex-maps
 * GitHub Plugin URI: https://github.com/mihdan/mihdan-elementor-yandex-maps
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define( 'EB_YANDEX_MAPS_FILE', __FILE__ );
define( 'EB_YANDEX_MAPS_VERSION', '1.1.0' );

require_once __DIR__ . '/elementor-helper.php';


/**
 * Load Google Map Extended
 *
 * Load the plugin after Elementor (and other plugins) are loaded.
 *
 * @since 1.0.0
 */
function eb_google_map_extended() {
	// Load localization file
	load_plugin_textdomain( 'eb-google-map-extended' );

	// Notice if the Elementor is not active
	if ( ! did_action( 'elementor/loaded' ) ) {
		add_action( 'admin_notices', 'eb_elementor_google_map_fail_load' );
		return;
	}

	// Check required version
	$elementor_version_required = '1.8.0';
	if ( ! version_compare( ELEMENTOR_VERSION, $elementor_version_required, '>=' ) ) {
		add_action( 'admin_notices', 'eb_check_google_map_elementor_load_out_of_date' );
		return;
	}

	// Require the main plugin file
	function add_eb_google_map(){
		require_once __DIR__ . '/widgets/yandex-maps-widget.php';
	}
	add_action('elementor/widgets/widgets_registered','add_eb_google_map');
}

add_action( 'plugins_loaded', 'eb_google_map_extended' );

function eb_elementor_google_map_fail_load() {

	$message = '<p>' . __( 'You do not have Elementor Page Builder on your WordPress. Elementor Google Map Extended require Elementor in order to work.', 'eb-google-map-extended' ) . '</p>';

	echo '<div class="error">' . $message . '</div>';
}

function eb_check_google_map_elementor_load_out_of_date() {
	if ( ! current_user_can( 'update_plugins' ) ) {
		return;
	}

	$file_path = 'elementor/elementor.php';

	$upgrade_link = wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' ) . $file_path, 'upgrade-plugin_' . $file_path );
	$message = '<p>' . __( 'Elementor Google Map Extended may not work or is not compatible because you are using an old version of Elementor.', 'eb-google-map-extended' ) . '</p>';
	$message .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', $upgrade_link, __( 'Update Elementor Now', 'eb-google-map-extended' ) ) . '</p>';

	echo '<div class="error">' . $message . '</div>';
}

/**
 * Get the value of a settings field
 *
 * @param string $option settings field name
 * @param string $section the section name this field belongs to
 * @param string $default default text if it's not found
 *
 * @return mixed
 */
function eb_map_get_option( $option, $section, $default = '' ) {

    $options = get_option( $section );

    if ( isset( $options[$option] ) ) {
        return $options[$option];
    }

    return $default;
}

/**
 * Register and enqueue a custom stylesheet in the Elementor.
 */
add_action('elementor/editor/before_enqueue_scripts', function(){
	wp_enqueue_style( 'eb-google-map-admin', plugins_url( '/assets/css/eb-google-map-admin.css', EB_YANDEX_MAPS_FILE ) );
	//wp_enqueue_script( 'eb-google-maps-api-admin', 'https://maps.googleapis.com/maps/api/js?key=' . eb_map_get_option( 'eb_google_map_api_key', 'eb_map_general_settings' ) . '', ['jquery'], eb_google_map_version, true  );
	wp_enqueue_script( 'eb-google-maps-api-admin', 'https://api-maps.yandex.ru/2.1/?lang=ru_RU&mode=debug', ['jquery'], EB_YANDEX_MAPS_VERSION, true  );
	wp_localize_script( 'eb-google-maps-api-admin', 'EB_WP_URL', array( 'plugin_url' => plugin_dir_url(__FILE__) ));
	wp_enqueue_script( 'eb-google-map-admin', plugins_url( '/assets/js/eb-google-map-admin.js', EB_YANDEX_MAPS_FILE ), ['eb-google-maps-api-admin'], EB_YANDEX_MAPS_VERSION, true );
});

add_action('elementor/frontend/after_enqueue_styles', function(){
	wp_enqueue_style( 'eb-google-map', plugins_url( '/assets/css/eb-google-map.css', EB_YANDEX_MAPS_FILE ) );
});

add_action('elementor/frontend/after_register_scripts', function(){
	//wp_register_script( 'eb-google-maps-api', 'https://maps.googleapis.com/maps/api/js?key=' . eb_map_get_option( 'eb_google_map_api_key', 'eb_map_general_settings' ) . '', [], eb_google_map_version, true  );
	wp_register_script( 'eb-google-maps-api', 'https://api-maps.yandex.ru/2.1/?lang=ru_RU&mode=debug', [], EB_YANDEX_MAPS_VERSION, true  );
	wp_localize_script( 'eb-google-maps-api', 'EB_WP_URL', array( 'plugin_url' => plugin_dir_url(__FILE__) ));
	wp_register_script( 'eb-google-map', plugins_url( '/assets/js/eb-google-map.js', EB_YANDEX_MAPS_FILE ), [ 'eb-google-maps-api' ], EB_YANDEX_MAPS_VERSION, true );
});

// eof;
