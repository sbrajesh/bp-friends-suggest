<?php
/*
Plugin Name: BP Friends Suggestions Widget
Plugin URI: http://buddydev.com/plugins/buddypress-friends-suggest/
Description: BuddyPress Friends Suggestion Widget  - displays friend suggestions for logged in users.
Version: 1.1.0
Author: gwu
Author URI: http://buddydev.com/members/gwu123/
 Last Updated: September 8, 2012
*/
class BP_Friend_Suggest_Helper {
	private static $instance = null;
	
	private $url;
	private $path;
	
	private function __construct() {
		
		$this->url = plugin_dir_url( __FILE__ );
		$this->path = plugin_dir_path( __FILE__ );
		
		add_action( 'bp_loaded', array( $this, 'load' ) );
		add_action( 'bp_init', array( $this, 'load_textdomain' ) );
		
		add_action( 'wp_ajax_friend_suggest_remove_suggestion', array( $this, 'remove_suggestion' ) );
		
		add_action( 'bp_enqueue_scripts', array( $this, 'load_js' ) );
		//add_action( 'bp_enqueue_scripts', array( $this, 'load_css' ) );
	}
	
	
	public static function get_instance() {
		
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}

	public function load() {
		
		$files = array(
			'bp-friend-suggest-functions.php',
			'bp-friend-suggest-widget.php'
		);
		
		foreach ( $files as $file ) {
			require_once $this->path . $file ;
		}
		
	}
	
	public function load_textdomain() {
		
		load_plugin_textdomain( 'bp-friends-suggest', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' ); 
	}
	
	/**
	 * Ajax handler for removing suggestions
	 * 
	 * @return type
	 */
	public function remove_suggestion() {
		
		$suggestion_id = $_POST['suggestion_id'] ;
		
		check_ajax_referer( 'friend-suggestion-remove-' . $suggestion_id );

		if ( empty ( $suggestion_id ) || ! is_user_logged_in() ) {
			return;
		}
		
		$user_id=  get_current_user_id();
	  
		$excluded = (array) get_user_meta( $user_id, 'hidden_friend_suggestions', true );
		$excluded[] = $suggestion_id;
	  
		update_user_meta( $user_id, 'hidden_friend_suggestions', $excluded );
		exit(0);
	}
	
	public function load_js() {
		
		if( ! is_user_logged_in() ) {
			return ;
		}
		
		wp_enqueue_script( 'friend-suggest-js', $this->url . 'friend-suggest.js', array( 'jquery' ) );
		
	}
	
}


BP_Friend_Suggest_Helper::get_instance();