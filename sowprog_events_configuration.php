<?php

class SowprogEventsConfiguration {

	function getUserType() {
		return get_option('sowprog_user_type');
	}

	function setUserType($userType) {
		update_option('sowprog_user_type', $userType);
	}

	function getUserID() {
		return get_option('sowprog_user_id');
	}

	function setUserID($userID) {
		update_option('sowprog_user_id', $userID);
	}

	function getUserEmail() {
		return get_option('sowprog_user_email');
	}

	function setUserEmail($userEMail) {
		update_option('sowprog_user_email', $userEMail);
	}

	function getCodeBefore() {
		return stripcslashes(get_option('sowprog_code_before'));
	}
	
	function setCodeBefore($codeBefore) {
		update_option('sowprog_code_before', $codeBefore);
	}
	
	function getCodeAfter() {
		return stripcslashes(get_option('sowprog_code_after'));
	}
	
	function setCodeAfter($codeAfter) {
		update_option('sowprog_code_after', $codeAfter);
	}
	
	function getWidgetCode() {
		return stripcslashes(get_option('sowprog_widget_code'));
	}
	
	function setWidgetCode($widgetCode) {
		update_option('sowprog_widget_code', $widgetCode);
	}
	
	function getDoNotUseFontAwesome() {
		return get_option('sowprog_do_not_use_font_awesome');
	}
	
	function setDoNotUseFontAwesome($doNotUseFontAwesome) {
		update_option('sowprog_do_not_use_font_awesome', $doNotUseFontAwesome);
	}
	
	function getShowAsPage() {
		return get_option('sowprog_show_as_page');
	}
	
	function setShowAsPage($showAsPage) {
		update_option('sowprog_show_as_page', $showAsPage);
	}
	
	function getAgendaPage() {
		return get_option('sowprog_agenda_page');
	}
	
	function getAgendaPageFullURL() {
		return get_home_url().'/'.$this->getAgendaPage().'/';
	}

	function setAgendaPage($agendaPage) {
		update_option('sowprog_agenda_page', $agendaPage);
	}
	
	function getSowprogAPIBaseURL() {
		return 'https://api.sowprog.com';
	}
	
	function getCurrentURL() {
		/*** check for https ***/
		$protocol = $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
		/*** return the full address ***/
		return $protocol.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	}
	
	function getCurrentURLNoParameters() {
		return strtok($this->getCurrentURL(), '?');
	}
	

	function initialize($userEMail, $agendaPage, $codeBefore, $codeAfter, $widgetCode, $doNotUseFontAwesome, $showAsPage) {
		$this->setUserEmail($userEMail);
		$this->setAgendaPage($agendaPage);
		$this->setCodeBefore($codeBefore);
		$this->setCodeAfter($codeAfter);
		$this->setWidgetCode($widgetCode);
		$this->setDoNotUseFontAwesome($doNotUseFontAwesome);
		$this->setShowAsPage($showAsPage);
		
		
		$url = $this->getSowprogAPIBaseURL() . '/rest/v1/user/account?user_email=' . $userEMail;
		$headers = array(
				'Accept' => 'application/json',
				'Content-Type' => 'application/json');
			
		$result = wp_remote_get( $url, array( 'headers' => $headers , 'timeout' => 120) );
		if( is_wp_error( $result ) ) {
			// Warn user
			$this->setUserType('');
			$this->setUserID('');
			return;
		}
		if ( 200 != $result['response']['code'] ) {
			// Warn user
			$this->setUserType('');
			$this->setUserID('');
			return;
		}
		
		$json = $result['body'];
		$data = json_decode( $json );
		
		if ($data->user->role == 'CHANNEL_CONSUMER') {
			$url = $this->getSowprogAPIBaseURL() . '/rest/v1/user/channel?user_email=' . $userEMail;
			$headers = array(
					'Accept' => 'application/json',
					'Content-Type' => 'application/json');
				
			$result = wp_remote_get( $url, array( 'headers' => $headers , 'timeout' => 120) );
			if( is_wp_error( $result ) ) {
				// Warn user
				$this->setUserType('');
				$this->setUserID('');
				return;
			}
			if ( 200 != $result['response']['code'] ) {
				// Warn user
				$this->setUserType('');
				$this->setUserID('');
				return;
			}
			$json = $result['body'];
			$data = json_decode( $json );
			
			$this->setUserType('channel');
			$this->setUserID($data->channel->sowprogId->id);	
		} else {
			$this->setUserType('user');
			$this->setUserID($data->user->sowprogId->id);
		}

	}

