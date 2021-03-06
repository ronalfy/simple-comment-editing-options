<?php
/**
 * Create table for SCEOptions.
 *
 * @package SCEOptions
 */

namespace SCEOptions\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct access.' );
}

/**
 * Class for creating the options table.
 */
class Table {

	/**
	 * Create comments table
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	public static function create_table() {
		global $wpdb;
		$tablename = $wpdb->base_prefix . 'sce_comments';

		$version = get_site_option( 'sce_table_version', '0' );
		if ( version_compare( $version, SCE_OPTIONS_TABLE_VERSION ) < 0 ) {
			$charset_collate = '';
			if ( ! empty( $wpdb->charset ) ) {
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			}
			if ( ! empty( $wpdb->collate ) ) {
				$charset_collate .= " COLLATE $wpdb->collate";
			}

			$sql = "CREATE TABLE {$tablename} (
							id BIGINT(20) NOT NULL AUTO_INCREMENT,
							blog_id BIGINT(20) NOT NULL DEFAULT 0,
							comment_id BIGINT(20) NOT NULL DEFAULT 0,
							comment_content text NOT NULL,
							date DATETIME NOT NULL,
							PRIMARY KEY  (id) 
							) {$charset_collate};";
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );

			update_site_option( 'sce_table_version', SCE_OPTIONS_TABLE_VERSION );
		}
	}

	/**
	 * Drop comments table
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	public static function drop() {
		global $wpdb;
		$tablename = $wpdb->base_prefix . 'sce_comments';
		$sql       = "drop table if exists $tablename";
		$wpdb->query( $sql ); // phpcs:ignore
		delete_site_option( 'sce_table_version' );
	}
}
