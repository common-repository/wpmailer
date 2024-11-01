<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://wewp.dev
 * @since      1.0.0
 *
 * @package    Wpmailer
 * @subpackage Wpmailer/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wpmailer
 * @subpackage Wpmailer/admin
 * @author     wewp.dev <contact@wewp.dev>
 */
class Wpmailer_Admin
{

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
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wpmailer_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wpmailer_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/wpmailer-admin.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wpmailer_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wpmailer_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/wpmailer-admin.js', array('jquery'), $this->version, false);
	}

	public function setup_admin_menu()
	{
		
		add_menu_page('Main page', 'WPMailer', 'read', 'wpmailer', array($this, 'render_admin_main_page'), plugins_url('../assets/wpmailer-icon.png', __FILE__));
	}

	public function render_admin_main_page()
	{
?>
		<style>
			iframe {
				border: none;
				height: calc(100vh - 32px);
				width: calc(100vw - 160px);
				position: fixed;
				right: 0;
				left: 160px;
				top: 32px;
				bottom: 0;
				z-index: 100000;
			}
		</style>
		<iframe src='/wp-content/plugins/wpmailer/admin/build/index.html' width="100vh" height='100vh' style="border:none" />
<?php
	}
}
