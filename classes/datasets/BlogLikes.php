<?php
/**
 *
 * Implementation of the BlogLikes Dataset used to convert blog likes into ratings
 * and store them in the DB
 *
 * Typicaly this is all that has to be done to implement new Elgg based dataset
 *
 * @author agiljanovic
 *
 */

class BlogLikes extends ElggRecommenderDataSet
{

	/**
	 *
	 * Constructor passes subjectype ("user") and objecttype ("blog") to the base class.
	 * For Elgg based datasets subjecttype and objecttype names should be the same as coresponding Elgg object types
	 */

	public function __construct(){
		parent::__construct("user", "blog");
			
	}

	/**
	 * returns the name of the dataset
	 */

	public function get_name(){
		return elgg_echo("recommender:dataset:bloglikes:name");
	}

	/**
	 * returns verbal description of the top_matches for the given subject and object.
	 * if this dataset is going to be used in the inverted(reversed) way also then this function has to take that in account by returning different descriptions
	 * in regard to the state of the reversed flag
	 * In this particular case this will return something as 'Recommended Blog Authors'
	 * or 'Blog Authors simmilar to you'
	 *
	 */
	public function get_matches_description(){
		return elgg_echo("recommender:dataset:bloglikes:matchesdescription");
	}
	/**
	 * This function returns the subject id to be used when getting top matches
	 * in this scenario it returns the id of the current user
	 */
	public function get_matches_subject_id(){
		return elgg_get_logged_in_user_guid();
	}
	/**
	 * returns verbal description of the recommendations for the given subject and object.
	 * if this dataset is going to be used in the inverted(reversed) way also then this function has to take that in account by returning different descriptions
	 * in regard to the state of the reversed flag
	 * In this particular case this will return something as 'Recommended Blogs'
	 *
	 */
	public function get_recommendations_description(){

		return elgg_echo("recommender:dataset:bloglikes:recommendationdescription");
	}
	/**
	 * This function returns the subject id to be used when getting recommendadtions
	 * in this scenario it returns the id of the current user
	 */

	public function get_recommendations_subject_id(){
		return $this->get_matches_subject_id();
	}

	/**
	 * This function returns the name  of the subject for the given id
	 * implementation should take in account the state of the revesed flag if
	 * this dataset is to be used in inverted mode
	 *
	 */
	public function get_subject_name($subjectid){
		$user = get_user($subjectid);
		return $user->name;
	}
	/**
	 * This function returns the name  of the object for the given id
	 * implementation should take in account the state of the revesed flag if
	 * this dataset is to be used in inverted mode
	 *
	 */

	public function get_object_name($objectid){
		$blog = get_entity($objectid);
		return $blog->title;
	}
	/**
	 *
	 * returns the name of the hook to be handled
	 */
	public static function get_hook_name(){
		return "blog";
	}
	/**
	 *
	 * returns the type of the hook to be handled
	 */

	public static function get_hook_type(){
		return "likes";
	}


	/**
	 * This function transforms the plugin hook parameters into a rating.
	 * it is automaticaly called by the recommender when hook handler for this dataset is invoked.
	 * the returned result should be array structured as in the code below
	 */

	public  function transform($params){
		$result = NULL;
		if ($params["action_type"]=="likes") {
			if ($subject = get_entity($params["subject_guid"])) {
				$subjecttype = $subject->getType();
				$objecttype = $params["subtype"];
				if ($subjecttype == $this->get_subject_type_id() && $objecttype == $this->get_object_type_id()){
					$result = array( array('subjectid' => $params["subject_guid"], 'objectid'=>$params["object_guid"],'rating'=> 5));
				}
			}
		}
		return $result;
	}

}

?>