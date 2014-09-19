<?php
/* ---- Default category-accordion template ---- */

//Check for Multi Event Registration
$multi_reg = false;
if (function_exists('event_espresso_multi_reg_init')) {
	$multi_reg = true;
}
$toutput .= '<div id="espresso_accordion"><ul class="espresso-category-accordion">';

foreach ($categories as $category) {
					$i = 0;

	$catcode = $category->id;
	$catmeta = unserialize($category->category_meta);
	$bg = $catmeta['event_background'];
	$fontcolor = $catmeta['event_text_color'];
		$use_bg = $catmeta['use_pickers'];

	if($use_bg == "Y") {
		$toutput .= '<li class="has-sub" style="border-left: 10px solid ' . $bg . ';">';
	} else {
		$toutput .= '<li class="has-sub" style="border-left: 10px solid #CCC;">';
	}

	$toutput .= '<h2 class="ee-category"><a href="#">'.stripslashes($category->category_name).'</a></h2>';
	$toutput .= '<ul>';

	foreach ($events as $event){
		global $this_event_id;
		$this_event_id = $event->id;
		$this_event_desc	= explode('<!--more-->', $event->event_desc);
		$this_event_desc 	= array_shift($this_event_desc);
		$path_to_thumbnail = '';
		$filename = '';
		$event_meta = unserialize($event->event_meta);
		$link_text = __('Register Now!', 'event_espresso');
		$event_status = event_espresso_get_status($event->id);
		$externalURL = $event->externalURL;
		$registration_url = !empty($externalURL) ? $externalURL : espresso_reg_url($event->id);
		if (!empty($event_meta['event_thumbnail_url'])){
			$upload_dir = wp_upload_dir();
			$pathinfo = pathinfo( $event_meta['event_thumbnail_url'] );
			$dirname = $pathinfo['dirname'] . '/';
			$filename = $pathinfo['filename'];
			$ext = $pathinfo['extension'];
			$path_to_thumbnail = $dirname . $filename . '.' . $ext;
			if ( $pathinfo['dirname'] == $upload_dir['baseurl'] ) {
				if ( ! file_exists( $uploads['basedir'] . DIRECTORY_SEPARATOR . $filename . '.' . $ext )) {
					$path_to_thumbnail = file_exists( $uploads['basedir'] . DIRECTORY_SEPARATOR . $filename . '.' . $ext ) ? $event_meta['event_thumbnail_url'] : FALSE;
				}
			}
		}

		//lets check the staus and attendee count
		if ( has_filter( 'filter_hook_espresso_get_num_available_spaces' ) ){
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
				//$ed = explode('<!--more-->', $event->event_desc);
				
				$toutput .= '<li>'.(!empty($filename)?'<a href="' . $registration_url . '""><img id="ee-event-thumb-' . $event->id . '" class="ee-event-thumb" src="' . $path_to_thumbnail . '" alt="image of ' . $filename . '" /></a>':'').'<h3 class="event-title" id="event-title-' . $event->id . '" ><a href="' . $registration_url . '"">' . $event_name . '</a></h3>';


				if( isset($ee_attributes['show_description']) && $ee_attributes['show_description'] == "false" ) { 
					//do nothing 
				} else {
					$toutput .= '<div class="event-desc">'.espresso_format_content( $this_event_desc ).'</div>';
				}

				$toutput .= '<p id="p_event_price-'. $event->id .'" class="event_price event-cost"><span class="section-title">'.__('Price: ', 'event_espresso').'</span> ' . $org_options['currency_symbol'].$event->event_cost . '</p>';
				$toutput .= '<p id="event_date-'.$event->id.'" class="event-date event-meta"><span class="section-title ">'.__('Date:', 'event_espresso').'</span> ' . event_date_display($event->start_date.' '.$event->start_time, get_option('date_format').' '.get_option('time_format')) . '</p>';
				$toutput .= isset($event->venue_name) ? '<p id="event_venue-'.$event->id.'" class="event-venue event-meta"><span class="section-title ">'.__('Venue:', 'event_espresso').'</span> ' . stripslashes($event->venue_name) . '</p>' : '';

				$toutput .= '<p class="event-status"><a href="' . $registration_url . '"">' . $link_text . '</a></p>';
				$toutput .= '</li>';
			$i++;
			}
		}
	}
				if( $i == 0 ) { $toutput .= '<li class="catacc_noevents">' . __('No events found.', 'event_espresso') . '</li>'; }

	$toutput .= '</ul>';
}

$toutput .= '</li></ul></div>';

echo $toutput;
