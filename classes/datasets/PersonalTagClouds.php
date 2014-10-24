<?php

class PersonalTagClouds extends ElggRecommenderDataSet
{
	
	public function __construct(){
		parent::__construct("user", "tags");	
			
	}
	
	public function get_name(){
		return elgg_echo('recommender:dataset:personaltagclouds:name');
	}
	
	public function get_matches_description(){
		if ($this->get_reverse()){
			return elgg_echo('recommender:dataset:personaltagclouds:matchesdescription:reversed');	
		}
		else{
			return elgg_echo('recommender:dataset:personaltagclouds:matchesdescription');
		}
	}
	public function get_matches_subject_id(){
		return elgg_get_logged_in_user_guid();
	}
	public function get_recommendations_description(){
		if ($this->get_reverse()){
			return elgg_echo('recommender:dataset:personaltagclouds:recommendationdescription:reversed');	
		}
		else{
			return elgg_echo('recommender:dataset:personaltagclouds:recommendationdescription');		
		}
	}

	public function get_recommendations_subject_id(){
		return $this->get_matches_subject_id();
	}

	public function get_subject_name($subjectid){
		$user = get_user($subjectid);
		if ($this->get_reverse()){
			return $user->name." Tags";
		}
		else{
			return $user->name;
		}
	}

	public function get_object_name($objectid){
		$user = get_user($objectid);
		if ($this->get_reverse()){
			return $user->name;
		}
		else{
			return $user->name." Tags";
		}
	}
	
	public static function get_hook_name(){
		return "tags";
	}
	public static function get_hook_type(){
		return "rating";
	}
	
	public  function transform($ratings){
		$params = current($ratings);
		$result = NULL;
		if (isset($params["rating"])&&
			isset($params["subjectid"])&&
			isset($params["objectid"])){
			$result = $ratings;
			}
		return $result;		
	}
}

?>