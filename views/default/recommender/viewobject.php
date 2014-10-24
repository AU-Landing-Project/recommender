<?php
/**
 * After a link is clicked in the recommender widget this will display default view for the object type represented 
 * by the link
 * When new widgets/configurations are introduced and if they deal with non Elgg objecttypes, support fot the new type and its view should be added below
 * in the switch     
 */
$objectguid = $vars['objectguid'];
$configurationguid= $vars['configurationguid'];
$objecttype = $vars['objecttype'];

switch ($objecttype) {
	case 'tags':
		elgg_push_context('tags');
		$options = array(
					'owner_guid' => $objectguid,
					'threshold' => 1,
					'limit' => 100,
					'tag_name' => 'tags',
		);
		echo elgg_view_tagcloud($options);
		elgg_pop_context();
		break;
	case 'user':
	case 'blog':
	default:
		if (elgg_entity_exists($objectguid)){
			$object = get_entity($objectguid);
			echo elgg_view_entity($object);	
		}else{
			echo "View for $objecttype not supported (yet).";
		}
		break;
}
?>