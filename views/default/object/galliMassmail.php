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

$full = elgg_extract('full_view', $vars, FALSE);
$galliMassmail = elgg_extract('entity', $vars, FALSE);

if (!$galliMassmail) {
	return;
}

$owner = $galliMassmail->getOwnerEntity();
$owner_icon = elgg_view_entity_icon($owner, 'tiny');

$description = elgg_view('output/longtext', array('value' => $galliMassmail->description, 'class' => 'pbl'));

$owner_link = elgg_view('output/url', array(
	'href' => $owner->getURL(),
	'text' => $owner->name,
	'is_trusted' => true,
));
$author_text = elgg_echo('byline', array($owner_link));

$date = elgg_view_friendly_time($galliMassmail->time_created);

$metadata = elgg_view_menu('entity', array(
	'entity' => $vars['entity'],
	'handler' => 'galliMassmail',
	'sort_by' => 'priority',
	'class' => 'elgg-menu-hz',
));

$subtitle = "$author_text $date";

if (elgg_in_context('widgets')) {
	$metadata = '';
}

if ($full && !elgg_in_context('gallery')) {

	$params = array(
		'entity' => $galliMassmail,
		'title' => false,
		'metadata' => $metadata,
		'subtitle' => $subtitle,
	);
	$params = $params + $vars;
	$summary = elgg_view('object/elements/summary', $params);
	
	
	$body = <<<HTML
<div class="galliMassmail elgg-content mts">
	$description
</div>
HTML;

	echo elgg_view('object/elements/full', array(
		'entity' => $galliMassmail,
		'icon' => $owner_icon,
		'summary' => $summary,
		'body' => $body,
	));

} elseif (elgg_in_context('gallery')) {
	echo <<<HTML
<div class="galliMassmail-gallery-item">
	<h3>$galliMassmail->title</h3>
	<p class='subtitle'>$owner_link $date</p>
</div>
HTML;
} else {
	$excerpt = elgg_get_excerpt($galliMassmail->description);
	if ($excerpt) {
		$excerpt = "$excerpt";
	}

	$params = array(
		'entity' => $galliMassmail,
		'metadata' => $metadata,
		'subtitle' => $subtitle,
		'content' => $excerpt,
	);
	$params = $params + $vars;
	$body = elgg_view('object/elements/summary', $params);
	
	echo elgg_view_image_block($owner_icon, $body);
}
