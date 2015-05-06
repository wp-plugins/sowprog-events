<?php

require_once (dirname( __FILE__ ) . '/sowprog_events_configuration.php');

class SowprogEventsOutput {
	function output_main_page() {
		$sowprogEventsConfiguration = new SowprogEventsConfiguration();

		$account_type = $sowprogEventsConfiguration->getUserType();
		$account_id = $sowprogEventsConfiguration->getUserID();

		if (empty($account_type) || empty($account_id)) {
			return '';
		}
		
		$parts = parse_url($sowprogEventsConfiguration->getCurrentURL());
		parse_str($parts['query'], $query);

		wp_enqueue_script('pickadate-picker', plugins_url( '/includes/js/pickadate.js-3.5.5/lib/picker.js' , __FILE__ ), array( 'jquery'), false, true);
		wp_enqueue_script('pickadate-picker.date', plugins_url( '/includes/js/pickadate.js-3.5.5/lib/picker.date.js' , __FILE__ ), array( 'jquery'), false, true);
		wp_enqueue_script('pickadate-picker.time', plugins_url( '/includes/js/pickadate.js-3.5.5/lib/picker.time.js' , __FILE__ ), array( 'jquery'), false, true);
		wp_enqueue_script('pickadate-french', plugins_url( '/includes/js/pickadate.js-3.5.5/lib/translations/fr_FR.js' , __FILE__ ), array( 'jquery'), false, true);
		wp_enqueue_script('pickadate-legacy', plugins_url( '/includes/js/pickadate.js-3.5.5/lib/legacy.js' , __FILE__ ), array( 'jquery'), false, true);
		wp_enqueue_script('sowprog_events', plugins_url( '/includes/js/sowprog_events.js' , __FILE__ ), array( 'jquery'), false, true);

		wp_enqueue_style('pickadate-classic', plugins_url( '/includes/js/pickadate.js-3.5.5/lib/themes/classic.css' , __FILE__ ));
		wp_enqueue_style('pickadate-classic.date', plugins_url( '/includes/js/pickadate.js-3.5.5/lib/themes/classic.date.css' , __FILE__ ));

		if (!empty($query['swc_location']) || !empty($query['swc_event'])) {
			wp_enqueue_script('google-maps', 'https://maps.googleapis.com/maps/api/js?sensor=false');
		}
		if (!$sowprogEventsConfiguration->getDoNotUseFontAwesome()) {
			wp_enqueue_style('font-awesome', plugins_url( '/includes/css/font-awesome.min.css' , __FILE__ ));
		}
		wp_enqueue_style('sowprog-events-style', plugins_url( '/includes/css/sowprog_basic_widget.css' , __FILE__ ));

		ob_start();
		
		echo $sowprogEventsConfiguration->getCodeBefore();

		?>

		<form action="<?php echo $sowprogEventsConfiguration->getAgendaPageFullURL(); ?>" method="get">
			<input size="10" type="text" id="swc_date" name="swc_date" value="<?php if (!empty($query['swc_date'])) { echo date("d/m/Y", strtotime($query['swc_date'])); } ?>" readonly="true" placeholder="A partir du">
			<input type="text" id="swc_query" name="swc_query" value="<?php echo $query['swc_query']; ?>" placeholder="Recherche">
			<button type="submit">
				<i class="fa fa-search icon-search"></i>
			</button>
		</form>
		<br />
		<?php
		
		$widget_base_url = $sowprogEventsConfiguration->getSowprogAPIBaseURL() . '/v1';
		$widget_type='oembed';
		$widget_template='basic';
		$widget_id='swc_main';
		
		$agenda_base_url = $sowprogEventsConfiguration->getAgendaPageFullURL();
		
		$swc_event = $query['swc_event'];
		$swc_location = $query['swc_location'];
		$swc_search_by_event_type = $query['swc_search_by_event_type'];
		$swc_search_by_event_style = $query['swc_search_by_event_style'];
		$swc_start_page = $query['swc_start_page'];
		$swc_date = $query['swc_date'];
		$swc_query = $query['swc_query'];
		$count = '20';
		
		global $swc_data;
		
		$widget_url = $widget_base_url.'/'.$account_type.'/'.$account_id.'/widget/'.$widget_type .'/'.$widget_template;
		
		if($swc_event) {
			$params = http_build_query(array(
					'base_agenda_url' => $agenda_base_url,
					'widget_id' => $widget_id));
			$swc_data = json_decode(file_get_contents($widget_url.'/events/'.$swc_event.'?'.$params), true);
		}
		else if($swc_location) {
			$params = http_build_query(array(
					'base_agenda_url'=>$agenda_base_url,
					'widget_id' => $widget_id));
			$swc_data = json_decode(file_get_contents($widget_url.'/locations/'.$swc_location.'?'.$params), true);
		}
		else {
			$params_array = array(
					'base_agenda_url'=>$agenda_base_url,
					'startPage'=>$swc_start_page,
					'count'=>$count,
					'widget_id' => $widget_id);

			if($swc_search_by_event_type) {
				$params_array['event.eventType.id'] = $swc_search_by_event_type;
			}

			if($swc_search_by_event_style) {
				$params_array['event.eventStyle.id'] = $swc_search_by_event_style;
			}

			if($swc_date) {
				$params_array[date] = $swc_date;
			}

			if($swc_query) {
				$params_array[query] = $swc_query;
			}

			$params = http_build_query($params_array);

			$swc_data = json_decode(file_get_contents($widget_url.'/events'.'?'.$params), true);
		}

		if($swc_data[html]) {
			echo $swc_data[html];
		}
		
		echo $sowprogEventsConfiguration->getCodeAfter();
		
		return ob_get_clean();
	}

