<?php
/*
Plugin Name: WP Toolbar Flags
Plugin URI: https://scottjwalter.consulting/
Description: Displays the status of WP_DEBUG, DISALLOW_FILE_EDIT, and DISALLOW_FILE_MODS in the Toolbar.
Version: 1.2.1
Author: Scott J. Walter
Author URI: https://profiles.wordpress.org/scottjwalter/
License: GPL2

Copyright 2012  Scott J. Walter  (email : scott@scottjwalter.consulting)

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

class SJWC_ToolbarFlags {
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

	public function SJWC_ToolbarFlags() {
		$this->__construct();
	}

	public function __construct() {
		# actions and filters
		add_action('admin_init'			, array(&$this, 'admin_init') );

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
												'id'	=> 'sjwc_toolbar_flags'
											, 	'title'	=>__( '<span style="font-weight:bolder;color:yellow;">CAUTION</span>' )
											)
										);
            }
        }
    }
}

new SJWC_ToolbarFlags;
