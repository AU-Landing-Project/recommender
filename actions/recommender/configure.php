<?php
/**
 * This action processes the Recommender Configure form 
 */

$error = FALSE;
$forward_url = "{$CONFIG->wwwroot}admin/plugin_settings/recommender";
$error_forward_url = $forward_url;
$user = elgg_get_logged_in_user_entity();
$guid = get_input('guid');

if ($guid) {
	$entity = get_entity($guid);
	if (elgg_instanceof($entity, 'object', 'recommender_configuration') && $entity->canEdit()) {
		$configuration = $entity;
	} else {
		register_error(elgg_echo('recommender:error:configuration:notfound'));
		forward(get_input('forward', REFERER));
	}
} else {
	$configuration = new ElggObject();
	$configuration->subtype = 'recommender_configuration';
}

$values = get_form_vars_structure();
$required = array('name', 'description', 'dataset','visualization');

foreach ($values as $name => $default) {
	$value = get_input($name, $default);

	if (in_array($name, $required) && empty($value)) {
		$error = elgg_echo('recommender:error:missing'). $name;
	}

	if ($error) {
		break;
	}

	switch ($name) {
		// don't try to set the guid
		case 'guid':
			unset($values['guid']);
			break;

		default:
			$values[$name] = $value;
			break;
	}
}


if (!$error) {
	foreach ($values as $name => $value) {
		if (FALSE === ($configuration->$name = $value)) {
			$error = elgg_echo("recommender:error:save:  $name=$value"); 
			break;
		}
	}
}

if (!$error) {
	if ($configuration->save()) {
		system_message(elgg_echo('recommender:message:configuration:save'));
		forward($forward_url); //todo
	} else {
		register_error(elgg_echo('recommender:error:save'));
		forward($error_forward_url);
	}
} else {
	register_error($error);
	forward($error_forward_url);
}