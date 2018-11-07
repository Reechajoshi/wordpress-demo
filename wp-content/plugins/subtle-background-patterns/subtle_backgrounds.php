<?php
/*
Plugin Name: Subtle Background Patterns
Plugin URI: http://www.clubdesign.at
Description: Add the world famous SubtlePatterns ( http://www.subtlepatterns.com ) as backgrounds into your Wordpress Blog. Patterns are updated as Subtlepatterns adds new patterns. Enjoy the Live Preview of Patterns. Cycle through all patterns on your own website and save one if you like it!
Version: 1.2
Author: Marcus Pohorely
Author URI: http://www.clubdesign.at
*/

if (!defined('SUBPAT_PLUGIN_DIR')) define( 'SUBPAT_PLUGIN_DIR', dirname(__FILE__) );
if (!defined('SUBPAT_OPTIONS_NAME') ) define('SUBPAT_OPTIONS_NAME', 'subpat_options');


class SubPat {
    
	public $version = '1.2'; 

	public $version_field_name = 'subpat_version';
	public $options_field_name = SUBPAT_OPTIONS_NAME;

	public $pluginurl;
	private $updateurl;
	public $options;


	private $github_subtle_url = 'https://api.github.com/repos/subtlepatterns/SubtlePatterns/git/trees/master';
	private $github_subtle_raw_url = 'https://raw.github.com/subtlepatterns/SubtlePatterns/master/';

	private $github_response;


	protected $gettext_domain = 'subpat';

	public function __construct() {

		load_plugin_textdomain( 'subpat', false, '/subtle_backgrounds/localization' );

		$this->pluginurl 				= plugins_url('', __FILE__);

		$this->get_options();
				
        register_activation_hook(  __FILE__, array( &$this, 'activate_plugin' ) );
        register_deactivation_hook( __FILE__, array( &$this,'deactive_plugin' ) );
        	
		//action hooks
        add_action( 'admin_init', array( &$this, 'admin_init' ) );
        add_action( 'wp_enqueue_scripts', array( &$this, 'theme_init' ) );
    	add_action( 'load-appearance_page_custom-background', array( &$this, 'load_custom_background_page' ) );
		add_action( 'after_setup_theme', array( &$this, 'check_theme_support' ), 1000 );
        
        add_action('admin_print_scripts-appearance_page_custom-background', array(&$this, 'register_admin_scripts') );

		add_action('admin_notices', array(&$this, 'the_notices') );

// Maybe some time
#		add_action('admin_bar_menu', array( &$this, 'admin_bar_menu' ), 35);


		add_action('admin_head', array(&$this, 'admin_head') );
		add_action('wp_head', array(&$this, 'frontend_head') );

  	}

  	public function activate_plugin() {

		if(version_compare(PHP_VERSION, '5.2.0', '<')) { 
		  deactivate_plugins(plugin_basename(__FILE__)); // Deactivate plugin
		  wp_die("Sorry, but you can't run this plugin, it requires PHP 5.2 or higher."); 
		  return; 
		}
		
		$this->install();

	}

	public function deactive_plugin() {
		
		delete_option( SUBPAT_OPTIONS_NAME );			
			
	}

    public function admin_head() {

    	$livemode = ( isset( $_POST['subtle-pattern-live-mode'] ) ) ? $_POST['subtle-pattern-live-mode'] : $this->options['subtle-live-mode'];

	    echo '<script type="text/javascript">';
	    echo 'var subtle_live_mode_option = ' . $livemode . ';';
	    echo '</script>';
	

	}

	public function frontend_head() {

	    echo '<script type="text/javascript">';
	    echo 'var ajaxurl = "' . admin_url('admin-ajax.php') . '";';
	    echo '</script>';

	}
      
    public function admin_init() {

    	if( isset($_GET['subtle_msg']) && $_GET['subtle_msg'] == '1' ) {
    		$this->options['subtle-notice'] = '1';
    		$this->save_options();
    	}

        wp_register_script( 'subtle-backgrounds-admin-script', plugins_url('/subtle-backgrounds-admin.js', __FILE__), array('jquery'), '', true );
        wp_register_style( 'subtle-backgrounds-admin-css', plugins_url('/subtle-backgrounds-admin.css', __FILE__) );

        add_action( 'wp_ajax_subtlepat', array( &$this, 'ajax_requests' ) );
    }

