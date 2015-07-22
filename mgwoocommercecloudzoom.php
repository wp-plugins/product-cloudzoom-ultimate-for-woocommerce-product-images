<?php
/* 
Plugin Name: Ultimate WooCommerce CloudZoom for Product Images
Plugin URI: http://magniumthemes.com/
Description: Add CloudZoom feature to your product images on product page.
Version: 1.0
Author: MagniumThemes
Author URI: http://magniumthemes.com/
Copyright MagniumThemes.com. All rights reserved.
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

@session_start();

class MGWCZ {

	public function __construct() {
		register_activation_hook( __FILE__, array( $this, 'ob_install' ) );
		register_deactivation_hook( __FILE__, array( $this, 'ob_uninstall' ) );

		/**
		 * add action of plugin
		 */

		add_action( 'admin_init', array( $this, 'obScriptInit' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'obScriptInitFrontend' ) );
		add_action( 'wp_footer', array( $this, 'execute_cloudzoom' ), 30);

		/*Setting*/
		add_action( 'plugins_loaded', array( $this, 'init_mgwoocommercecloudzoom' ) );

		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
		
		add_filter( 'single_product_small_thumbnail_size', array( $this, 'change_catalog_thumbnail'), 10, 2 );

		add_action( 'admin_notices', array( $this, 'show_admin_notice') );
	}

	public function show_admin_notice() {
    ?>
    <div class="uwcz-message error" style="display:none;">
        <p><?php _e( '<strong>You are using FREE Version of Ultimate WooCommerce CloudZoom plugin without this additional features:</strong>', 'mgwoocommercecloudzoom' ); ?></p>
        <ul>	
        	<li>- Plugin work with ANY theme that support WooCommerce even with changed default HTML layouts (Custom Themes Support)</li>
        	<li>- Variable Products support (with different images for variations)</li>
        	<li>- Admin Panel settings area to manage plugin settings</li>
        	<li>- Change CloudZoom position (left, right, top, bottom, inside)</li>
        	<li>- Change CloudZoom smoothing animation</li>
        	<li>- Change CloudZoom options like tint, opacity, margins and more</li>
        	<li>- Detailed Documentation guide</li>
        	<li>- Free Plugin updates and dedicated support</li>
        </ul>
    	<a style="margin:10px 0; display:block;" href="//www.bluehost.com/track/magniumthemes/uwcz" target="_blank">
        <img border="0" src="//bluehost-cdn.com/media/partner/images/magniumthemes/760x80/bh-ppc-banners-dynamic-760x80.png">
        <a class="button-primary" style="margin-bottom: 10px;" href="http://codecanyon.net/item/ultimate-woocommerce-cloudzoom-for-product-images/12148208/?ref=dedalx" target="_blank">Update to PRO version to get premium features</a> <a id="uwcz-dismiss-notice" class="button-secondary" style="margin-bottom: 10px;" href="javascript:void(0);">Hide this message</a>
        </a>
    </div>

                    
	<?php
	}

	/**
	 * This is an extremely useful function if you need to execute any actions when your plugin is activated.
	 */
	function ob_install() {
		global $wp_version;
		If ( version_compare( $wp_version, "2.9", "<" ) ) {
			deactivate_plugins( basename( __FILE__ ) ); // Deactivate our plugin
			wp_die( "This plugin requires WordPress version 2.9 or higher." );
		}

	}

	/**
	 * This function is called when deactive.
	 */
	function ob_uninstall() {
		//do something
		delete_option('mgwcz_options');
	}

	/**
	 * Function set up include javascript, css.
	 */
	function obScriptInit() {
		wp_enqueue_script( 'mgwcz-script-admin', plugin_dir_url( '' ) . basename( dirname( __FILE__ ) ) . '/js/mgwoocommercecloudzoom-admin.js', array(), false, true );
		wp_enqueue_style( 'mgwcz-style-admin', plugin_dir_url( '' ) . basename( dirname( __FILE__ ) ) . '/css/mgwoocommercecloudzoom-admin.css' );
	}

	function obScriptInitFrontend() {
		if(is_product()) {
			wp_enqueue_script( 'mgwcz-script-frontend', plugin_dir_url( '' ) . basename( dirname( __FILE__ ) ) . '/js/mgwoocommercecloudzoom.js', array(), false, true );
			wp_enqueue_style( 'mgwcz-style-frontend', plugin_dir_url( '' ) . basename( dirname( __FILE__ ) ) . '/css/mgwoocommercecloudzoom.css' );
		}
	}

	/**
	 * This function is run when go to product page
	 */
	function execute_cloudzoom( $post_ID ) {

		if(is_product()) {

			$default_options = $this->plugin_cloudzoom_defaults();
        	$current_options = $default_options;

	        $mouseEvent = ($current_options['mouseEvent']=="")?$default_options['mouseEvent']:$current_options['mouseEvent'];
			$thumbnailsContainer = ($current_options['thumbnailsContainer']=="")?$default_options['thumbnailsContainer']:$current_options['thumbnailsContainer'];
			$productImages = ($current_options['productImages']=="")?$default_options['productImages']:$current_options['productImages'];

			?>
			<style>
			<?php echo $thumbnailsContainer;?> img {
				width: <?php echo get_option('woocommerce_thumbnail_image_width'); ?>px;
			}
			</style>
			<script type="text/javascript">
	        jQuery(document).ready(function($){
	            $('a.zoom').unbind('click.fb');
	            $thumbnailsContainer = $('<?php echo $thumbnailsContainer;?>');
	            $thumbnails = $('a', $thumbnailsContainer);

	            $productImages = $('<?php echo $productImages;?>');
	            addCloudZoom = function(el){

	                el.addClass('cloud-zoom').CloudZoom();

	            }

	            if($thumbnails.length){
	            	<?php if($mouseEvent == 'click') {
	            		echo '$thumbnails.unbind(\'click\');';
	            	}
	            	?>
	      			
	                $thumbnails.bind('<?php echo $mouseEvent; ?>',function(){
	                    $image = $(this).clone(false);
	                    $image.insertAfter($productImages);
	                    $productImages.remove();
	                    $productImages = $image;
	                    $('.mousetrap').remove();
	                    addCloudZoom($productImages);

	                    return false;

	                })

	            }
	            addCloudZoom($productImages);

	        });
	        </script>
			<?php

		}		

	}

	/**
	 * Init when plugin load
	 */
	function init_mgwoocommercecloudzoom() {
		load_plugin_textdomain( 'mgwoocommercecloudzoom' );
		$this->load_plugin_textdomain();
		
	}

	/* Load Language */
	function replace_mgwoocommercecloudzoom_default_language_files() {

		$locale = apply_filters( 'plugin_locale', get_locale(), 'mgwoocommercecloudzoom' );

		return plugins_url( "languages/mgwoocommercecloudzoom-$locale.mo", __FILE__ ) ;

	}

	/**
	 * Function load language
	 */
	public function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'mgwoocommercecloudzoom' );

		// Admin Locale
		if ( is_admin() ) {
			load_textdomain( 'mgwoocommercecloudzoom', plugins_url( "languages/mgwoocommercecloudzoom-$locale.mo", __FILE__ ));
		}

		// Global + Frontend Locale
		load_textdomain( 'mgwoocommercecloudzoom', plugins_url( "languages/mgwoocommercecloudzoom-$locale.mo", __FILE__ ) );
		load_plugin_textdomain( 'mgwoocommercecloudzoom', false, plugins_url( "languages/", __FILE__ ) );
	}

	public function plugin_row_meta( $links, $file ) {
		if ( $file == plugin_basename( __FILE__ ) ) {
			$row_meta = array(
				'getpro'	=>	'<a href="http://codecanyon.net/item/ultimate-woocommerce-cloudzoom-for-product-images/12148208/?ref=dedalx" target="_blank" style="color: blue;font-weight:bold;">' . __( 'Get PRO version', 'mgwoocommercecloudzoom' ) . '</a>',
				'about'	=>	'<a href="http://magniumthemes.com/" target="_blank" style="color: red;font-weight:bold;">' . __( 'Premium WordPress themes', 'mgwoocommercecloudzoom' ) . '</a>',
				
			);

			return array_merge( $links, $row_meta );
		}

		return (array) $links;
	}

	/* Default JS script configuration */
	function plugin_cloudzoom_defaults(){

	    return array(
	        "thumbnailsContainer" => ".product .thumbnails",
	        "mouseEvent" => "hover",
	        "productImages" => ".product .images > a",
	    );

	}

	/* Change thumb size for correct zoom work */
	function change_catalog_thumbnail(){
	    $return = 'shop_single';
	    return $return;
	}

}

$mgwoocommercecloudzoom = new MGWCZ();
?>