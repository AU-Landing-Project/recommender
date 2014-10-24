<?php
/**
 *This form is used to Add/Edit Recommender Configuration
 *
 */

$delete_link = '';

if ($vars['guid']) {
	$delete_url = "action/recommender/delete?guid={$vars['guid']}";
	$delete_link = elgg_view('output/confirmlink', array(
		'href' => $delete_url,
		'text' => elgg_echo('recommender:configuration:delete'),
		'class' => 'elgg-button elgg-button-delete elgg-state-disabled'
	));
}

$save_button = elgg_view('input/submit', array('value' => elgg_echo('Save Configuration')));//TODO

$name_label = elgg_echo('recommender:configuration:name');
$name_input = elgg_view('input/text', array(
	'name' => 'name',
	'id' => 'configuration_title',
	'value' => $vars['name']
));


$description_label = elgg_echo('recommender:configuration:description');
$description_input = elgg_view('input/text', array(
	'name' => 'description',
	'id' => 'configuration_description',
	'value' => $vars['description']
));

$dataset_label = elgg_echo('recommender:configuration:dataset:name');
$dataset_input = elgg_view('input/dropdown', array(
	'name' => 'dataset',
	'id' => 'dataset_name',
	'value' => $vars['dataset'],
	'options_values' =>get_datasets()
));

$visualization_label = elgg_echo('recommender:configuration:visualization:name');
$visualization_input = elgg_view('input/dropdown', array(
	'name' => 'visualization',
	'id' => 'visualization_name',
	'value' => $vars['visualization'],
	'options_values' =>get_visualizations()
));


$algorithm_label = elgg_echo('recommender:configuration:algorithm');
$algorithm_input = elgg_view('input/dropdown', array(
	'name' => 'algorithm',
	'id' => 'algorithm_options',
	'value' => $vars['algorithm'],
	'options_values' => Recommender::get_supported_algorithms()
));


$type_label = elgg_echo('recommender:configuration:type');
$type_input = elgg_view('input/dropdown', array(
	'name' => 'recommender_type',
	'id' => 'type_options',
	'value' => $vars['recommender_type'],
	'options_values' => Recommender::get_supported_types()
));


$processing_type_label = elgg_echo('recommender:configuration:processing:type');
$processing_type_input = elgg_view('input/dropdown', array(
	'name' => 'processing_type',
	'id' => 'processing_type_options',
	'value' => $vars['processing_type'],
	'options_values' => Recommender::get_processing_types()
));


$guid_input = elgg_view('input/hidden', array('name' => 'guid', 'value' => $vars['guid']));

//TO DO Move the style to CSS
echo <<<___HTML
<div style="margin-left:auto;margin-right:auto;width:60%;">

<br />

<div>
	<label for="configuration_name">$name_label</label>
	$name_input
</div>

<br />

<div>
<label for="description">$description_label</label>
$description_input
</div>

<br />

<div>
	<label for="dataset">$dataset_label</label><br />
	$dataset_input
</div>

<br />

<div>
	<label for="visualization">$visualization_label</label><br />
	$visualization_input
</div>

<br />

<div>
	<label for="algorithm">$algorithm_label</label><br />
	$algorithm_input
</div>

<br />

<div>
	<label for="algorithm">$type_label</label><br />
	$type_input
</div>
<br />

<div>
	<label for="algorithm">$processing_type_label</label><br />
	$processing_type_input
</div>
<br />


$guid_input
<div>
$save_button
</div>
<br />
<div>
$delete_link
</div>
</div>
___HTML;
