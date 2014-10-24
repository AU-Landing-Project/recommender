<?php

/**
 * Test page for Tags Recommendations
 */

//cleanup_and_refresh_tags_similarities();

//	cleanup_tags_similarities();
//	calculate_and_load_all_tags_similarities_in_recommender();


//echo "test";

/*
$dataset = new BlogLikes();
$dataset->delete_ratings();
$dataset->toggle_reverse();
$dataset->delete_ratings();
$dataset->delete_aggregates();
$dataset->toggle_reverse();
$dataset->delete_aggregates();
return;
*/
echo "<h5>Tags Recommender Tests</h5>";


$alltags=get_all_users_top_tags(100,1);
$alg = Recommender::ALGORITHM_COSINE;
$sims=get_tags_similarities($alg, $alltags);
echo var_dump($sims);
reset($alltags);
$first = current($alltags);
$first_owner_guid=key($alltags);
next($alltags);
$second = current($alltags);
$second_owner_guid=key($alltags);
next($alltags);
$third = current($alltags);
$third_owner_guid=key($alltags);


test_drive(Recommender::ALGORITHM_COSINE,$first,$second,"frst and second",$first_owner_guid,$second_owner_guid,true);
test_drive($algorithm=Recommender::ALGORITHM_PEARSON,$first,$second,"frst and second",$first_owner_guid,$second_owner_guid);
test_drive(Recommender::ALGORITHM_EUCLIDEAN,$first,$second,"frst and second",$first_owner_guid,$second_owner_guid);

test_drive(Recommender::ALGORITHM_COSINE,$first,$third,"first and third",$first_owner_guid,$third_owner_guid,true);
test_drive($algorithm=Recommender::ALGORITHM_PEARSON,$first,$third,"first and third",$first_owner_guid,$third_owner_guid);
test_drive(Recommender::ALGORITHM_EUCLIDEAN,$first,$third,"first and third",$first_owner_guid,$third_owner_guid);

test_drive(Recommender::ALGORITHM_COSINE,$second,$third,"second and third",$second_owner_guid,$third_owner_guid,true);
test_drive($algorithm=Recommender::ALGORITHM_PEARSON,$second,$third,"second and third",$second_owner_guid,$third_owner_guid);
test_drive(Recommender::ALGORITHM_EUCLIDEAN,$second,$third,"second and third",$second_owner_guid,$third_owner_guid);



//**************************************************************************************************
function test_drive($algorithm,$mcloud, $scloud, $title,$mowner,$sowner,$displaydata){

	$mowner=get_user($mowner);
	$sowner=get_user($sowner);
	$vectors = get_word_vectors($mcloud,$scloud);
	$similarity = Recommender::getVectorSimilarity($algorithm, $vectors[0], $vectors[1]);
	$svectors = get_word_vectors($scloud,$mcloud);
	$ssimilarity = Recommender::getVectorSimilarity($algorithm, $svectors[0], $svectors[1]);
	
	if ($displaydata){
		echo strtoupper($title)."<br>";
		echo "Tag Cloud Similarity for: ".$mowner->name." (master set) and ".$sowner->name."<br>";
		echo "<br>";
		echo $mowner->name."<br>";
		echo var_dump($mcloud);
		echo $sowner->name."<br>";
		echo var_dump($scloud);
		echo"<br>";
		echo "Maser and Slave Vectors:<br>";
		echo var_dump($vectors);
		echo "<br>";
		echo "Switched Maser and Slave Vectors:<br>";
		echo var_dump($svectors);
		echo "<br>";
	}
	echo "$algorithm: $similarity<br>";
	echo "Switched: $algorithm: $ssimilarity<br>";
	echo "____________________________________________________<br>";
}

