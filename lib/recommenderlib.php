<?php



/**
 * get_visualizations() returns list containing visualization implemantations  found in the 'recommender/classes/visualizations'
 * every visualization implementation should be placed in this same dir in order to appear as one of the choices when configuration is created and/or configured 
 */
function get_visualizations(){
	$subdir='recommender/classes/visualizations';
	return get_classes_selection($subdir);
}

/** 
 * get_datasets() returns list containing dataset implemantations  found in the 'recommender/classes/datasets'
 * every dataset implementation should be placed in this same dir in order to appear as one of the choices when configuration is created and/or configured 
 */


function get_datasets(){
	$subdir='recommender/classes/datasets';
	return get_classes_selection($subdir);
}



function get_classes_selection($subdir){
	$dir= elgg_get_plugins_path() . $subdir;
	$classes = elgg_get_file_list($dir, array(), array(), array('.php'));
	$sellection=array();
	foreach ($classes as $class) {
		$class = basename($class, '.php');
		$sellection[$class]= $class;
	}
	return $sellection;
}



function get_selected_cron_period(){
	$allperiods = get_cron_periods();
	$period = elgg_get_plugin_setting("processing_period");
	if (isset($period )){
		$period = 	$allperiods[$period];
	}
	else
	{
		$period = ($allperiods[5]);
	}
	return $period;
}

function get_selected_number_of_records(){
	$allnumbers = get_number_of_records_to_process();
	$number = elgg_get_plugin_setting("number_reccords");
	if (isset($number )){
		$number = 	$allnumbers[$period];
	}
	else
	{
		$number = ($allnumbers[1]);
	}
	return $number;
}


function get_cron_periods(){
	return array(
	1 => 'minute',
	2 => 'fiveminute',
	3 => 'fifteenmin',
	4 => 'halfhour',
	5 => 'hourly',
	6 => 'daily',
	7 => 'weekly',
	8 => 'monthly',
	9 => 'yearly',
	10 => 'reboot',
	);
}

function get_number_of_records_to_process(){
	return array(
	0 => 'All',
	1 => '20',
	2 => '40',
	3 => '100',
	4 => '300',
	5 => '600',
	6 => '1000',
	7 => '3000',
	);
}

function get_no_or_yes(){
	return array(
	0 => 'No',
	1 => 'Yes',
	);
}


/**
 * 
 * recommender_render_link($configguid, $objectguid, $objecttype, $text) renders a recommended object link used in visualizations, 
 * this link is redirectiong to the processclick action, wher the click on the link is processed and information stored in the DB 
 * @param $configguid - widget configuration id - defines used configuration and in turn corresponding dataset and visualization 
 * @param $objectguid - id of the object reprezented by the link e.g. userid 
 * @param $objecttype - type of the Object reprezented by the link e.g. user
 * @param $text - text to be displayed e.g. user name
 */


function recommender_render_link($configguid, $objectguid, $objecttype, $text){
	$action_url = "action/recommender/processclick?configguid=$configguid&objectguid=$objectguid&objecttype=$objecttype";
	return elgg_view('output/url', array(
				'href' => $action_url,
				'text' => elgg_echo($text),
				'is_action' => true,
	));
}



function recommender_page_handler($page){
	$page_type = $page[0];

	switch ($page_type) {
		case 'add':
			$content = get_widget_configuration_form($page_type);
			break;
		case 'edit':
			$content = get_widget_configuration_form($page_type, $page[1]);
			break;
		case 'viewobject':
			$vars['objectguid'] = $page[1];
			$vars['objecttype'] = $page[2];
			$vars['configurationguid'] = $page[3];
			$content['title'] = 'Recommender view';
			$content['content'] = elgg_view('recommender/viewobject',$vars);
			break;
		default:
			$file_dir = elgg_get_plugins_path() . 'recommender/pages/recommender';
			include "$file_dir/temp.php";
			return true;
	}

	if (($page_type=="add" || $page_type=="edit")) {
		elgg_set_context('admin');
		//elgg_push_context('admin');
		admin_pagesetup();
		echo elgg_view_page($content['title'], $content['content'],"admin");
		elgg_pop_context();
	}
	else{
		echo elgg_view_page($content['title'], $content['content']);
	};
	return true;
}



