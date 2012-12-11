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
?>
<div>
	<?php
		echo elgg_echo('galliMassmail:subject');
		echo elgg_view('input/text', array('name' => 'subject', 'value' => $name));
	?>
</div>
<div>
	<?php	
		echo elgg_echo('galliMassmail:message');
		echo elgg_view('input/plaintext', array('name' => 'message', 'value' => $message));
	?>	
</div>	
<div>
	<?php
		echo elgg_view('input/submit', array('name' => 'submit', 'value' => elgg_echo('galliMassmail:submit')));
	?>
</div>	
