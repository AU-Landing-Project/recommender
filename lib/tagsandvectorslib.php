<?php

/**
 * 
 * Library - Tags and Word Vectors
 */




/**
 * cleanup_and_refresh_tags_similarities()
 * reinitializes all tag recommendations

 */

function cleanup_and_refresh_tags_similarities(){
	set_time_limit(0);// no execution time limit
	cleanup_tags_similarities();
	calculate_and_load_all_tags_similarities_in_recommender();
	set_time_limit(30);
}


/**
 * cleanup_tags_similarities()
 * delets all personal tag clouds rating and similaritty data 
 */

function cleanup_tags_similarities(){
	$dataset = new PersonalTagClouds();
	$dataset->delete_ratings();
	$dataset->toggle_reverse();
	$dataset->delete_ratings();
	$dataset->delete_aggregates();
	$dataset->toggle_reverse();
	$dataset->delete_aggregates();
}


/**
 * calculate_and_load_all_tags_similarities_in_recommender($algorithm,$limit,$treshold)
 * calculates tag cloud similarities for all users and loads them as ratings into recommender 
 */


function calculate_and_load_all_tags_similarities_in_recommender($algorithm=Recommender::ALGORITHM_COSINE,$limit=100,$treshold=1){
	load_tags_similarities_in_recommender_as_ratings(get_tags_similarities($algorithm, get_all_users_top_tags($limit,$treshold)));
}

//load tag cluds similarites in recommender as ratings
function load_tags_similarities_in_recommender_as_ratings($similarities){
	
	if (is_null($similarities)) return;
	
	foreach ($similarities as $subjectguid=>$tagclouds){
		foreach($tagclouds as $objectguid=>$rating){
		 	$params[] = array('subjectid' => $subjectguid, 'objectid'=>$objectguid,'rating'=> (float)$rating*5);
		}
	}
	elgg_trigger_plugin_hook('tags', 'rating',$params);
}


//
function get_tags_similarities($algorithm, $alltags){
	$alluserguids=array_keys($alltags);
	foreach($alluserguids as $theuserguid){
		$theusertags = $alltags[$theuserguid];
		$similaritiesforuser=array();
		foreach($alltags as $auserguid => $ausertags){
			if ($theuserguid != $auserguid){
				$vectors = get_word_vectors($theusertags,$ausertags);
				$cloudssimilarity = Recommender::getVectorSimilarity($algorithm, $vectors[0], $vectors[1]);
				if ($cloudssimilarity>0) $similaritiesforuser[$auserguid]= $cloudssimilarity;
			}
		}
		$simforallusers[$theuserguid]=$similaritiesforuser;
	}
	return $simforallusers;
}





function get_word_vectors($master,$slave){
	$mastersize = count($master);
	$slavesize = count($slave);
	$vectors=array();
	if(	$mastersize>0 && $slavesize>0){
		$maxwordcount = $mastersize>=$slavesize ? $slavesize : $mastersize;
		reset($master);
		for ($i=0;$i<$maxwordcount;$i++){
			$vector0[]=current($master);
			$key=key($master);
			$vector1value= array_key_exists($key,$slave)?$slave[$key]:0;
			$vector1[]=$vector1value;
			next($master);
		}
		$vectors[]=$vector0;
		$vectors[]=$vector1;
	}
	return $vectors;
}

function get_all_users_top_tags($limit=100,$treshold=1){
	$allusers=r_get_users();
	foreach($allusers as $user){
		$guid = $user->guid;
		$usertags = r_get_tags($guid,$limit,$treshold);
		$thisusertagsarray=array();
		foreach ($usertags as $tag){
			$thisusertagsarray[$tag->tag]=(int)$tag->total;
		}
		$allusertags[$guid]=$thisusertagsarray;
	}
	return $allusertags;
}

function r_get_users(){
	return 	elgg_get_entities(array('type' => 'user'));
}

function r_get_tags($ownerid,$limit=100,$treshold=1){
	$options = array(
	'owner_guid' => $ownerid,
	'threshold' => $treshold,
	'limit' => $limit,
	'tag_name' => 'tags'
	);
	return elgg_get_tags($options);
}

function recommender_view_tagcloud($diplaylimit,$show_own_tags=0,$limit=100,$treshold=1){
	$recommender=new Recommender(new PersonalTagClouds(), Recommender::ALGORITHM_PEARSON, Recommender::ITEM_BASED, false, 10, 10, Recommender::PROCESSING_SYNCHRONOUS);
	$result = $recommender->get_top_matches();

	foreach ($result as $aggregate){
		$user_guids[]= $aggregate->subjectid2;
	}


	$user_tags = r_get_tags(elgg_get_page_owner_guid(),$limit,$treshold);
	
	if ($show_own_tags == 'Yes'){
		$tag_data = $user_tags; 
		$exclude_tags=array();
	}
	else{
		$tag_data = array	();
		$exclude_tags=$user_tags;
	}
	
	foreach($user_guids as $guid){
		$tag_data = array_merge($tag_data,r_get_tags($guid,$limit,$treshold));
	}

	$normalised=recommender_normalise_tags($tag_data,$exclude_tags,$diplaylimit);

	return elgg_view("output/tagcloud", array(
		'value' => $normalised,
		'type' => '',
		'subtype' => '',
	));
}

function recommender_normalise_tags($tag_data,$exclude_tags,$limit=20){
	foreach ($tag_data as $tag){
		if(!in_array($tag,$exclude_tags)){
			$norma[$tag->tag]+= $tag->total;
		}
	}
	arsort($norma,SORT_NUMERIC);
	$i=0;
	foreach ($norma as $word=>$count){
		$returno = new stdClass();
		$returno->tag=$word;
		$returno->total=$count;
		$returna[]=$returno;
		if (++$i==$limit) break;
	}
	return $returna;
}

