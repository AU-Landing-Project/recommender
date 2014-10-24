<?php

/**
 * Recommender
 *
 * @author Aleksandar Giljanovic
 */

elgg_register_event_handler('init', 'system', 'recommender_init');       

function recommender_init() {
	
	$plug_path =  elgg_get_plugins_path();
	elgg_register_classes($plug_path . 'recommender/classes/datasets');	
	elgg_register_classes($plug_path . 'recommender/classes/visualizations');
	
	elgg_register_library('recommenderlib', $plug_path . 'recommender/lib/recommenderlib.php');
	elgg_register_library('tagsandvectorslib', $plug_path . 'recommender/lib/tagsandvectorslib.php');
	elgg_load_library('recommenderlib');
	elgg_load_library('tagsandvectorslib');
	
	elgg_register_widget_type('recommender', 'Recommender', 'The Recommender widget','all',true);
	elgg_register_widget_type('recommended_tags', 'Recommended Tags', 'The Tags Recommender widget','all',false);	

	$action_path = $plug_path . 'recommender/actions/recommender';
	elgg_register_action('recommender/configure',"$action_path/configure.php",'admin');
	elgg_register_action('recommender/delete',"$action_path/delete.php",'admin');
	elgg_register_action('recommender/processclick',"$action_path/processclick.php",'logged_in');
	elgg_register_action('recommender/initialize_tag_recommendations',"$action_path/initialize_tag_recommendations.php",'logged_in');

	elgg_register_page_handler('recommender', 'recommender_page_handler');
	elgg_register_event_handler('create', 'annotation', "blog_like_handler");
	register_dataset_plugin_hook_handlers();
	elgg_register_plugin_hook_handler("cron",get_selected_cron_period(), "recommender_cron_handler");
	//cleanup();
	//load_testdata();
}


function blog_like_handler($event,$type,$object){
	if($object){
		$entity = get_entity($object->entity_guid);
		$subtype = get_subtype_from_id($entity->subtype);
		if($object->name=='likes'&& $subtype=='blog'){
			$params = array("action_type"=>$object->name,
							"subtype"=>$subtype,
							"subject_guid"=>$object->owner_guid,
							"object_guid"=>$object->entity_guid,);
			
			elgg_trigger_plugin_hook('blog', 'likes',$params);
		}
	}
	
}

/*
function cleanup(){
	//	process_queued_ratings(0);
	delete_all_entities('rating');
	delete_all_entities('ratings_queue');
	delete_all_entities('aggregated_similarity');
	delete_all_entities('recommender_usage_log');
//	load_testdata();
//	load_testdata_set2();
	
}
*/?>
