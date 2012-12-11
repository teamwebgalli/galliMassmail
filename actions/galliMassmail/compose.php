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

$subject = get_input('subject');
$message = get_input('message');

if(!$subject or !$message){
	register_error('galliMassmail:neededfields');
	forward(REFERER);
}

$massmail = new ElggObject;
$massmail->subtype = "galliMassmail";
$massmail->title = $subject;
$massmail->description = $message;
$massmail->access_id = 2;
$massmail->complete = false;
$massmail->offset = 0;
if ($massmail->save()) {
	system_message(elgg_echo('galliMassmail:save:success'));
} else {
	register_error('galliMassmail:save:failed');
}

forward(REFERER);