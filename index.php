<?php
/*
  Plugin Name: Event Espresso Template - Category Accordion
  Plugin URI: http://www.eventespresso.com
  Description: Will display the categories in bars, once clicked events associated with that category will appear in an "accordion" style. If category colours are turned on, the block to the left will be that colour, otherwise it will default to grey. [EVENT_CUSTOM_VIEW template_name="category-accordion"] (Extra parameter: exclude="1,2,3" This uses the category IDs and will exclude them from being listed. Use a single number or a comma separated list of numbers.)
  Version: 1.0.p
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

add_action('action_hook_espresso_custom_template_category-accordion','espresso_category_accordion', 10, 1);

if (!function_exists('espresso_category_accordion')) {

	function espresso_category_accordion(){

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

		//Check for Multi Event Registration
		$multi_reg = false;
		if (function_exists('event_espresso_multi_reg_init')) {
			$multi_reg = true;
		}
		echo '<div id="espresso_accordion"><ul class="espresso-category-accordion">';

		foreach ($categories as $category) {
			$catcode = $category->id;
			$catmeta = unserialize($category->category_meta);
			$bg = $catmeta['event_background'];
			$fontcolor = $catmeta['event_text_color'];
   			$use_bg = $catmeta['use_pickers'];

			if($use_bg == "Y") {
				echo '<li class="has-sub" style="border-left: 10px solid ' . $bg . ';"><a href="#">';
			} else {
				echo '<li class="has-sub" style="border-left: 10px solid #CCC;"><a href="#">';
			}
		
			echo '<h2 class="ee-category">'.$category->category_name.'</h2></a>';
			echo '<ul><li>';

			foreach ($events as $event){
				$path_to_thumbnail = '';
				$filename = '';
				$event_meta = unserialize($event->event_meta);
				$link_text = __('Register Now!', 'event_espresso');
				if (!empty($event_meta['event_thumbnail_url'])){
					$upload_dir = wp_upload_dir();
					$pathinfo = pathinfo( $event_meta['event_thumbnail_url'] );
					$dirname = $pathinfo['dirname'] . '/';
					$filename = $pathinfo['filename'];
					$ext = $pathinfo['extension'];
					$path_to_thumbnail = $dirname . $filename . '.' . $ext;
					$externalURL = $event->externalURL; $registration_url = !empty($externalURL) ? $externalURL : espresso_reg_url($event->id);
					$event_status = event_espresso_get_status($event->id);
					if ( $pathinfo['dirname'] == $upload_dir['baseurl'] ) {
						if ( ! file_exists( $uploads['basedir'] . DIRECTORY_SEPARATOR . $filename . '.' . $ext )) {
							$path_to_thumbnail = file_exists( $uploads['basedir'] . DIRECTORY_SEPARATOR . $filename . '.' . $ext ) ? $event_meta['event_thumbnail_url'] : FALSE;
						}
					}
				}
				
				//lets check the staus and attendee count
				if ( ! has_filter( 'filter_hook_espresso_get_num_available_spaces' ) ){
					$open_spots		= apply_filters('filter_hook_espresso_get_num_available_spaces', $event->id); //Available in 3.1.37
				}else{
					$open_spots		= get_number_of_attendees_reg_limit($event->id, 'number_available_spaces');
				}

				if($open_spots < 1 && $event->allow_overflow == 'N') {
					$link_text = __('Sold Out', 'event_espresso');
				} else if ($open_spots < 1 && $event->allow_overflow == 'Y'){
					$registration_url = espresso_reg_url($event->overflow_event_id);
					$link_text = !empty($event->overflow_event_id) ? __('Join Wait List', 'event_espresso') : __('Sold Out', 'event_espresso');
				}
				
				if ( $event_status == 'NOT_ACTIVE' ) {
					$link_text = __('Closed', 'event_espresso');
				}

				$event_name = stripslashes_deep($event->event_name);

				$arr=explode(",",$event->category_id);
				foreach ($arr as $a) {
					if ($a == $catcode) {
						echo '<li><h3 class="event-title" id="event-title-' . $event->id . '" ><a href="' . $registration_url . '"">' . $event_name . '</a></h3>';
						echo !empty($filename)?'<img id="ee-event-thumb-' . $event->id . '" class="ee-event-thumb" src="' . $path_to_thumbnail . '" alt="image of ' . $filename . '" />':'';
						echo '<p id="p_event_price-'. $event->id .'" class="event_price event-cost"><span class="section-title">'.__('Price: ', 'event_espresso').'</span> ' . $org_options['currency_symbol'].$event->event_cost . '</p>';
						echo '<p id="event_date-'.$event->id.'" class="event-date event-meta"><span class="section-title ">'.__('Date:', 'event_espresso').'</span> ' . event_date_display($event->start_date.' '.$event->start_time, get_option('date_format').' '.get_option('time_format')) . '</p>';

						echo '<p class="event-status"><a href="' . $registration_url . '"">' . $link_text . '</a></p>';
						echo '</li>';
					}
				}
			}
			echo '</ul>';
		}
	echo '</li></ul></div>';
	
	?>
	<script>
	jQuery(function ($) {
		$('#espresso_accordion > ul > li > a').click(function() {
			$('#espresso_accordion li').removeClass('active');
			$(this).closest('li').addClass('active');	
			var checkElement = $(this).next();
			if((checkElement.is('ul')) && (checkElement.is(':visible'))) {
				$(this).closest('li').removeClass('active');
				checkElement.slideUp('normal');
			}
			if((checkElement.is('ul')) && (!checkElement.is(':visible'))) {
				$('#espresso_accordion ul ul:visible').slideUp('normal');
				checkElement.slideDown('normal');
			}
			if($(this).closest('li').find('ul').children().length == 0) {
				return true;
			} else {
				return false;	
			}		
		});
	});
	</script>
	<?php
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