/**
 * 
 * process_queued_ratings - processes ratings queued for asynch processing
 * @param $maxrecords - max number of records to be processed in one pass,
 * default is 0 and it means: process all records in the queue
 */

function process_queued_ratings($maxrecords=0){
	set_time_limit(0);
	$configurations = get_configurations();
	foreach ($configurations as $config){
		$dataset=$config->dataset;
		$recommender = new Recommender(new $dataset(), Recommender::ALGORITHM_EUCLIDEAN, Recommender::USER_BASED, false, 0, 0);
		$recommender->process_queue($maxrecords);
	}
	set_time_limit(30);	
}

/**
 * 
 * recommender_cron_handler - used for asynch processing, processing parameters configurable on the pluggin setings form
 * @param $hook
 * @param $type
 * @param $params
 * @param $returnvalue
 */


function recommender_cron_handler($hook, $type, $params, $returnvalue){
	process_queued_ratings(get_selected_number_of_records());
}

/**
 * 
 * register_dataset_plugin_hook_handlers()- registers plugin hook handlers for every unque dataset used in the recommender configurations.
 * handler are registered using corresponding  hook_name and hook_type as defined by the dataset in question.
 * the recommender plugin hooks should be triggered when certain rating happens in order for the rating to get processed by the recommender  
 */

function register_dataset_plugin_hook_handlers(){
	$configurations = get_configurations();
	
	if (is_null($configurations)) return;
	
	foreach ($configurations as $config){
		$dataset=$config->dataset;
		$ds_hook_name = $dataset::get_hook_name();
		$ds_hook_type = $dataset::get_hook_type();
		if(isset($ds_hook_name) && isset($ds_hook_type)){
			$name_type_array[]= array($ds_hook_name,$ds_hook_type);
			$unique_array[]= $ds_hook_name."***".$ds_hook_type;
		}
	}

	if ($unique_array != null){
		$unique_array=array_unique($unique_array);
		reset($unique_array);
		foreach ($unique_array as $key => $value){
			$hook_name = $name_type_array[$key][0];
		$hook_type = $name_type_array[$key][1];
		elgg_register_plugin_hook_handler($hook_name,$hook_type,"recommender_plugin_hook_handler");
	}
	}
}

/**
 * 
 * recommender_plugin_hook_handler($hook, $type, $returnvalue, $params)- handles  hooks triggered by recomendations
 * handler will search for the first configuration whos dataset defines given hook and type arguments, it will then proces the rating
 * in synch or asynch way depending on the configuration settings 
 * 
 * @param $hook
 * @param $type
 * @param $returnvalue
 * @param $params
 */


function recommender_plugin_hook_handler($hook, $type, $returnvalue, $params){
		
	$configurations = get_configurations();
	
	if (is_null($configurations)) return;
	
	foreach ($configurations as $config){
		$dataset=$config->dataset;
		$ds_hook_name = $dataset::get_hook_name();
		$ds_hook_type = $dataset::get_hook_type();
		if(isset($ds_hook_name)&&isset($ds_hook_type)){
			if ($hook==$ds_hook_name && $type==$ds_hook_type){
				$processingtype = $config->processing_type;
				$processingtypes = Recommender::get_processing_types();
				$processingtype = $processingtypes[$processingtype];
				$recommender = new Recommender(new $dataset(), Recommender::ALGORITHM_EUCLIDEAN, Recommender::USER_BASED, false, 0, 0, $processingtype);
				$recommender->import_rating_from_hook($params);
			}
		}
	}
}

function get_configurations(){
	$options = array(
		'type' => 'object',
		'subtype' => 'recommender_configuration',
		'limit'=>0,
	);

	return  elgg_get_entities_from_metadata($options);
}


function get_configurations_array(){
	$configurations = get_configurations();
	$retval = array();
	foreach ($configurations as $configuration){
		$retval[$configuration->guid]= $configuration->name;
	}
	asort($retval,SORT_STRING);
	return $retval;
}

