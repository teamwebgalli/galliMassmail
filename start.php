<?php
/**
 *	galliMassmail
 *	Author : Raez Mon | Team Webgalli
 *	Team Webgalli | Elgg developers and consultants
 *	Mail : info@webgalli.com
 *	Web	: http://webgalli.com
 *	Skype : 'team.webgalli'
 *	@package galliMassmail plugin
 *	Licence : GPLv2
 *	Copyright : Team Webgalli 2011-2015
 */
 
elgg_register_event_handler('init', 'system', 'galliMassmail_init');

function galliMassmail_init() {
	elgg_register_admin_menu_item('administer', 'galliMassmail', 'administer_utilities');
	
	$base = elgg_get_plugins_path() . "galliMassmail/actions/galliMassmail";
	elgg_register_action('galliMassmail/compose', "$base/compose.php", 'admin');
	elgg_register_action('galliMassmail/delete', "$base/delete.php", 'admin');
	
	elgg_register_plugin_hook_handler('cron', 'fiveminute', 'galliMassmail_send_mails');
	elgg_register_plugin_hook_handler('register', 'menu:entity', 'galliMassmail_entity_menu_setup');
}	

function galliMassmail_send_mails($hook, $entity_type, $returnvalue, $params){
	$mails = elgg_get_entities_from_metadata(array('types' => 'object', 'subtypes' => 'galliMassmail', 'metadata_name' => 'complete', 'metadata_value' => false ));
	$limit = 25;
	$site = elgg_get_site_entity();
	$site = get_entity($site->guid);
	if ($site && $site->email) {
		$from = $site->email;
	} else {
		$from = 'noreply@' . get_site_domain($site->guid);
	}
	if($mails){
		foreach($mails as $mail){
			$subject = $mail->title;
			$message = $mail->description;
			$offset = $mail->offset;
			$emails = galliMassmail_select_emails($limit, $offset);
			if($emails){
				foreach ($emails as $to) {
					if ($to && is_email_address($to)) {
						elgg_send_email($from, $to, $subject, $message);
					}
				}
				galliMassmail_set_metadata($mail, 'offset', $limit + $offset);
			} else {
				galliMassmail_set_metadata($mail, 'complete', true);
			}
		}	
	}
}	

function galliMassmail_select_emails($limit = 10, $offset =0){
	$dbPrefix = elgg_get_config('dbprefix');
	$query = "SELECT email from {$dbPrefix}users_entity ORDER BY {$dbPrefix}users_entity.guid DESC LIMIT $offset, $limit";
	return get_data($query);
}

function galliMassmail_set_metadata($entity, $md_name, $md_value){
	$ia = elgg_set_ignore_access(true);
	$entity->$md_name = $md_value;
	$entity->save();
	elgg_set_ignore_access($ia);
}

function galliMassmail_entity_menu_setup($hook, $type, $return, $params) {
	if (elgg_in_context('widgets')) {
		return $return;
	}
	$entity = $params['entity'];
	$handler = elgg_extract('handler', $params, false);
	if ($handler != 'galliMassmail') {
		return $return;
	} 
	foreach ($return as $index => $item) {
		if (($item->getName() == 'edit') or ($item->getName() == 'access')) {
			unset($return[$index]);
		}
	}
	if($entity->complete != true){
		$status = elgg_echo('galliMail:status:incomplete');
	} else {
		$status = elgg_echo('galliMail:status:complete');
	}
	$options = array(
		'name' => 'status',
		'text' => $status,
		'href' => false,
		'priority' => 200,
	);
	$return[] = ElggMenuItem::factory($options);
	return $return;
}