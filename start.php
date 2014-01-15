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
	$site = elgg_get_site_entity();
	if ($site && $site->email) {
		$from = $site->email;
	} else {
		$from = 'noreply@' . get_site_domain($site->guid);
	}
	$limit = 25;
	if($mails){
		foreach($mails as $mail){
			$subject = $mail->title;
			$message = $mail->description;
			$offset = (int) $mail->offset;
			$message_type = $mail->message_type;
			$increment = (int) $limit + $offset;
			if($message_type == 'elggmail'){
				$guids = galliMassmail_select_users('guid', $limit, $offset);
				if($guids){
					foreach ($guids as $guid) {
						$recipient_guid = (int) $guid->guid;
						if ($recipient_guid) {
							// Internal message
							// Adopted from messages' plugin
							$sender_guid = (int) $mail->owner_guid;
							// Initialise 2 new ElggObject
							$message_to = new ElggObject();
							$message_to->subtype = "messages";
							$message_to->owner_guid = $recipient_guid;
							$message_to->container_guid = $recipient_guid;
							$message_to->title = $subject;
							$message_to->description = $message;
							$message_to->toId = $recipient_guid; // the user receiving the message
							$message_to->fromId = $sender_guid; // the user receiving the message
							$message_to->readYet = 0; // this is a toggle between 0 / 1 (1 = read)
							$message_to->hiddenFrom = 0; // this is used when a user deletes a message in their sentbox, it is a flag
							$message_to->hiddenTo = 0; // this is used when a user deletes a message in their inbox
							$message_to->msg = 1;
							// Save the copy of the message that goes to the recipient
							$message_to->access_id = ACCESS_PRIVATE;
							$message_to->save();
						}
					}
					galliMassmail_set_metadata($mail, 'offset', $increment);
				} else {
					galliMassmail_set_metadata($mail, 'complete', true);
				}
			} else {
				$emails = galliMassmail_select_users('email', $limit, $offset);
				if($emails){
					foreach ($emails as $email) {
						$to = $email->email;
						if ($to && is_email_address($to)) {
							elgg_send_email($from, $to, $subject, $message);
						}
					}
					$increment = (int) $limit + $offset;
					galliMassmail_set_metadata($mail, 'offset', $increment);
				} else {
					galliMassmail_set_metadata($mail, 'complete', true);
				}
			}
		}	
	}
}	

function galliMassmail_select_users($select = 'email', $limit = 10, $offset =0){
	$dbPrefix = elgg_get_config('dbprefix');
	$query = "SELECT $select from {$dbPrefix}users_entity ORDER BY {$dbPrefix}users_entity.guid ASC LIMIT $offset, $limit";
	return get_data($query);
}

function galliMassmail_set_metadata($entity, $md_name, $md_value){
	$ia = elgg_set_ignore_access(true);
	create_metadata($entity->guid, $md_name, $md_value, "integer", 0, 2, false);
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