function get_widget_configuration_form($page, $guid = 0){
	gatekeeper();
	$return = array();
	$vars = array();
	$vars['id'] = 'recommender_configuration_edit';
	$vars['name'] = 'recommender_configuration';

	if ($page == 'edit') {
		$configuration = get_entity((int)$guid);
		$title = elgg_echo('Recommender Configuration');
		if (elgg_instanceof($configuration, 'object', 'recommender_configuration') && $configuration->canEdit()) {
			$vars['entity'] = $configuration;
			$title .= ": \"$configuration->name\"";
			$body_vars = prepare_form_vars($configuration);
			$content = elgg_view_form('recommender/configure', $vars, $body_vars);
		} else {
			$content = elgg_echo('recommender:error:configuration:edit');
		}
	} else {
		$body_vars = prepare_form_vars(NULL);
		$title = elgg_echo('New Recommender Configuration');
		$content = elgg_view_form('recommender/configure', $vars, $body_vars);
	}
	$return['title'] = $title;
	$return['content'] = $content;
	return $return;
}

function prepare_form_vars($post = NULL) {

	$values = get_form_vars_structure();

	if ($post) {
		foreach (array_keys($values) as $field) {
			if (isset($post->$field)) {
				$values[$field] = $post->$field;
			}
		}
	}
	return $values;
}

function get_form_vars_structure(){
	return 	 array(
		'name' => NULL,
		'description' => NULL,
		'dataset' => NULL,
		'access_id' => ACCESS_LOGGED_IN,
		'visualization' => NULL,
		'algorithm' => Recommender::ALGORITHM_EUCLIDEAN,
		'recommender_type' => Recommender::ITEM_BASED,
		'processing_type' => Recommender::PROCESSING_SYNCHRONOUS,
		'guid' => NULL,
	);
}

function gr_compare($a,$b){

	$value_a=(float)$a["rank"];
	$value_b=(float)$b["rank"];

	return compare($value_a, $value_b);
}

function compare($value_a, $value_b){
	if ($value_a > $value_b) return -1;
	if ($value_a < $value_b) return 1;
	return 0;
}


// test
 function load_testdata(){
 set_time_limit(0);
 $recommender = new Recommender(new Movies(),Recommender::ALGORITHM_EUCLIDEAN, Recommender::ITEM_BASED, false, 3, 3);
 	$dataset = $recommender->get_data_set();
	$dataset->delete_ratings();
	$dataset->toggle_reverse();
	$dataset->delete_ratings();
	$dataset->delete_aggregates();
	$dataset->toggle_reverse();
	$dataset->delete_aggregates();
 
 $recommender->add_rating('Lisa Rose', 'Lady in the water', 2.5);
 $recommender->add_rating('Lisa Rose', 'Snakes on a plane', 3.5);
 $recommender->add_rating('Lisa Rose', 'Just my luck', 3);
 $recommender->add_rating('Lisa Rose', 'Superman returns', 3.5);
 $recommender->add_rating('Lisa Rose', 'You me and Dupree', 2.5);
 $recommender->add_rating('Lisa Rose', 'The night listener', 3);

 $recommender->add_rating('Gene Seymour', 'Lady in the water', 3);
 $recommender->add_rating('Gene Seymour', 'Snakes on a plane', 3.5);
 $recommender->add_rating('Gene Seymour', 'Just my luck', 1.5);
 $recommender->add_rating('Gene Seymour', 'Superman returns', 5);
 $recommender->add_rating('Gene Seymour', 'The night listener', 3);
 $recommender->add_rating('Gene Seymour', 'You me and Dupree', 3.5);

 $recommender->add_rating('Michael Phillips', 'Lady in the water', 2.5);
 $recommender->add_rating('Michael Phillips', 'Snakes on a plane', 3);
 $recommender->add_rating('Michael Phillips', 'Superman returns', 3.5);
 $recommender->add_rating('Michael Phillips', 'The night listener', 4);

 $recommender->add_rating('Claudia Puig', 'Snakes on a plane', 3.5);
 $recommender->add_rating('Claudia Puig', 'Just my luck', 3);
 $recommender->add_rating('Claudia Puig', 'The night listener', 4.5);
 $recommender->add_rating('Claudia Puig', 'Superman returns', 4);
 $recommender->add_rating('Claudia Puig', 'You me and Dupree', 2.5);

 $recommender->add_rating('Mick LaSalle', 'Lady in the water', 3);
 $recommender->add_rating('Mick LaSalle', 'Snakes on a plane', 4);
 $recommender->add_rating('Mick LaSalle', 'Just my luck', 2);
 $recommender->add_rating('Mick LaSalle', 'Superman returns', 3);
 $recommender->add_rating('Mick LaSalle', 'The night listener', 3);
 $recommender->add_rating('Mick LaSalle', 'You me and Dupree', 2);

 $recommender->add_rating('Jack Matthews', 'Lady in the water', 3);
 $recommender->add_rating('Jack Matthews', 'Snakes on a plane', 4);
 $recommender->add_rating('Jack Matthews', 'The night listener', 3);
 $recommender->add_rating('Jack Matthews', 'Superman returns', 5);
 $recommender->add_rating('Jack Matthews', 'You me and Dupree', 3.5);

 $recommender->add_rating('Toby', 'Snakes on a plane', 4.5);
 $recommender->add_rating('Toby', 'You me and Dupree', 1);
 $recommender->add_rating('Toby', 'Superman returns', 4);
	set_time_limit(30);	
 }

