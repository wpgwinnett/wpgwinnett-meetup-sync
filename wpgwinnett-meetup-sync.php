<?php
/**
 * @wordpress-plugin
 * Plugin Name:       WP Gwinnett Meetup.com Sync
 * Plugin URI:        http://wpgwinnett.com
 * Description:       Syncs meetup information from wpgwinnett.com with meetup.com, using the meetup.com API
 * Version:           1.0.0
 * Author:            WP Gwinnett
 * Author URI:        http://wpgwinnett.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wpgwinnett-meetup-sync
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class WPGwinnett_Meetup_Sync {

	public function __construct() {
	}

	public function run() {

		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );

		add_action( 'init', array( $this, 'init' ) );
	}

	public function plugins_loaded() {
	}

	public function init() {

		add_shortcode( 'wpgwinnett_meetup_intro', array( $this, 'wpgwinnett_meetup_intro' ) );

		add_shortcode( 'wpgwinnett_meetup_meetups', array( $this, 'wpgwinnett_meetup_meetups' ) );

		add_shortcode( 'wpgwinnett_meetup_sponsors', array( $this, 'wpgwinnett_meetup_sponsors' ) );

	}

	public function wpgwinnett_meetup_intro() {

		$settings = get_option( 'meetup_group_settings' );

		return $settings[ 'welcome_message' ];
	}

	public function wpgwinnett_meetup_meetups() {

		$settings = get_option( 'meetup_group_settings' );

        $response = wp_remote_get( 'http://api.meetup.com/2/events?key=' . MEETUP_API . '&sign=true&group_urlname=' . MEETUP_GROUP . '&page=2' );

		$mfile = wp_remote_retrieve_body( $response );

		$next_event = json_decode( preg_replace( '/[\x00-\x1F\x80-\xFF]/', '', $mfile ), true );

		$settings[ 'next_event' ][ 0 ] = array(
                                               'name'        => $next_event[ 'results' ][ 0 ][ 'name' ],
                                               'description' => $next_event[ 'results' ][ 0 ][ 'description' ],
                                               'link'        => $next_event[ 'results' ][ 0 ][ 'event_url' ],
                                               'location'    => $next_event[ 'results' ][ 0 ][ 'venue' ][ 'name' ],
                                               'time'        => $next_event[ 'results' ][ 0 ][ 'time' ],
                                               );

		$settings[ 'next_event' ][ 1 ] = array(
                                               'name'        => $next_event[ 'results' ][ 1 ][ 'name' ],
                                               'description' => $next_event[ 'results' ][ 1 ][ 'description' ],
                                               'link'        => $next_event[ 'results' ][ 1 ][ 'event_url' ],
                                               'location'    => $next_event[ 'results' ][ 1 ][ 'venue' ][ 'name' ],
                                               'time'        => $next_event[ 'results' ][ 1 ][ 'time' ],
                                               );

		update_option( 'meetup_group_settings', $settings );

		$next_user_meetup_time = gmdate( 'D, M j, Y g:i a', $settings[ 'next_event' ][ 0 ][ 'time' ] );

		$meetup_address = $settings[ 'locations' ][ 'address' ];

		$next_user_meetup_location_name = $settings[ 'next_event' ][ 0 ][ 'location' ];

		$next_user_meetup_name = $settings[ 'next_event' ][ 0 ][ 'name' ];

		$next_user_meetup_link = $settings[ 'next_event' ][ 0 ][ 'link' ];

		$next_dev_meetup_time = $settings[ 'next_event' ][ 1 ][ 'time' ];

		$next_dev_meetup_location_name = $settings[ 'next_event' ][ 1 ][ 'location' ];

		$next_dev_meetup_link = $settings[ 'next_event' ][ 1 ][ 'link' ];

		$next_dev_meetup_name = $settings[ 'next_event' ][ 1 ][ 'name' ];

		$meetups = "<p><a href=\"{$next_user_meetup_link}\">{$next_user_meetup_name}</a> at <a href=\"https://maps.google.com/maps?q={$meetup_address}\">{$next_user_meetup_location_name}</a></p>";

		$meetups .= "<p><a href=\"{$next_dev_meetup_link}\">{$next_dev_meetup_name}</a> at <a href=\"https://maps.google.com/maps?q={$meetup_address}\">{$next_dev_meetup_location_name}</a></p>";


		return $meetups;
	}

	public function wpgwinnett_meetup_sponsors() {

		$settings = get_option( 'meetup_group_settings' );

		$response = wp_remote_get( 'http://api.meetup.com/2/groups?key=' . MEETUP_API . '&sign=true&group_urlname=' . MEETUP_GROUP . '&fields=other_services,sponsors,welcome_message' );

		$mfile = wp_remote_retrieve_body( $response );

		$group = json_decode( preg_replace( '/[\x00-\x1F\x80-\xFF]/', '', $mfile ), true );

		if ( empty( $settings ) ) {

			$settings = array(
				'name'            => $group[ 'results' ][ 0 ][ 'name' ],
				'organizer'       => $group[ 'results' ][ 0 ][ 'organizer' ][ 'name' ],
				'meetup_url'      => $group[ 'results' ][ 0 ][ 'link' ],
				'description'     => $group[ 'results' ][ 0 ][ 'description' ],
				'id'              => $group[ 'results' ][ 0 ][ 'id' ],
				'twitter'         => $group[ 'results' ][ 0 ][ 'other_services' ][ 'twitter' ][ 'identifier' ],
				'welcome_message' => $group[ 'results' ][ 0 ][ 'welcome_message' ],
			);

		}
		else {

			$settings['name']            = $group[ 'results' ][ 0 ][ 'name' ];
			$settings['organizer']       = $group[ 'results' ][ 0 ][ 'organizer' ][ 'name' ];
			$settings['meetup_url']      = $group[ 'results' ][ 0 ][ 'link' ];
			$settings['description']     = $group[ 'results' ][ 0 ][ 'description' ];
			$settings['id']              = $group[ 'results' ][ 0 ][ 'id' ];
			$settings['twitter']         = $group[ 'results' ][ 0 ][ 'other_services' ][ 'twitter' ][ 'identifier' ];
			$settings['welcome_message'] = $group[ 'results' ][ 0 ][ 'welcome_message' ];
		}

		$settings['sponsors'] = array();

		foreach ( $group[ 'results' ][ 0 ][ 'sponsors' ] as $sponsor ) {

			$settings[ 'sponsors' ][] = array(
				'name'        => $sponsor[ 'name' ],
				'url'         => $sponsor[ 'url' ],
				'logo'        => $sponsor[ 'image_url' ],
				'description' => $sponsor[ 'details' ],
				'provide'     => $sponsor[ 'info' ],
			);

		}

		update_option( 'meetup_group_settings', $settings );

		$sponsors = '';

		foreach ( $settings[ 'sponsors' ] as $sponsor ) {
			//if ( 'Rock, Paper, Scissors ' == $sponsor['name'] ) {

			$sponsors .= "<p><a href=\"{$sponsor['url']}\"><img src=\"{$sponsor['logo']}\" /></a>{$sponsor['description']}</p><p>{$sponsor['provide']}</p>";

		}

		return $sponsors;
	}

	// MEETUP.COM - PEOPLE
//--------------------------------
	/*function meetup_info() {

		//General Info
		$response = wp_remote_get( 'http://api.meetup.com/2/groups?key=' . MEETUP_API . '&sign=true&group_urlname=' . MEETUP_GROUP . '&fields=other_services,sponsors,welcome_message' );

		$mfile = wp_remote_retrieve_body( $response );

		$group = json_decode( preg_replace( '/[\x00-\x1F\x80-\xFF]/', '', $mfile ), true );
		//print_r( $group['results'][0]['sponsors'] );

		$settings = array(
			'name'            => $group[ 'results' ][ 0 ][ 'name' ],
			'organizer'       => $group[ 'results' ][ 0 ][ 'organizer' ][ 'name' ],
			'meetup_url'      => $group[ 'results' ][ 0 ][ 'link' ],
			'description'     => $group[ 'results' ][ 0 ][ 'description' ],
			'id'              => $group[ 'results' ][ 0 ][ 'id' ],
			'twitter'         => $group[ 'results' ][ 0 ][ 'other_services' ][ 'twitter' ][ 'identifier' ],
			'welcome_message' => $group[ 'results' ][ 0 ][ 'welcome_message' ],
		);

		foreach ( $group[ 'results' ][ 0 ][ 'sponsors' ] as $sponsor ) {

			$settings[ 'sponsors' ][] = array(
				'name'        => $sponsor[ 'name' ],
				'url'         => $sponsor[ 'url' ],
				'logo'        => $sponsor[ 'image_url' ],
				'description' => $sponsor[ 'details' ],
				'provide'     => $sponsor[ 'info' ],
			);

		}
		/*$settings['sponsors'] = array(	'name' => $sponsor['name'],
										'url' => $sponsor['url'], 
										'logo' => $sponsor['image_url'], 
									);*/


		//Meeting Location
		/*$response = wp_remote_get( 'http://api.meetup.com/2/venues?key=' . MEETUP_API . '&sign=true&group_urlname=' . MEETUP_GROUP );

		$mfile = wp_remote_retrieve_body( $response );

		$venues = json_decode( preg_replace( '/[\x00-\x1F\x80-\xFF]/', '', $mfile ), true );

		foreach ( $venues[ 'results' ] as $location ) {

			$settings[ 'locations' ] = array(
				'name'    => $location[ 'name' ],
				'address' => $location[ 'address_1' ] . ' ' . $location[ 'city' ] . ' ' . $location[ 'state' ] . ' ' . $location[ 'zip' ] . ' ' . $location[ 'country' ],
			);
		}

		//Next Event
		$response = wp_remote_get( 'http://api.meetup.com/2/events?key=' . MEETUP_API . '&sign=true&group_urlname=' . MEETUP_GROUP . '&page=2' );

		$mfile = wp_remote_retrieve_body( $response );

		$next_event = json_decode( preg_replace( '/[\x00-\x1F\x80-\xFF]/', '', $mfile ), true );

		$settings[ 'next_event' ][ 0 ] = array(
			'name'        => $next_event[ 'results' ][ 0 ][ 'name' ],
			'description' => $next_event[ 'results' ][ 0 ][ 'description' ],
			'link'        => $next_event[ 'results' ][ 0 ][ 'event_url' ],
			'location'    => $next_event[ 'results' ][ 0 ][ 'venue' ][ 'name' ],
			'time'        => $next_event[ 'results' ][ 0 ][ 'time' ],
		);

		$settings[ 'next_event' ][ 1 ] = array(
			'name'        => $next_event[ 'results' ][ 1 ][ 'name' ],
			'description' => $next_event[ 'results' ][ 1 ][ 'description' ],
			'link'        => $next_event[ 'results' ][ 1 ][ 'event_url' ],
			'location'    => $next_event[ 'results' ][ 1 ][ 'venue' ][ 'name' ],
			'time'        => $next_event[ 'results' ][ 1 ][ 'time' ],
		);

		update_option( 'meetup_group_settings', $settings );

	}/*

// MEETUP.COM - PEOPLE
//--------------------------------

	function meetup_people() {

		$api_response = wp_remote_get( 'http://api.meetup.com/2/members?key=' . MEETUP_API . '&sign=true&group_urlname=' . MEETUP_GROUP . '&page=' . MEETUP_LIMIT );

		$mfile = wp_remote_retrieve_body( $api_response );

		$meetup = json_decode( preg_replace( '/[\x00-\x1F\x80-\xFF]/', '', $mfile ), true );

		$people = array();

		$peopleindex = array();

		foreach ( $meetup[ 'results' ] as $person ) {

			$id = $person[ 'id' ];

			// Thumb used for Recent Work
			$thumb50 = wpthumb( $person[ 'photo_url' ], 'width=50&height=50&crop=1&jpeg_quality=95', false );

			// Thumb used for Header Background
			//$thumb80 = wpthumb( $person['photo_url'], 'width=80&height=80&crop=1&jpeg_quality=95', false );

			// Store for Display
			/*$people[] = array(
					'id' => $person['id'],
					'name' => $person['name'],
					'twitter' => $person['other_services']['twitter']['identifier'],
					'photo' => $thumb80,
					'link' => $person['link']
			);*/

			/*$people[] = array(
				'id'      => $person[ 'id' ],
				'name'    => $person[ 'name' ],
				'twitter' => $person[ 'other_services' ][ 'twitter' ][ 'identifier' ],
				'photo'   => $person[ 'photo' ][ 'thumb_link' ],
				'link'    => $person[ 'link' ]
			);

			// Store for cross-referencing against Recent Work (Member ID is used as key)
			$peopleindex[ $id ] = array(
				'name'    => $person[ 'name' ],
				'twitter' => $person[ 'other_services' ][ 'twitter' ][ 'identifier' ],
				'photo'   => $thumb50,
				'link'    => $person[ 'link' ]
			);
		}

		// Store count that is displayed within tagline
		update_option( 'meetup_people_count', count( $people ) );

		// Store Member ID's as Keys
		update_option( 'meetup_people_index', $peopleindex );

		// Randomize the display of avatars to keep it interesting
		shuffle( $people );

		// Output Display HTML

		$i = 0;

		$output = '';

		foreach ( $people as $person ) {

			$thumb = $person[ 'photo' ];

			// If blank avatar, skip and do not count towards the total
			if ( $thumb ) {
				$output .= '<div class="home-thumb-person" style="background-image:url(' . $thumb . ')"></div>';
			} else {
				$i -= 1;
			}

			if ( ++ $i == 100 ) {
				break;
			}

		}

		// Cover the screen
		$output .= $output;
		$output .= $output;

		// Store in case of transient fail
		update_option( 'meetup_people_backup', $output );

		return $output;

	}

	/*function meetup_people_backup() {

		$pics = get_option( 'meetup_people_backup' );
		if ( $pics ) {
			echo $pics;
		}

	}


	function meetup_people_display() {

		meetup_info();

		$t = tlc_transient( 'meetup_people_transient' );
		if ( true ) {
			$t->updates_with( 'meetup_people' );
		} else {
			$t->updates_with( 'meetup_people_backup' );
		}

		$t->expires_in( 3600 );
		$t->background_only();

		return $t->get();

	}

// MEETUP.COM - RECENT WORK
//--------------------------------

// function for retrieving the title of a website within meetup_recentwork()
// TODO check string is valid url

	function get_site_title( $url ) {

		// Get <title>$title</title> from remote url
		$str = file_get_contents( $url );
		if ( strlen( $str ) > 0 ) {
			preg_match( "/\<title\>(.*)\<\/title\>/", $str, $title );

			return $title[ 1 ];

		}
	}

	function meetup_recentwork() {

		$i = 0;

		$api_response = wp_remote_get( 'http://api.meetup.com/2/profiles.json?key=' . MEETUP_API . '&sign=true&group_urlname=' . MEETUP_GROUP . '&page=100' );
		$mfile        = wp_remote_retrieve_body( $api_response );
		$meetup       = json_decode( preg_replace( '/[\x00-\x1F\x80-\xFF]/', '', $mfile ), true );

		$recentwork = array();

		$question_id_url = get_option( 'meetup_question_url' );
		$question_id_img = get_option( 'meetup_question_img' );

		foreach ( $meetup[ 'results' ] as $person ) {

			$id = $person[ 'member_id' ];

			if ( $person[ 'answers' ] ) {

				foreach ( $person[ 'answers' ] as $question ) {

					if ( $question[ 'question_id' ] == $question_id_url ) {
						$url = $question[ 'answer' ];
					}
					if ( $question[ 'question_id' ] == $question_id_img ) {
						$img = $question[ 'answer' ];
					}

				}

			}

			if ( ( strpos( $img, '.jpg' ) !== false ) || strpos( $img, '.png' ) !== false ) {

				$recentwork[] = array( 'url' => $url, 'img' => $img, 'id' => $id );

			}

			$url = '';
			$img = '';
		}

		// Randomize recent work items to keep it fair & interesting
		shuffle( $recentwork );

		$profile = get_option( 'meetup_people_index' );

		$output = '<div class="home-recent-wrap">';

		foreach ( $recentwork as $site ) {

			$url = $site[ 'url' ];
			$img = $site[ 'img' ];

			// Skip if questions not answered
			if ( $url && $img ) {

				$thumb = wpthumb( $img, 'width=330&height=220&crop=1&jpeg_quality=95', false );

				$profilepic = $profile[ $site[ 'id' ] ][ 'photo' ];
				$name       = $profile[ $site[ 'id' ] ][ 'name' ];
				$link       = $profile[ $site[ 'id' ] ][ 'link' ];
				$sitetitle  = substr( get_site_title( $url ), 0, 30 ) . '...';

				if ( $thumb ) {
					$output .= '<div class="home-thumb-recent" style="background-image:url(' . $thumb . ')"><div class="home-thumb-recent-desc"><a href="' . $link . '"><img src="' . $profilepic . '" /></a><div class="home-recent-title"><a href="' . $url . '" rel="nofollow" target="_blank">' . $sitetitle . '</a></div><div class="home-recent-author"><em>by </em><a href="' . $link . '">' . $name . '</a></div></div></div>';
				} else {
					$i -= 1;
				}

			}

			if ( ++ $i == 100 ) {
				break;
			}

		}

		$output .= '</div>';

		// Store in case of transient fail
		update_option( 'meetup_recentwork_backup', $output );

		return $output;


	}

	function meetup_recentwork_backup() {

		$pics = get_option( 'meetup_recentwork_backup' );
		if ( $pics ) {
			echo $pics;
		}

	}

	function meetup_recentwork_display() {

		$t = tlc_transient( 'meetup_recentwork_transient' );
		if ( true ) {
			$t->updates_with( 'meetup_recentwork' );
		} else {
			$t->updates_with( 'meetup_recentwork_backup' );
		}

		$t->expires_in( 180 );
		$t->background_only();

		return $t->get();

	}*/

}

$wpgwinnett_meetup_sync = new WPGwinnett_Meetup_Sync();

$wpgwinnett_meetup_sync->run();