	function output_widget_javascript($count) {
		$sowprogEventsConfiguration = new SowprogEventsConfiguration();
		if (!$sowprogEventsConfiguration->getDoNotUseFontAwesome()) {
			wp_enqueue_style('font-awesome', plugins_url( '/includes/css/font-awesome.min.css' , __FILE__ ));
		}
		wp_enqueue_style('sowprog-events-style', plugins_url( '/includes/css/sowprog_basic_widget.css' , __FILE__ ));

		wp_enqueue_script('sowprog_events_widget', plugins_url( '/includes/js/sowprog_events_widget.js' , __FILE__ ), array( 'jquery'), false, true);
		wp_localize_script(
			'sowprog_events_widget', 
			'sowprog_events_widget_parameters', 
			array(
				'count' => $count,
				'agendaPageFullURL' => $sowprogEventsConfiguration->getAgendaPageFullURL(),
				'sowprogAPIBaseURL' => $sowprogEventsConfiguration->getSowprogAPIBaseURL(),
				'userID' => $sowprogEventsConfiguration->getUserID(),
				'userType' => $sowprogEventsConfiguration->getUserType()
			) 
		);
		echo '<div id="swc_events_small_div"></div>';
		echo $sowprogEventsConfiguration->getWidgetCode();
	}

	function output_widget($count) {
		$sowprogEventsConfiguration = new SowprogEventsConfiguration();
			
		$account_type = $sowprogEventsConfiguration->getUserType();
		$account_id = $sowprogEventsConfiguration->getUserID();

		if (empty($account_type) || empty($account_id)) {
			return '';
		}

		if (!$sowprogEventsConfiguration->getDoNotUseFontAwesome()) {
			wp_enqueue_style('font-awesome', plugins_url( '/includes/css/font-awesome.min.css' , __FILE__ ));
		}
		wp_enqueue_style('sowprog-events-style', plugins_url( '/includes/css/sowprog_basic_widget.css' , __FILE__ ));

		ob_start();
		
		$widget_base_url = $sowprogEventsConfiguration->getSowprogAPIBaseURL() . '/v1';
		$widget_type='oembed';
		$widget_template='basic';
		$widget_id='swc_widget';

		$agenda_base_url=$sowprogEventsConfiguration->getAgendaPageFullURL();
			
		global $swc_data;

		$widget_url = $widget_base_url.'/'.$account_type.'/'.$account_id.'/widget/'.$widget_type .'/'.$widget_template.'/events/small';
		$params = http_build_query(array(
				'base_agenda_url'=>$agenda_base_url,
				'count'=>$count,
				'widget_id' => $widget_id));
		$swc_data = json_decode(file_get_contents($widget_url.'?'.$params), true);
			
		if($swc_data[html]) {
			?>
<?php 	
echo $swc_data[html];
		}
		
		echo $sowprogEventsConfiguration->getWidgetCode();

		return ob_get_clean();
	}
}

?>