	function form() {

		if ('POST' == $_SERVER['REQUEST_METHOD']) {
			$this->initialize(
					$_POST['sowprog_user_email'], 
					$_POST['sowprog_agenda_page'], 
					$_POST['sowprog_code_before'], 
					$_POST['sowprog_code_after'],
					$_POST['sowprog_widget_code'],
					$_POST['sowprog_do_not_use_font_awesome'],
					$_POST['sowprog_show_as_page']
				);
		}
		?>
<div class="wrap">
	<h2>Configuration SOWPROG</h2>
	<form class="add:the-list: validate" method="post" enctype="multipart/form-data">
		<fieldset>
			<p>
				<label for="sowprog_user_email">Login sowprog</label>
				<br>
				<input name="sowprog_user_email" id="sowprog_user_email" value="<?php echo $this->getUserEmail() ?>" />
			</p>
			<p>
				<label for="sowprog_agenda_page">Page agenda</label>
				<br>
				<input name="sowprog_agenda_page" id="sowprog_agenda_page" value="<?php echo $this->getAgendaPage() ?>" />
			</p>
			<p>
				<input type="checkbox" name="sowprog_do_not_use_font_awesome" value="1" <?php if ($this->getDoNotUseFontAwesome()) { ?> checked="checked" <?php } ?>>&nbsp;Ne pas inclure Font Awesome. Cochez cette case uniquement si votre thème inclus déjà Font Awesome<br>
			</p>
			<p>
				<input type="checkbox" name="sowprog_show_as_page" value="1" <?php if ($this->getShowAsPage()) { ?> checked="checked" <?php } ?>>&nbsp;Afficher comme une page et non un post.<br>
			</p>
			<p>
				<label for="sowprog_code_before">HTML / CSS / JS à placer avant</label>
				<br>
				<textarea rows="10" cols="50" name="sowprog_code_before" id="sowprog_code_before">
					<?php echo $this->getCodeBefore() ?>
				</textarea>
			</p>
			<p>
				<label for="sowprog_code_after">HTML / CSS / JS à placer après</label>
				<br>
				<textarea rows="10" cols="50" name=sowprog_code_after id="sowprog_code_after">
					<?php echo $this->getCodeAfter() ?>
				</textarea>
			</p>
			<p>
				<label for="sowprog_widget_code">HTML / CSS / JS à placer après le Widget</label>
				<br>
				<textarea rows="10" cols="50" name=sowprog_widget_code id="sowprog_widget_code">
					<?php echo $this->getWidgetCode() ?>
				</textarea>
			</p>
		</fieldset>
		<p class="submit">
			<input type="submit" class="button" name="submit" value="Configurer" />
		</p>
	</form>
	<p><span>ID utilisateur : <?php echo $this->getUserID() ?></span></p>
	<p><span>Type utilisateur : <?php echo $this->getUserType() ?></span></p>
</div>
<?php
	}
}

function sowprog_events_admin_menu() {
	require_once ABSPATH . '/wp-admin/admin.php';
	$sowprogEventsConfiguration = new SowprogEventsConfiguration();
	add_management_page('sowprog_events_configuration.php', 'SOWPROG', 'manage_options', __FILE__, array(&$sowprogEventsConfiguration, 'form'));
}

add_action('admin_menu', 'sowprog_events_admin_menu');

?>