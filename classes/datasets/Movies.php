<?php

class Movies extends ElggRecommenderDataSet
{
	
	
	public function __construct(){
		parent::__construct("person1", "movie1");	
			
	}
	
	
	public function get_name(){
		return "Movies and Critics";
	}
	
	public function get_matches_description(){
		$subject_name = $this->get_subject_name($this->get_matches_subject_id());
		return $this->get_reverse() ? "Movies similar to '$subject_name'" : "Users with similar taste in movies";
	}
	public function get_matches_subject_id(){
		if ($this->get_reverse()){
			return 'Lady in the water';
		}
		else{
			return 'Toby';
		}
	}
	public function get_recommendations_description(){
		$subject_name = $this->get_subject_name($this->get_recommendations_subject_id());
		return $this->get_reverse() ? "Users who might like '$subject_name'" : "Movies you might like";
	}
	public function get_recommendations_subject_id(){
		return $this->get_matches_subject_id();
	}
	public function get_subject_name($subjectid){
		return $subjectid;
	}
	public function get_object_name($objectid){
		return $objectid;
	}
	
	public static function get_hook_name(){
		return NULL;
	}
	public static function get_hook_type(){
		return NULL;
	}
	public function transform($params){
		return NULL;
	}
	
}

?>