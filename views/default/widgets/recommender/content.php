<div class="contentWrapper">
<?php
/**
* Recommender widget, uses selected configuration visualization to render the visual reprezentation
 */
$widget = $vars['entity'];
$configuration_guid = $widget->configuration;
if (isset($configuration_guid)){
	$configuration = get_entity($configuration_guid);
}

if(empty($configuration)){
	echo elgg_echo('recommender:not_configured');
	return;	
}

$dataset = $configuration->dataset;
$algorithm = $configuration->algorithm;
$algorithms= Recommender::get_supported_algorithms();
$algorithm = $algorithms[$algorithm];
$rectype = $configuration->recommender_type;
$rectypes = Recommender::get_supported_types();
$rectype = $rectypes[$rectype];
$processingtype = $configuration->processing_type;
$processingtypes = Recommender::get_processing_types();
$processingtype = $processingtypes[$processingtype];

$visualization = $configuration->visualization;
	

$recommender = new Recommender(new $dataset(),$algorithm, $rectype, false, 0, 0,$processingtype);
$vis = new $visualization();
echo $vis->render($recommender, $configuration_guid);

?>
</div>