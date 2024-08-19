<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
 * Create More Tables
 */
if ( ! function_exists( 'trav_create_extra_tables' ) ) {
	function trav_create_extra_tables() {
		global $wpdb;

		$installed_db_ver = get_option( "trav_db_version" );
		if ( TRAV_DB_VERSION != $installed_db_ver ) {
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

			$sql = "CREATE TABLE " . TRAV_ACCOMMODATION_VACANCIES_TABLE . " (
						id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
						date_from date DEFAULT '0000-00-00',
						date_to date DEFAULT '9999-12-31',
						accommodation_id bigint(20) unsigned DEFAULT NULL,
						room_type_id bigint(20) unsigned DEFAULT NULL,
						rooms tinyint(11) unsigned DEFAULT NULL,
						price_per_room decimal(16,2) DEFAULT '0.00',
						price_per_person decimal(16,2) DEFAULT '0.00',
						child_price varchar(1000) DEFAULT NULL,
						other text,
						PRIMARY KEY  (id)
					) DEFAULT CHARSET=utf8;";
			dbDelta($sql);

			$sql = "CREATE TABLE " . TRAV_ACCOMMODATION_BOOKINGS_TABLE . " (
						id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
						first_name varchar(255) NOT NULL,
						last_name varchar(255) NOT NULL,
						email varchar(255) NOT NULL,
						country_code varchar(50) DEFAULT NULL,
						phone varchar(255) DEFAULT NULL,
						address varchar(255) DEFAULT NULL,
						city varchar(255) DEFAULT NULL,
						zip varchar(255) DEFAULT NULL,
						country varchar(255) DEFAULT NULL,
						special_requirements text,
						accommodation_id bigint(20) unsigned DEFAULT NULL,
						room_type_id bigint(20) unsigned DEFAULT NULL,
						rooms tinyint(1) unsigned DEFAULT '0',
						adults tinyint(1) unsigned DEFAULT '0',
						kids tinyint(1) unsigned DEFAULT '0',
						child_ages text,
						room_price decimal(16,2) DEFAULT '0.00',
						tax decimal(16,2) DEFAULT '0.00',
						discount_rate decimal(16,2) DEFAULT '0.00',
						total_price decimal(16,2) DEFAULT '0.00',
						currency_code varchar(8) DEFAULT '',
						exchange_rate decimal(16,8) DEFAULT '1',
						deposit_price decimal(16,2) DEFAULT '0.00',
						deposit_paid tinyint(1) DEFAULT '0',
						date_from date DEFAULT NULL,
						date_to date DEFAULT NULL,
						user_id bigint(20) unsigned DEFAULT NULL,
						created datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
						pin_code int(5) DEFAULT NULL,
						booking_no int(20) DEFAULT NULL,
						status tinyint(1) DEFAULT '1',
						updated datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
						mail_sent tinyint(1) DEFAULT '0',
						other text,
						woo_order_id bigint(20) unsigned DEFAULT NULL,
						PRIMARY KEY  (id)
					) DEFAULT CHARSET=utf8;";
			dbDelta($sql);

			$sql = "CREATE TABLE " . TRAV_TOUR_SCHEDULES_TABLE . " (
						id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
						tour_id bigint(20) unsigned DEFAULT NULL,
						st_id tinyint(1) DEFAULT '0',
						tour_date date DEFAULT '0000-00-00',
						duration varchar(255) DEFAULT '',
						max_people tinyint(1) unsigned DEFAULT 0,
						price decimal(16,2) DEFAULT '0.00',
						child_price decimal(16,2) DEFAULT NULL,
						is_daily tinyint(1) DEFAULT '0',
						per_person_yn tinyint(1) DEFAULT '0',
						date_to date DEFAULT '9999-12-31',
						other text,
						PRIMARY KEY  (id)
					);";
			dbDelta($sql);

			$sql = "CREATE TABLE " . TRAV_TOUR_BOOKINGS_TABLE . " (
						id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
						tour_id bigint(20) unsigned DEFAULT NULL,
						st_id tinyint(1) DEFAULT '0',
						tour_date date DEFAULT '0000-00-00',
						first_name varchar(255) NOT NULL,
						last_name varchar(255) NOT NULL,
						email varchar(255) NOT NULL,
						country_code varchar(50) DEFAULT NULL,
						phone varchar(255) DEFAULT NULL,
						address varchar(255) DEFAULT NULL,
						city varchar(255) DEFAULT NULL,
						zip varchar(255) DEFAULT NULL,
						country varchar(255) DEFAULT NULL,
						special_requirements text,
						adults tinyint(1) unsigned DEFAULT '0',
						kids tinyint(1) unsigned DEFAULT '0',
						discount_rate decimal(16,2) DEFAULT '0.00',
						total_price decimal(16,2) DEFAULT '0.00',
						currency_code varchar(8) DEFAULT '',
						exchange_rate decimal(16,8) DEFAULT '1',
						deposit_price decimal(16,2) DEFAULT '0.00',
						deposit_paid tinyint(1) DEFAULT '0',
						user_id bigint(20) unsigned DEFAULT NULL,
						pin_code int(5) DEFAULT NULL,
						booking_no int(20) DEFAULT NULL,
						status tinyint(1) DEFAULT '1',
						created datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
						updated datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
						mail_sent tinyint(1) DEFAULT '0',
						other text,
						woo_order_id bigint(20) unsigned DEFAULT NULL,
						PRIMARY KEY  (id)
					) DEFAULT CHARSET=utf8;";
			dbDelta($sql);

			$sql = "CREATE TABLE " . $wpdb->prefix . "trav_reviews (
						id INT(11) NOT NULL AUTO_INCREMENT,
						date DATETIME NOT NULL,
						reviewer_name VARCHAR(150) DEFAULT NULL,
						reviewer_email VARCHAR(150) DEFAULT NULL,
						reviewer_ip VARCHAR(15) DEFAULT NULL,
						review_title VARCHAR(150) DEFAULT NULL,
						review_text TEXT,
						review_rating decimal(2,1) DEFAULT '0',
						review_rating_detail VARCHAR(150) DEFAULT '0',
						post_id INT(11) NOT NULL DEFAULT '0',
						status TINYINT(1) DEFAULT '0',
						trip_type tinyint(1) DEFAULT '0',
						other text,
						booking_no int(9) DEFAULT NULL,
						pin_code int(5) DEFAULT NULL,
						user_id bigint(20) unsigned NULL,
						PRIMARY KEY  (id)
					) DEFAULT CHARSET=utf8;";
			dbDelta($sql);

			$sql = "CREATE TABLE " . $wpdb->prefix . "trav_currencies (
						id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
						currency_code varchar(10) NOT NULL,
						currency_label varchar(255) NOT NULL,
						currency_symbol varchar(10) DEFAULT NULL,
						exchange_rate decimal(16,8) DEFAULT '1',
						other text,
						PRIMARY KEY  (id)
					) DEFAULT CHARSET=utf8;";
			dbDelta($sql);

			$sql = "CREATE TABLE " . TRAV_CAR_BOOKINGS_TABLE . " (
						id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
						first_name varchar(255) NOT NULL,
						last_name varchar(255) NOT NULL,
						email varchar(255) NOT NULL,
						country_code varchar(5) DEFAULT NULL,
						phone varchar(255) DEFAULT NULL,
						address varchar(255) DEFAULT NULL,
						city varchar(255) DEFAULT NULL,
						zip varchar(255) DEFAULT NULL,
						country varchar(255) DEFAULT NULL,
						special_requirements text,
						car_id bigint(20) unsigned DEFAULT NULL,
						price decimal(16,2) DEFAULT '0.00',
						discount_rate decimal(16,2) DEFAULT '0.00',
						tax decimal(16,2) DEFAULT '0.00',
						total_price decimal(16,2) DEFAULT '0.00',						
						currency_code varchar(8) DEFAULT '',
						exchange_rate decimal(16,8) DEFAULT '1',
						deposit_price decimal(16,2) DEFAULT '0.00',
						deposit_paid tinyint(1) DEFAULT '0',
						date_from date DEFAULT NULL,
						date_to date DEFAULT NULL,
						time_from varchar(50) DEFAULT NULL,
						time_to varchar(50) DEFAULT NULL,
						location_from varchar(255) DEFAULT NULL,
						location_to varchar(255) DEFAULT NULL,
						user_id bigint(20) unsigned DEFAULT NULL,
						created datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
						pin_code int(5) DEFAULT NULL,
						booking_no int(20) DEFAULT NULL,
						status tinyint(1) DEFAULT '1',
						updated datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
						mail_sent tinyint(1) DEFAULT '0',
						other text,
						woo_order_id bigint(20) unsigned DEFAULT NULL,
						PRIMARY KEY  (id)
					) DEFAULT CHARSET=utf8;";
			dbDelta($sql);

			$sql = "CREATE TABLE " . TRAV_CRUISE_BOOKINGS_TABLE . " (
						id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
						first_name varchar(255) NOT NULL,
						last_name varchar(255) NOT NULL,
						email varchar(255) NOT NULL,
						country_code varchar(5) DEFAULT NULL,
						phone varchar(255) DEFAULT NULL,
						address varchar(255) DEFAULT NULL,
						city varchar(255) DEFAULT NULL,
						zip varchar(255) DEFAULT NULL,
						country varchar(255) DEFAULT NULL,
						special_requirements text,
						cruise_id bigint(20) unsigned DEFAULT NULL,
						cabin_type_id bigint(20) unsigned DEFAULT NULL,
						cabins tinyint(1) unsigned DEFAULT '0',
						adults tinyint(1) unsigned DEFAULT '0',
						kids tinyint(1) unsigned DEFAULT '0',
						child_ages text,
						discount_rate decimal(16,2) DEFAULT '0.00',
						cabin_price decimal(16,2) DEFAULT '0.00',
						tax decimal(16,2) DEFAULT '0.00',
						total_price decimal(16,2) DEFAULT '0.00',
						currency_code varchar(8) DEFAULT '',
						exchange_rate decimal(16,8) DEFAULT '1',
						deposit_price decimal(16,2) DEFAULT '0.00',
						deposit_paid tinyint(1) DEFAULT '0',
						date_from date DEFAULT NULL,
						date_to date DEFAULT NULL,
						duration int(5) DEFAULT NULL,
						user_id bigint(20) unsigned DEFAULT NULL,
						created datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
						pin_code int(5) DEFAULT NULL,
						booking_no int(20) DEFAULT NULL,
						status tinyint(1) DEFAULT '1',
						updated datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
						mail_sent tinyint(1) DEFAULT '0',
						other text,
						woo_order_id bigint(20) unsigned DEFAULT NULL,
						PRIMARY KEY  (id)
					) DEFAULT CHARSET=utf8;";
			dbDelta($sql);

			$sql = "CREATE TABLE " . TRAV_CRUISE_SCHEDULES_TABLE . " (
						id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
						cruise_id bigint(20) unsigned DEFAULT NULL,
						date_from date DEFAULT '0000-00-00',
						date_to date DEFAULT '0000-00-00',
						duration tinyint(11) unsigned DEFAULT NULL,
						departure varchar(200) DEFAULT NULL,
						arrival varchar(200) DEFAULT NULL,
						itinerary text,
						PRIMARY KEY  (id)
					) DEFAULT CHARSET=utf8;";
			dbDelta($sql);

			$sql = "CREATE TABLE " . TRAV_CRUISE_VACANCIES_TABLE . " (
						id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
						date_from date DEFAULT '0000-00-00',
						date_to date DEFAULT '9999-12-31',
						cruise_id bigint(20) unsigned DEFAULT NULL,
						cabin_type_id bigint(20) unsigned DEFAULT NULL,
						cabins tinyint(11) unsigned DEFAULT NULL,
						price_per_duration decimal(16,2) DEFAULT '0.00',
						price_per_cabin decimal(16,2) DEFAULT '0.00',
						price_per_person decimal(16,2) DEFAULT '0.00',
						child_price varchar(1000) DEFAULT NULL,
						other text,
						PRIMARY KEY  (id)
					) DEFAULT CHARSET=utf8;";
			dbDelta($sql);


			$sql = "CREATE TABLE " . TRAV_FLIGHT_BOOKINGS_TABLE . " (
						id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
						flight_id bigint(20) unsigned DEFAULT NULL,
						st_id tinyint(1) DEFAULT '0',
						flight_date date DEFAULT '0000-00-00',
						location_from tinyint(11) DEFAULT NULL,
						location_to tinyint(11) DEFAULT NULL,
						departure_time varchar(255) DEFAULT NULL,
						arrival_time varchar(255) DEFAULT NULL,
						air_line tinyint(11) DEFAULT NULL,
						flight_type tinyint(11) DEFAULT NULL,
						flight_stop tinyint(11) DEFAULT NULL,

						trip_type varchar(255) DEFAULT NULL,
						return_flight_id bigint(20) unsigned DEFAULT NULL,
						return_flight_date date DEFAULT NULL,
						return_location_from tinyint(11) DEFAULT NULL,
						return_location_to tinyint(11) DEFAULT NULL,
						return_departure_time varchar(255) DEFAULT NULL,
						return_arrival_time varchar(255) DEFAULT NULL,
						return_air_line tinyint(11) DEFAULT NULL,
						return_flight_type tinyint(11) DEFAULT NULL,
						return_flight_stop tinyint(11) DEFAULT NULL,

						first_name varchar(255) NOT NULL,
						last_name varchar(255) NOT NULL,
						email varchar(255) NOT NULL,
						country_code varchar(50) DEFAULT NULL,
						phone varchar(255) DEFAULT NULL,
						address varchar(255) DEFAULT NULL,
						city varchar(255) DEFAULT NULL,
						zip varchar(255) DEFAULT NULL,
						country varchar(255) DEFAULT NULL,
						special_requirements text,
						adults tinyint(1) unsigned DEFAULT '0',
						tax decimal(16,2) DEFAULT '0.00',
						discount_rate decimal(16,2) DEFAULT '0.00',
						total_price decimal(16,2) DEFAULT '0.00',
						currency_code varchar(8) DEFAULT '',
						exchange_rate decimal(16,8) DEFAULT '1',
						deposit_price decimal(16,2) DEFAULT '0.00',
						deposit_paid tinyint(1) DEFAULT '0',
						user_id bigint(20) unsigned DEFAULT NULL,
						pin_code int(5) DEFAULT NULL,
						booking_no int(20) DEFAULT NULL,
						status tinyint(1) DEFAULT '1',
						created datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
						updated datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
						mail_sent tinyint(1) DEFAULT '0',
						other text,
						woo_order_id bigint(20) unsigned DEFAULT NULL,
						PRIMARY KEY  (id)
					) DEFAULT CHARSET=utf8;";
			dbDelta($sql);

			update_option( "trav_db_version", TRAV_DB_VERSION );
	  	}

	  	if ( '1.5' <= $installed_db_ver ) { 
			// add "discount_rate" column into accommodation_bookings table
			$row = $wpdb->get_results( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
				WHERE table_name = '" . TRAV_ACCOMMODATION_BOOKINGS_TABLE . "' AND column_name = 'discount_rate'" );

			if ( empty( $row ) ) { 
				$wpdb->query( "ALTER TABLE " . TRAV_ACCOMMODATION_BOOKINGS_TABLE . " ADD discount_rate decimal(16,2) DEFAULT '0.00'" );
			}

			// add "discount_rate" column into tour_bookings table
			$row = $wpdb->get_results( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
				WHERE table_name = '" . TRAV_TOUR_BOOKINGS_TABLE . "' AND column_name = 'discount_rate'" );

			if ( empty( $row ) ) { 
				$wpdb->query( "ALTER TABLE " . TRAV_TOUR_BOOKINGS_TABLE . " ADD discount_rate decimal(16,2) DEFAULT '0.00'" );
			}
	  	}

	  	if ( '3.1' <= $installed_db_ver ) { 
			// add "discount_rate" column into accommodation_bookings table
			$row = $wpdb->get_results( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
				WHERE table_name = '" . TRAV_FLIGHT_BOOKINGS_TABLE . "' AND column_name = 'return_flight_id'" );

			if ( empty( $row ) ) { 
				$wpdb->query( "ALTER TABLE " . TRAV_FLIGHT_BOOKINGS_TABLE . " ADD trip_type varchar(255) DEFAULT NULL" );
				$wpdb->query( "ALTER TABLE " . TRAV_FLIGHT_BOOKINGS_TABLE . " ADD return_flight_id bigint(20) unsigned DEFAULT NULL" );
				$wpdb->query( "ALTER TABLE " . TRAV_FLIGHT_BOOKINGS_TABLE . " ADD return_flight_date date DEFAULT NULL" );
				$wpdb->query( "ALTER TABLE " . TRAV_FLIGHT_BOOKINGS_TABLE . " ADD return_location_from tinyint(11) DEFAULT NULL" );
				$wpdb->query( "ALTER TABLE " . TRAV_FLIGHT_BOOKINGS_TABLE . " ADD return_location_to tinyint(11) DEFAULT NULL" );
				$wpdb->query( "ALTER TABLE " . TRAV_FLIGHT_BOOKINGS_TABLE . " ADD return_departure_time varchar(255) DEFAULT NULL" );
				$wpdb->query( "ALTER TABLE " . TRAV_FLIGHT_BOOKINGS_TABLE . " ADD return_arrival_time varchar(255) DEFAULT NULL" );
				$wpdb->query( "ALTER TABLE " . TRAV_FLIGHT_BOOKINGS_TABLE . " ADD return_air_line tinyint(11) DEFAULT NULL" );
				$wpdb->query( "ALTER TABLE " . TRAV_FLIGHT_BOOKINGS_TABLE . " ADD return_flight_type tinyint(11) DEFAULT NULL" );
				$wpdb->query( "ALTER TABLE " . TRAV_FLIGHT_BOOKINGS_TABLE . " ADD return_flight_stop tinyint(11) DEFAULT NULL" );
			}
	  	}


		$installed_theme_ver = get_option( "trav_theme_version" );
		if ( TRAV_VERSION != $installed_theme_ver ) {
			update_option( "trav_theme_version", TRAV_VERSION );
		}
	}
}

add_action( "after_switch_theme", "trav_create_extra_tables" );
