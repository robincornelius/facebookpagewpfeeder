<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/admin
 * @author     Your Name <email@example.com>
 */
class Plugin_Name_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/facebookfeeder-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/facebookfeeder-admin.js', array( 'jquery' ), $this->version, false );

	}
        
        public function register_my_custom_menu_page() {
                //add_submenu_page( 'options-general.php','Facebook Page Feed Options', 'Facebook Page Feed', 'manage_options', 'myplugin/admin/partials/facebookfeeder-admin-display.php', '', plugins_url( 'myplugin/images/icon.png' ), 6 );
                add_options_page('Facebook Page Feed Options', 'Facebook Page Feed Options', 'manage_options', 'FacebookPageFeed', array($this, 'options_do_page'));           
       }
        
        
         public function register_my_settings() {  
            register_setting( 'FacebookPageFeed', 'facebookfeed_pageid'); 
            register_setting( 'FacebookPageFeed', 'facebookfeed_appid'); 
            register_setting( 'FacebookPageFeed', 'facebookfeed_appsecret' ); 
            register_setting( 'FacebookPageFeed', 'facebookfeed_lastfbpost' ); 
            register_setting( 'FacebookPageFeed', 'facebookfeed_postas' ); 
            register_setting( 'FacebookPageFeed', 'facebookfeed_postcategory' );
            register_setting( 'FacebookPageFeed', 'facebookfeed_synclimit' );
            register_setting( 'FacebookPageFeed', 'facebookfeed_nextsyncfrom' ); 
			register_setting( 'FacebookPageFeed', 'facebookfeed_who' ); 
			register_setting( 'FacebookPageFeed', 'facebookfeed_titletype' ); 
			
        }
        
        public function options_do_page() {
            include "partials/facebookfeeder-admin-display.php";
        }
    

}
