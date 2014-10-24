<?php
/**
 * Tagcloud widget edit view
 *
 */

// set default value
if (!isset($vars['entity']->num_items)) {
	$vars['entity']->num_items = 30;
}


if (!isset($vars['entity']->show_own_tags)) {
	$vars['entity']->show_own_tags = 0;
}


$params = array(
	'name' => 'params[num_items]',
	'value' => $vars['entity']->num_items,
	'options' => array(10, 20, 30, 50, 100),
);
$num_items_dropdown = elgg_view('input/dropdown', $params);

$params2 = array(
	'name' => 'params[show_own_tags]',
	'value' => $vars['entity']->show_own_tags,
	'options' => get_no_or_yes(),
);
$show_own_tags_dropdown = elgg_view('input/dropdown', $params2);


?>
<p>
	<?php echo elgg_echo('recommender:recommended_tags:numtags'); ?>:
	<?php echo $num_items_dropdown; ?>
	<br>
	<?php echo elgg_echo('recommender:recommended_tags:show_own_tags'); ?>:
	<?php echo $show_own_tags_dropdown; ?>
	
</p>