    public function theme_init() {
    	
    	if( current_user_can('edit_theme_options') && $this->options['subtle-live-mode'] == 1 ) {

	    	wp_enqueue_script( 'subtle-backgrounds-frontend-slider', plugins_url('/subtle-flexslider.js', __FILE__), array('jquery'), '', true );
	    	wp_enqueue_script( 'subtle-backgrounds-frontend-script', plugins_url('/subtle-backgrounds-frontend.js', __FILE__), array('jquery'), '', true );
	    	wp_enqueue_style( 'subtle-backgrounds-admin-css', plugins_url('/subtle-backgrounds-frontend.css', __FILE__) );

	    }

    }

	public function register_admin_scripts() {
		
        wp_enqueue_script( 'subtle-backgrounds-admin-script' );
		wp_enqueue_style( 'subtle-backgrounds-admin-css' );
		wp_enqueue_style( 'wp-pointer' );
	    wp_enqueue_script( 'wp-pointer' );
	    
	}

	public function the_notices() {

		if( $this->options['subtle-notice'] == '1' ) {
			return;
		}


		if( defined('SUBPAT_NOTICE') && SUBPAT_NOTICE == '0' ) {
			return;
		}

		parse_str($_SERVER['QUERY_STRING'], $params);

	    printf(__('<div class="updated">
	    		<p>
	    		<div style="float: right"><a href="%1$s">Hide message</a></div>
	    		Superb!<br>
	    		You have successfully installed the <strong>SubtlePatterns Wordpress Plugin!</strong><br> 
	    		Now go and edit your page background <a href="' . get_admin_url() . 'themes.php?page=custom-background">in the Appearance panel!</a> 
	    		Also, <a href="' . get_bloginfo('url') . '" target="_blank">try the live mode!</a>
	    		</p>
	    		</div>'), '?' . http_build_query( array_merge($params, array('subtle_msg'=>'1') ) ) );
	}


	public function get_options() {
		
		if ( !$options = get_option( SUBPAT_OPTIONS_NAME ) ) {
			$options = array(
				'subtle-version' 	=> $this->version,
				'subtle-live-mode' 	=> '1',
				'subtle-notice' 	=> '0'
			);

			if( defined('SUBPAT_LIVE_MODE') && SUBPAT_LIVE_MODE == '0' ) {
				$options['subtle-live-mode'] = '0';
			}

			update_option( SUBPAT_OPTIONS_NAME, $options);
		}
		$this->options = $options;

	}

	public function save_options() {

		return update_option( SUBPAT_OPTIONS_NAME, $this->options );

	}

	public function check_version() {
		
		if( get_option($this->version_field_name) != $this->version)
		    $this->upgrade();

	}
    
    public function check_theme_support() {
        if( ! current_theme_supports( 'custom-background' ) ) add_custom_background( array( &$this, 'custom_background_callback' ) );
    }
    
    public function custom_background_callback() {
        
    	$background         = get_background_image();
		$color              = get_background_color();
        
		if ( ! $background && ! $color ) return;

		$style              = $color ? "background-color: #$color;" : '';

		if ( $background ) {
			$image          = " background-image: url('$background');";

			$repeat         = get_theme_mod( 'background_repeat', 'repeat' );
			if ( ! in_array( $repeat, array( 'no-repeat', 'repeat-x', 'repeat-y', 'repeat' ) ) )
				$repeat     = 'repeat';
			$repeat         = " background-repeat: $repeat;";

			$position       = get_theme_mod( 'background_position_x', 'left' );
			if ( ! in_array( $position, array( 'center', 'right', 'left' ) ) )
				$position   = 'left';
			$position       = " background-position: top $position;";

			$attachment     = get_theme_mod( 'background_attachment', 'scroll' );
			if ( ! in_array( $attachment, array( 'fixed', 'scroll' ) ) )
				$attachment = 'scroll';
			$attachment     = " background-attachment: $attachment;";

			$style          .= $image . $repeat . $position . $attachment;
		}
	
		echo '<style type="text/css">';
		echo 'body { ' . trim( $style ) . '  !important }';
		echo '</style>';
	}
    
    public function load_custom_background_page() {

		if( isset( $_POST['background_image'] ) && $_POST['background_pattern_updated'] == 1 ) {
			set_theme_mod( 'background_image', esc_url( $_POST['background_image'] ) ); // used by WP
			set_theme_mod( 'background_image_thumb', esc_url( $_POST['background_image'] ) );
			set_theme_mod( 'background_repeat', 'repeat' );
		}

		if( isset( $_POST['subtle-pattern-live-mode'] ) ) {
			$this->options['subtle-live-mode'] = $_POST['subtle-pattern-live-mode'];
			$this->save_options();
		}

		if( $this->options['subtle-notice'] == '0' ) {
    		$this->options['subtle-notice'] = '1';
    		$this->save_options();
    	}

	}

	public function ajax_requests() {
		global $current_user;

		if( !current_user_can('edit_themes') ) {
			print json_encode( array('error' => 'Invalid request!') );
			exit;
		}

		switch( $_POST['action_type'] ) {
			
			case "getPatterns":
				$response = $this->getPatterns();
			break;

			case "setBackground":
				$response = $this->getSetBackground();
			break;

		}
		
		print $response;
		exit;
	}

	private function getPatterns() {
		
		$patterns = $this->getPatternsFromGithub();
		// $patterns = $this->getPatternsFromJson();
		// $patterns = $this->getPatternsFromBackupSource();

		return $patterns;

	}

	private function getPatternsFromGithub() {

	    $response = wp_remote_get( $this->github_subtle_url );

	    if($response['response']['code'] == 200) {

	    	$this->github_response = $response['body'];
	    	return $this->saveJson()->prepareJson();

	    } else {

	    	return json_encode( array('error' => 'Sorry, no connection to the background source. Please try again later!') );

	    }

	}

	private function saveJson() {

		if( !is_dir( SUBPAT_PLUGIN_DIR . '/json' ) ) mkdir( SUBPAT_PLUGIN_DIR . '/json' );
		
		file_put_contents(SUBPAT_PLUGIN_DIR . '/json/github_response.json', $this->github_response);

		return $this;
	}

	private function prepareJson() {

		if(!$json = json_decode( $this->github_response )) {
			return json_encode( array('error' => 'Some Error occured while trying to parse the fetched String. Please check the Json Logfile in the Plugin Directory and post it to the support forums!') );
		}

		$files = $json->tree;

		if(count((array)$files) < 10) {
			return json_encode( array('error' => 'Some Error occured! There should have been sent more backgrounds! Please check the Json Logfile in the Plugin Directory and post it to the support forums!') );
		}

		$response = array();

		foreach( $files as $k => $file ) {

			if(substr( $file->path, -3 ) != 'png') continue;

			$response[] = array(
				'url' => $this->github_subtle_raw_url . $file->path,
				'name' => str_replace(array('.png', '_'), array('', ' '), $file->path)
			);

		}

		return json_encode( $response );
	}

	private function getPatternsFromJson() {
		// Maybe later
		return 'Patterns loaded from local Json.';

	}

	private function getPatternsFromBackupSource() {
		// Maybe later
		return 'Patterns loaded from Backup source!';

	}

	public function getSetBackground() {

		if ( ! current_user_can('edit_theme_options') || ! isset( $_POST['url'] ) ) exit;

		$url 		= $_POST['url'];
		$filename 	= basename( $url );

		$gh_pattern = strpos( $url, $this->github_subtle_raw_url );

		if($gh_pattern === false) {
			return json_encode( array('error' => 'Go away, bad boy. Hacking is NOT cool! ;)') );
			exit;
		}



		// Get Image
		$response 	= wp_remote_get( $url );
		$newfile 	= $response['body']; 
		$upload_dir = wp_upload_dir();
		$uploadPath = $upload_dir['path'] . '/' . $filename;
		file_put_contents( $uploadPath, $newfile );

		$wp_filetype = wp_check_filetype( $filename, null );

		$attachment = array(
			'post_mime_type' 	=> $wp_filetype['type'],
			'guid' 				=> $upload_dir['url'] . '/' . $filename,
			'post_title' 		=> 'subtlepattern_com - ' . str_replace(array('.png', '_'), array('', ' '), $filename),
		);

		$attachment_id = wp_insert_attachment( $attachment, $uploadPath );

		require_once(ABSPATH . 'wp-admin/includes/image.php');
		$attach_data = wp_generate_attachment_metadata( $attachment_id, $uploadPath );
		wp_update_attachment_metadata( $attachment_id, $attach_data );

		update_post_meta( $attachment_id, '_wp_attachment_is_custom_background', get_option('stylesheet' ) );

		$url = wp_get_attachment_image_src( $attachment_id, 'full' );
		$thumbnail = wp_get_attachment_image_src( $attachment_id, 'thumbnail' );
		set_theme_mod( 'background_image', esc_url_raw( $url[0] ) );
		set_theme_mod( 'background_image_thumb', esc_url_raw( $thumbnail[0] ) );

		return json_encode( array('success'=>true) );
	} 

	public function admin_bar_menu() {


	}

	public function install() {
	  
		// add_option($this->version_field_name, $this->version);
	
    }

    private function upgrade() {

    }


}
// Initalize the plugin
$SubPat = new SubPat();
?>