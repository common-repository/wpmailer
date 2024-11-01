<?php

/**
 * Fired during plugin activation
 *
 * @link       https://wewp.dev
 * @since      1.0.0
 *
 * @package    Wpmailer
 * @subpackage Wpmailer/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Wpmailer
 * @subpackage Wpmailer/includes
 * @author     wewp.dev <contact@wewp.dev>
 */
class Wpmailer_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		global $wpdb;
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		
		$sql = "CREATE TABLE `wpmailer_plugin_integrations_cf7` ( `id` int(11) NOT NULL AUTO_INCREMENT, `cf7_post_id` int(11) DEFAULT NULL, `user_template_id` int(11) DEFAULT NULL, `admin_template_id` int(11) DEFAULT NULL, `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP, `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP, `deletedAt` datetime DEFAULT NULL, PRIMARY KEY (`id`) ) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;";
        dbDelta( $sql );
		$sql = "CREATE TABLE `wpmailer_templates` ( `id` int(11) NOT NULL AUTO_INCREMENT, `name` varchar(255) DEFAULT NULL, `json` json DEFAULT NULL,`html` longtext DEFAULT NULL, `screenshot_path` varchar(255) DEFAULT NULL, `user_id` int(11) DEFAULT NULL, `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP, `updatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP, `deletedAt` datetime DEFAULT NULL, PRIMARY KEY (`id`) ) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;";
		dbDelta( $sql );
		
	}

	public static function recursive_copy($src,$dst) {
		$dir = opendir($src);
		@mkdir($dst);
		while(( $file = readdir($dir)) ) {
			if (( $file != '.' ) && ( $file != '..' )) {
				if ( is_dir($src . '/' . $file) ) {
					self::recursive_copy($src .'/'. $file, $dst .'/'. $file);
				}
				else {
					copy($src .'/'. $file,$dst .'/'. $file);
				}
			}
		}
		closedir($dir);
	}
}
