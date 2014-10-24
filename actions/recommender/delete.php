<?php
/**
 * Delete Configuration action - deletes a configuration
 */

$configuration_guid = get_input('guid');
$configuration = get_entity($configuration_guid);

if (elgg_instanceof($configuration, 'object', 'recommender_configuration') && $configuration->canEdit()) {

	if ($configuration->delete()) {
		system_message(elgg_echo('recommender:message:configuration:delete'));
	} else {
		register_error(elgg_echo('recommender:error:configuration:delete'));
	}
} else {
	register_error(elgg_echo('recommender:error:configuration:notfound')); 
}

forward("{$CONFIG->wwwroot}admin/plugin_settings/recommender");