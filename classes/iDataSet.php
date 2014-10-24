<?php

/**
 * @author agiljanovic
 *  Interface definition for the Recomender Datasets; every implementaion of dataset has to implemet this interface.
 *  typicaly data set implementations should subclass ElggRecomenderDataSet if recommendations are persisted in the Elgg environment
 *  otherwise BaseRecommenderDataSet class should be used. Both are abstract classes.
 *  Dataset is handling all comunication and interactin with the persistance (DB) layer.
 *  It stores ratings and processed aggreggate records and returns the rating information 
 *  it also defines the meaning and the way  of resolving the names for rating subject (e.g. user) and object (e.g. blog) 
 *  
 *  
 */

interface iDataSet
{
	public function get_name();
	public function set_reverse($reverse);
	public function get_reverse();
	public function toggle_reverse();
	public function add_rating_record($subjectid, $objectid, $rating);
	public function delete_ratings($subjectid,$objectid);
	public function queue_rating_record($subjectid, $objectid, $rating);
	public function get_queued_ratings($maxrecords);
	public function delete_queued_rating($queuedrating);
	public function get_rating($subjectid, $objectid);
	public function get_object_ratings($objectid);
	public function get_subject_ratings($subjectid);
	public function get_all_ratings();

	public function add_aggregate_record($subjectid1,$subjectid2);
	public function delete_aggregates($subjectid1,$subjectid2);
	public function save_aggregate($aggreg);
	public function get_aggregate($subjectid1,$subjectid2);
	public function get_subject1_aggregates($subjectid);
	public function get_subject2_aggregates($subjectid);
	public function get_all_aggregates();
	public function get_matches($subjectid, $algorithm, $limit); 
	
	public function get_subject_type_id();
	public function get_object_type_id();
	public function get_matches_description();
	public function get_matches_subject_id();
	public function get_recommendations_description();
	public function get_recommendations_subject_id();
	public function get_subject_name($subjectid);
	public function get_object_name($objectid);
	//public function get_selection_options();
}
