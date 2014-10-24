<?php
/**
 * Recommender Tag cloud widget
 */

$num_items = $vars['entity']->num_items;
$show_own_tags = $vars['entity']->show_own_tags;
elgg_push_context('tags');
echo recommender_view_tagcloud($num_items, $show_own_tags);
elgg_pop_context();

