<?php

class DS_Regular_Meetings_Plugin {

	private static $instance;

	protected $loader;
	protected $plugin_slug;
	protected $version;
	protected $RMO; 	// Regular Meeting Object;
	
	public function __construct() {

		if (!self::$instance) {
			self::$instance = $this;

			$this->load_dependencies();
			
			$this->plugin_slug = 'regular-meeting-manager-slug';
			$this->version = '0.1.0';
			$this->loader = new DS_Hook_And_Filter_Loader();
			$this->RMO = new DS_Regular_Meetings();
			
			$this->define_admin_hooks();
			$this->register_shortcodes();
                	            
			return self::$instance;
		
		}  else {
			return self::$instance;
		}	
	}
	

	private function load_dependencies() {

	#	require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-single-post-meta-manager-admin.php';
		
		require_once plugin_dir_path( __FILE__ ) . 'classes-meeting-times.php';
		require_once plugin_dir_path( __FILE__ ) . 'class-hook-and-filter-loader.php';
		require_once plugin_dir_path( __FILE__ ) . 'class-meeting.php';
		require_once plugin_dir_path( __FILE__ ) . 'class-regular-meetings.php';
		

	}

	private function define_admin_hooks() {

	#	$admin = new Single_Post_Meta_Manager_Admin( $this->get_version() );
	#	$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_styles' );
	#	$this->loader->add_action( 'add_meta_boxes', $admin, 'add_meta_box' );
	
		$this->loader->add_action( 'admin_menu', $this, 'add_admin_settings' );
	}

	public function add_admin_settings () {
		if (function_exists('add_options_page')) {
			add_options_page('Regular Meetings', 'Regular Meetings', 'manage_options', 'regular-meetings-settings', array($this->RMO, 'display_admin_settings_page'));
		}
	}
	
	private function register_shortcodes() {
		add_shortcode('display_upcoming_meetings',array( $this, 'display_upcoming_meetings_shortcode_handler') );
	}
	
	public function display_upcoming_meetings_shortcode_handler ( $atts, $content = null) {
	
		$displayOptions=shortcode_atts( Array( 'display_name' => TRUE, 'display_description' => FALSE, 'date_format' => '  jS F  g:ia'), $atts);
		extract( shortcode_atts( Array( 'heading' => "Upcoming Meetings", 
						'nbr' => 9 , 'class'=>'rm_upcoming_meetings', 'ids' => null ), $atts));
						
		if ($ids<>null) $ids=explode(",",$ids);			
		$returnHTML ="<div class='$class'> <h2>$heading</h2>";	
		$returnHTML.=$this->RMO->display_upcoming_meetings($nbr, $displayOptions, $ids);
		$returnHTML.='</div>';
	
		return $returnHTML;
		
	}	
		
	public function run() {
		$this->loader->run();
	}

	public function get_version() {
		return $this->version;
	}

}
