<?php
/*
Plugin Name: Toolbar Flags
Plugin URI: http://www.epicmedia.com/tools/tool/wp-toolbar-flags-plugin/
Description: Displays the status of WP_DEBUG, DISALLOW_FILE_EDIT, and DISALLOW_FILE_MODS in the Toolbar.
Version: 1.0.1
Author: Scott Walter
Author URI: http://About.Me/ScottJWalter
License: GPL2

Copyright 2012  Scott Walter  (email : scott@epicmedia.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/
	
new Epic_ToolbarFlags;
	
class Epic_ToolbarFlags {
	private $is_set;
	
	private $flags = array(
		'WP_DEBUG'				=> array(
									'is_good'		=> False
								)
	,	'DISALLOW_FILE_MODS'	=> array(
									'is_good'		=> True
								)
	,	'DISALLOW_FILE_EDIT'	=> array(
									'is_good'		=> True
								,	'good_if'		=> 'DISALLOW_FILE_MODS'
								)
	);

	public function Epic_ToolbarFlags() {
		$this->__construct();
	}
	
	public function __construct() {
		# actions and filters
		add_action('admin_init'			, array(&$this, 'admin_init') );
		add_action('admin_init'			, array(&$this, 'presstrends_plugin') );
		
		add_action('init'				, array(&$this, 'init') );
		
		add_action('admin_notices'		, array(&$this, 'admin_notices') );
	    add_action('admin_bar_menu'		, array(&$this, 'toolbar'), 100);
	}
	
	private function process_flag($flag) {
		if ( !isset( $this->flags[$flag]['value'] ) ) {
			if ( isset( $this->flags[$flag]['good_if'] ) && $this->process_flag($this->flags[$flag]['good_if']) ) {
				$this->flags[$flag]['value'] = True;
			} else {
				$this->flags[$flag]['value'] = ( defined($flag) && (constant($flag) == $this->flags[$flag]['is_good']) ) ? True : False;
			}
		}
		
		return $this->flags[$flag]['value'];
	}
	
	// Start of Presstrends Magic
	public function presstrends_plugin() {
		// PressTrends Account API Key
		$api_key = 'kzt7dniql7qkyzo64x4igl12htvvcrz9k6gx';
		$auth = '2yr33wfsmd6xpvnb1xu4jwhzddy2blgp1';

		// Start of Metrics
		global $wpdb;
		$data = get_transient( 'presstrends_data' );
		
		if (!$data || $data == '') {
			$api_base = 'http://api.presstrends.io/index.php/api/pluginsites/update/auth/';
			$url = $api_base . $auth . '/api/' . $api_key . '/';
			$data = array();
			$count_posts = wp_count_posts();
			$count_pages = wp_count_posts('page');
			$comments_count = wp_count_comments();
			$theme_data = get_theme_data(get_stylesheet_directory() . '/style.css');
			$plugin_count = count(get_option('active_plugins'));
			$all_plugins = get_plugins();
			
			foreach($all_plugins as $plugin_file => $plugin_data) {
				$plugin_name .= $plugin_data['Name'];
				$plugin_name .= '&';
			}
			
			$plugin_data = get_plugin_data( __FILE__ );
			$plugin_version = $plugin_data['Version'];
			$posts_with_comments = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}posts WHERE post_type='post' AND comment_count > 0");
			$comments_to_posts = number_format(($posts_with_comments / $count_posts->publish) * 100, 0, '.', '');
			$pingback_result = $wpdb->get_var('SELECT COUNT(comment_ID) FROM '.$wpdb->comments.' WHERE comment_type = "pingback"');
			$data['url'] = stripslashes(str_replace(array('http://', '/', ':' ), '', site_url()));
			$data['posts'] = $count_posts->publish;
			$data['pages'] = $count_pages->publish;
			$data['comments'] = $comments_count->total_comments;
			$data['approved'] = $comments_count->approved;
			$data['spam'] = $comments_count->spam;
			$data['pingbacks'] = $pingback_result;
			$data['post_conversion'] = $comments_to_posts;
			$data['theme_version'] = $plugin_version;
			$data['theme_name'] = urlencode($theme_data['Name']);
			$data['site_name'] = str_replace( ' ', '', get_bloginfo( 'name' ));
			$data['plugins'] = $plugin_count;
			$data['plugin'] = urlencode($plugin_name);
			$data['wpversion'] = get_bloginfo('version');
			
			foreach ( $data as $k => $v ) {
				$url .= $k . '/' . $v . '/';
			}
			
			$response = wp_remote_get( $url );
			set_transient('presstrends_data', $data, 60*60*24);
		}
	}

	public function admin_init() {
		$this->is_set = False;
	
		foreach( $this->flags as $flag => $attributes ) {
			$this->is_set |= !$this->process_flag($flag);
		}
	}
	
	public function init() { 
		if( current_user_can('administrator') ) {
			$this->admin_init();
		}
	}
	
	public function admin_notices() {
		if ($this->is_set) { 
			$first = 0;

			?><div class='update-nag'><?php 
			
				foreach( $this->flags as $flag => $attributes ) {
					if ( !$attributes['value'] ) {
					
						echo '<b>' . ( $first ? '&nbsp;&mdash;&nbsp;' : '' ) . $flag . '</b> is <b>' . ( $attributes['value'] ? 'ON' : 'OFF' ) . '</b>';
						
						$first += 1;
					}
				}
			?></div><?php 
		}
	}
	
	public function toolbar() {
		global $wp_admin_bar;
		
		if( current_user_can('administrator') ) {
			if( $this->is_set ) {
                $wp_admin_bar->add_node( array( 
												'id'	=> 'epic_toolbar_flags'
											, 	'title'	=>__( '<span style="font-weight:bolder;color:yellow;">CAUTION</span>' )
											)
										);
            }
        }
    }
}

?>