/*
 function load_testdata_set2(){
 $recommender = new Recommender(new Movies(),Recommender::ALGORITHM_EUCLIDEAN, Recommender::ITEM_BASED, false, 3, 3);
 $recommender->add_rating('Lisa Rose', 'Lady in the water', 1);
 $recommender->add_rating('Lisa Rose', 'Snakes on a plane', 1);
 $recommender->add_rating('Lisa Rose', 'Just my luck', 1);
 $recommender->add_rating('Lisa Rose', 'Superman returns', 1);
 $recommender->add_rating('Lisa Rose', 'You me and Dupree', 1);
 $recommender->add_rating('Lisa Rose', 'The night listener', 1);

 $recommender->add_rating('Gene Seymour', 'Lady in the water', 1);
 $recommender->add_rating('Gene Seymour', 'Snakes on a plane', 1);
 $recommender->add_rating('Gene Seymour', 'Just my luck', 1);
 $recommender->add_rating('Gene Seymour', 'Superman returns', 1);
 $recommender->add_rating('Gene Seymour', 'The night listener', 1);
 $recommender->add_rating('Gene Seymour', 'You me and Dupree', 1);

 $recommender->add_rating('Michael Phillips', 'Lady in the water', 1);
 $recommender->add_rating('Michael Phillips', 'Snakes on a plane', 1);
 $recommender->add_rating('Michael Phillips', 'Superman returns', 1);
 $recommender->add_rating('Michael Phillips', 'The night listener', 1);

 $recommender->add_rating('Claudia Puig', 'Snakes on a plane', 1);
 $recommender->add_rating('Claudia Puig', 'Just my luck', 1);
 $recommender->add_rating('Claudia Puig', 'The night listener', 1);
 $recommender->add_rating('Claudia Puig', 'Superman returns', 1);
 $recommender->add_rating('Claudia Puig', 'You me and Dupree', 1);

 $recommender->add_rating('Mick LaSalle', 'Lady in the water', 1);
 $recommender->add_rating('Mick LaSalle', 'Snakes on a plane', 1);
 $recommender->add_rating('Mick LaSalle', 'Just my luck', 1);
 $recommender->add_rating('Mick LaSalle', 'Superman returns', 1);
 $recommender->add_rating('Mick LaSalle', 'The night listener', 1);
 $recommender->add_rating('Mick LaSalle', 'You me and Dupree', 1);

 $recommender->add_rating('Jack Matthews', 'Lady in the water', 1);
 $recommender->add_rating('Jack Matthews', 'Snakes on a plane', 1);
 $recommender->add_rating('Jack Matthews', 'The night listener', 1);
 $recommender->add_rating('Jack Matthews', 'Superman returns', 1);
 $recommender->add_rating('Jack Matthews', 'You me and Dupree', 1);

 $recommender->add_rating('Toby', 'Snakes on a plane', 1);
 $recommender->add_rating('Toby', 'You me and Dupree', 1);
 $recommender->add_rating('Toby', 'Superman returns', 1);

 }
 */

 function delete_all_entities($subtype){
	 $options = array(
		 'type' => 'object',
		 'subtype' => $subtype,
		 'limit'=>0,
	 );
	 $entities = elgg_get_entities_from_metadata($options);
	 foreach ($entities as $item) {
		 $item->delete();
	 }
 }
 
?>