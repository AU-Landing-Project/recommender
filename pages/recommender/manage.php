<?php

/**
 * This is a Plugin Settings Page for recommender, implemeted features:
 * Initialization of the Tag Recommendations
 * Add/Create new configuration
 * list and Edit existing configurations
 * Configure the asynch processing options for configurations setup in asynch mode
 */
//echo '<br>';
$file_dir = "{$CONFIG->wwwroot}recommender";
////
$init_tag_recommendadtions_url = "action/recommender/initialize_tag_recommendations";
$init_tag_recommendadtions_link = elgg_view('output/confirmlink', array(
	'href' => $init_tag_recommendadtions_url,
	'text' => elgg_echo('recommender:configuration:init_tag_recommendadtions'),
	'class' => 'elgg-button elgg-button-delete elgg-state-disabled'
));
echo $init_tag_recommendadtions_link;
echo '<br><br>';

$link_url = "$file_dir/add";
$edit_link = elgg_view('output/url', array(
	'href' => $link_url,
	'text' => elgg_echo('recommender:configuration:add'),
	'class' => 'elgg-button-submit',
));
echo $edit_link;

$configurations =  get_configurations_array();
$c=1;
foreach ($configurations as $guid=>$name){
	if ($c==1){
		echo "<h4>";
		echo elgg_echo('recommender:configuration:edit');
		echo "</h4><br />";
	}
	$link_url = "$file_dir/edit/$guid";
	$edit_link = elgg_view('output/url', array(
		'href' => $link_url,
		'text' => elgg_echo($name),
	));

	echo '&nbsp;'. $c++.'. '.$edit_link. '<br>';
}



echo "<div>";
echo "<br />";
echo "<h4>";
echo elgg_echo("recommender:queue:label");
echo "</h4><br />";
echo elgg_echo('recommender:queue:period');
echo "<br />";
echo elgg_view(	'input/dropdown',
array(
                      'name' => 'params[processing_period]',
                      'options_values' => get_cron_periods(),
                      'value' => $vars['entity']->processing_period,
)
);
echo "<br />";
echo elgg_echo('recommender:queue:numofrecords');
echo "<br />";
echo elgg_view(	'input/dropdown',
array(
                      'name' => 'params[number_reccords]',
                      'options_values' => get_number_of_records_to_process(),
                      'value' => $vars['entity']->number_reccords,
)
);

echo "</div>";

