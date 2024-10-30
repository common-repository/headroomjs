<?php
/*
Plugin Name: Headroom.js
Plugin URI: http://onlineboswachters.nl
Description: Headroom.js allows you to bring elements into view when appropriate, and give focus to your content the rest of the time. 
Version: 0.1
Author: Online Boswachters
Author URI: http://onlineboswachters.nl
*/

/* Copyright 2014 Online Boswachters (email : info@onlineboswachters.nl) */
/* Plugin build by Nick Williams (http://wicky.nillia.ms/headroom.js) */

$pluginurl = plugin_dir_url(__FILE__);	
define( 'hr_FRONT_URL', $pluginurl );
define( 'hr_URL', plugin_dir_url(__FILE__) );
define( 'hr_PATH', plugin_dir_path(__FILE__) );
define( 'hr_BASENAME', plugin_basename( __FILE__ ) );
define( 'hr_VERSION', '0.1' );

class headroom {
	
	private $active = true;
	
	function __construct() {
		
		$this->get_options();
		$display 			= $this->get_option('display');
		
		if (is_admin()) :
			$this->add_admin_includes();
			add_action( 'admin_init', array( $this, 'options_init' ) );
			add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		else :
			if ($display != 'on') return;
			add_action( 'wp_enqueue_scripts', array(&$this, 'enqueue_scripts'), 11);
			add_action( 'wp_footer', array( $this, 'add_script' ), 20 );
		endif;
		add_action( 'wp_enqueue_scripts', array(&$this, 'enqueue_styles'), 11);
		
	}
	
	/* OPTIONS */
	
	/**
	 * Register the options needed for this plugins configuration pages.
	 */
	function options_init() {
		register_setting( 'headroom_settings', 'hr_settings' );
	}
	
	/**
	 * Retrieve an option for the configuration page.
	 */
	function get_option($key = '') {
		if (!empty($this->options) && isset($this->options[$key])) {
			if (is_array($this->options)) :
				return $this->options[$key];
			else :
				return stripslashes($this->options[$key]);
			endif;
		}
		return false;
	}
	/**
	 * Retrieve all options for the configuration page from WP Options.
	 */
	function get_options() {
		if (isset($this->options)) return $this->options;
		if ($options = get_option('hr_settings')) {
			if (is_array($options)) :
				$this->options = $options;
			else :
				$this->options = unserialize($options);	
			endif;
		}
	}
	
	/**
	 * Save all options to WP Options database
	 */
	function save_options() {
		if (!empty($this->options)) {
			update_option('hr_settings', serialize($this->options));	
		}
	}
	
	/**
	 * Save a specifix option to WP Option database
	 */
	function save_option($key, $value, $save_to_db = false) {
		if (!empty($this->options)) {
			$this->options[$key] = $value;
		}
		if ($save_to_db == true) {
			$this->save_options();	
		}
	}
	
	/* INCLUDES */
	
	/**
	 * Include specific PHP files when visiting an admin page
	 */
	function add_admin_includes() {
		$includes = array('plugin-admin'); //add includes here that are in the includes fodler, without the .php
		$this->add_includes($includes);
	}
	
	/**
	 * Include specific PHP files when visiting a page on the website
	 */
	function add_includes($includes_new = array()) {
		$includes = array(); //add includes here that are in the includes fodler, without the .php
		if (is_array($includes_new)) $includes = $includes_new;
		if (!count($includes)) return false;
		foreach ($includes as $_include) :		
			$path = hr_PATH.'includes/'.$_include.'.php';
			if (!file_exists($path)) continue;
			include_once($path);
		endforeach;
	}
	
	/* HELPERS */
	function enqueue_scripts() {
		wp_register_script('headroom', plugins_url('/js/headroom-scripts.js', __FILE__), false, '0.4', true);
		wp_enqueue_script('headroom');
	}
	
	function add_script() {	
	
		$element = $this->get_option('element');
		if (empty($element)) return;
		
		$tolerance = $this->get_option('tolerance');
		$tolerance = trim(str_replace('px','',$tolerance));
		if (!$tolerance || !is_numeric($tolerance)) $tolerance = 5;	
		$offset = $this->get_option('offset');
		$offset = trim(str_replace('px','',$offset));
		if (!$offset || !is_numeric($offset)) $offset = 0;
		$initial = $this->get_option('class_initial');
		if (!$initial) $initial = 'animated';		
		$pinned = $this->get_option('class_pinned');
		if (!$pinned) $pinned = 'slideDown';
		$unpinned = $this->get_option('class_unpinned');
		if (!$unpinned) $unpinned = 'slideUp';
		$top = $this->get_option('class_top');
		if (!$top) $top = 'headroom--top';
		$notTop = $this->get_option('class_notTop');
		if (!$notTop) $notTop = 'headroom--not-top';
		?>
        <script type="text/javascript">
			jQuery(function() {
				jQuery("<?php echo $element; ?>").headroom({
				  "tolerance": <?php echo $tolerance; ?>,
				  "offset": <?php echo $offset; ?>,
				  "classes": {
					"initial": "<?php echo esc_js($initial); ?>",
					"pinned": "<?php echo esc_js($pinned); ?>",
					"unpinned": "<?php echo esc_js($unpinned); ?>",
					"top": "<?php echo esc_js($top); ?>",
					"notTop": "<?php echo esc_js($notTop); ?>"
				  }
				});
			});
		</script>
        <?php

	}
	function enqueue_styles() {
		wp_register_style('headroom',plugins_url('/css/headroom.css', __FILE__),'','0.5.0');
	}
	
	/* ADMIN */
	
	function add_admin_menu() {
  		add_management_page( __( "Headroom" ), __( "Headroom" ), "administrator", 'hr_settings', array( &$this, "hr_settings" ) );
	}
	
	/**
	 * The settings page where you can edit the options of this plugin
	 */
	function hr_settings() {
		
		global $table_prefix;
		global $plugin_admin;
		
		$plugin_admin->admin_header(true, 'headroom_settings', 'hr_settings');
			
		echo '<p>More info on Headroom.js you can find on the <a href="http://wicky.nillia.ms/headroom.js/">Headroom.js</a> website';
			
		echo $plugin_admin->checkbox('display',__('Activate Headroom?'));
		
		echo $plugin_admin->textinput('element',__('What HTML element do you want to activate Headroom on? (#header for example)'));
		
		$content = '';
		$content .= $plugin_admin->textinput('tolerance', __('Scroll tolerance in px before state changes (default is 5)'));
		
		$content .= $plugin_admin->textinput('offset', __('Vertical offset in px before element is first unpinned (default is 0)'));
		
		$content .= $plugin_admin->textinput('class_initial', __('Class to use for initial state (default is animated)'));
		
		$content .= $plugin_admin->textinput('class_pinned', __('Class to use for pinned state (default is slideDown)'));
		
		$content .= $plugin_admin->textinput('class_unpinned', __('Class to use for unpinned state (default is slideUp)'));
		
		$content .= $plugin_admin->textinput('class_top', __('Class to use for top state (default is headroom--top)'));
		
		$content .= $plugin_admin->textinput('class_notTop', __('Class to use for notTop state (default is headroom--not-top)'));
		
		$headroom = $this->get_option('headroom');
		//var_dump($headroom);
		
		$plugin_admin->postbox( 'hr_settings', __( 'Visual settings', 'headroom' ), $content );		
		
		$plugin_admin->admin_footer();
		
	}

}
$headroom = new headroom();
?>