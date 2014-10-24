<?php
/**
 * Processes user clicks on a url in a recommender widget,
 * saves the configuration used, user and object guid,  in the
 * ElggObject subtype 'recommender_usage_log', for later analysis...
 * And than, redirects to the page showing default view for the  object that was clicked
 */

$configurationguid = get_input('configguid');
$configuration = get_entity($configurationguid);
$objectguid = get_input('objectguid');
$userguid = elgg_get_logged_in_user_guid();
$objecttype = get_input('objecttype');

$usagelog = new ElggObject();
$usagelog->subtype = 'recommender_usage_log';
$usagelog->access_id = ACCESS_LOGGED_IN;//ACCESS_PUBLIC
$usagelog->userguid = $userguid;
$usagelog->objectguid = $objectguid;
$usagelog->configuration_name = $configuration->name;
$usagelog->configuration_description =  $configuration->description;
$usagelog->configuration_dataset = $configuration->dataset;
$usagelog->configuration_visualization = $configuration->visualization;
$usagelog->configuration_algorithm = $configuration->algorithm;
$usagelog->configuration_recommender_type = $configuration->recommender_type;
$usagelog->configuration_guid = $configuration->guid;	
$usagelog->save();	
$file_dir = "{$CONFIG->wwwroot}recommender";
$link_url = "$file_dir/viewobject/$objectguid/$objecttype/$configurationguid";
forward($link_url);


