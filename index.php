<?php
/*
  Plugin Name: Event Espresso Template - Category Accordion
  Plugin URI: http://www.eventespresso.com
  Description: Will display the categories in bars, once clicked events associated with that category will appear in an "accordion" style. If category colours are turned on, the block to the left will be that colour, otherwise it will default to grey. [EVENT_CUSTOM_VIEW template_name="category-accordion"] (Extra parameter: exclude="1,2,3" This uses the category IDs and will exclude them from being listed. Use a single number or a comma separated list of numbers.)
  Version: 1.2.p
  Author: Event Espresso
  Author URI: http://www.eventespresso.com
  Copyright 2013 Event Espresso (email : support@eventespresso.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA02110-1301USA

*/

function load_scripts() {
		wp_register_script( 'category_accordion', WP_PLUGIN_URL. "/".plugin_basename(dirname(__FILE__)) .'/js/category-accordion.js', array('jquery'), '0.1', TRUE );
}
add_action( 'wp_enqueue_scripts', 'load_scripts' );


add_action('action_hook_espresso_custom_template_category-accordion','espresso_category_accordion', 10, 1);

if (!function_exists('espresso_category_accordion')) {

	function espresso_category_accordion(){

		$toutput = '';

		wp_enqueue_script( 'category_accordion' );

		global $wpdb, $org_options,$events, $ee_attributes;

		//If the css_file parameter is used in the shortcode
		$css_file = isset($ee_attributes['css_file']) && !empty($ee_attributes['css_file']) ? $ee_attributes['css_file'] : 'style';

		//Register styles
		wp_register_style( 'espresso_category_accordion', WP_PLUGIN_URL. "/".plugin_basename(dirname(__FILE__)) .'/'. $css_file .'.css' );
		wp_enqueue_style( 'espresso_category_accordion');

		//Get the categories
		$sql = "SELECT * FROM " . EVENTS_CATEGORY_TABLE;
		$categories = $wpdb->get_results($sql);

		$exclude = isset($ee_attributes['exclude']) && !empty($ee_attributes['exclude']) ? explode(',', $ee_attributes['exclude']) : false;

		if($exclude) {
			foreach($exclude as $exc) {
				foreach($temp_cats as $subKey => $subArray) {
					if($subArray->id == $exc) {
						unset($temp_cats[$subKey]);
					}
				}
			}
		}

		//Check for custom templates
		if(function_exists('espresso_custom_template_locate')) {
			$custom_template_path = espresso_custom_template_locate("category-accordion");
		} else {
			$custom_template_path = '';
		}

		if( !empty($custom_template_path) ) {
			//If custom template found include here
			include( $custom_template_path );
		} else {
			//Otherwise use the default template
			include( 'template.php' );
		}
	}
}

/**
 * hook into PUE updates
 */
//Update notifications
add_action('action_hook_espresso_template_category_accordion_update_api', 'espresso_template_category_accordion_load_pue_update');
function espresso_template_category_accordion_load_pue_update() {
	global $org_options, $espresso_check_for_updates;
	if ( $espresso_check_for_updates == false )
		return;

	if (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH . 'class/pue/pue-client.php')) { //include the file
		require(EVENT_ESPRESSO_PLUGINFULLPATH . 'class/pue/pue-client.php' );
		$api_key = $org_options['site_license_key'];
		$host_server_url = 'http://eventespresso.com';
		$plugin_slug = array(
			'premium' => array('p'=> 'espresso-template-category-accordion'),
			'prerelease' => array('b'=> 'espresso-template-category-accordion-pr')
			);
		$options = array(
			'apikey' => $api_key,
			'lang_domain' => 'event_espresso',
			'checkPeriod' => '24',
			'option_key' => 'site_license_key',
			'options_page_slug' => 'event_espresso',
			'plugin_basename' => plugin_basename(__FILE__),
			'use_wp_update' => FALSE
		);
		$check_for_updates = new PluginUpdateEngineChecker($host_server_url, $plugin_slug, $options); //initiate the class and start the plugin update engine!
	}
}
