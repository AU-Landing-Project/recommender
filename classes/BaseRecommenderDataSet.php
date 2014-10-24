<?php
/**
 * 
 * This class implemets generic functionality around rating subjecttype, objecttype
 * and handling of inverting (reversing)the recomendation dataset in which case the meaning of the subject and object gets switched around
 * subject becomes object and vice versa.    
 * @author agiljanovic
 *
 */

abstract class BaseRecommenderDataSet implements iDataSet
{
	protected $reverse;
	protected $subjecttypeid; 
	protected $objecttypeid;
	
	
	public function __construct($subjecttypeid,$objecttypeid){
		
		$this->subjecttypeid = $subjecttypeid;
		$this->objecttypeid = $objecttypeid;
	}
	
	public function get_subject_type_id(){
		return $this->get_reverse() ? $this->objecttypeid : $this->subjecttypeid;
	}
	public function get_object_type_id(){
		return $this->get_reverse() ? $this->subjecttypeid : $this->objecttypeid;
	}
	
	public function set_reverse($reverse){
		$this->reverse = $reverse;
	}
	public function get_reverse(){
		return $this->reverse;
	}
	public function toggle_reverse(){
		$this->set_reverse(!$this->get_reverse());
	} 
		
	public function add_rating_record($subjectid, $objectid, $rating){}
	public function delete_ratings($subjectid=null,$objectid=null){}
	public function get_object_ratings($objectid){}
	public function get_subject_ratings($subjectid){}
	public function get_rating($subjectid, $objectid){}
	public function get_all_ratings(){}
	public function add_aggregate_record($subjectid1,$subjectid2){}
	public function delete_aggregates($subjectid1=null,$subjectid2=null){}	
	public function save_aggregate($aggreg){}
	public function get_aggregate($subjectid1,$subjectid2){}
	public function get_subject1_aggregates($subjectid){}
	public function get_subject2_aggregates($subjectid){}
	public function get_all_aggregates(){}
	public function get_matches($subjectid, $algorithm, $limit){} 
	public function queue_rating_record($subjectid, $objectid, $rating){}
	public function get_queued_ratings($maxrecords){}
	public function delete_queued_rating($queuedrating){}
	
	 public function get_name(){}
	 public function get_matches_description(){}
	 public function get_matches_subject_id(){}
	 public function get_recommendations_description(){}
	 public function get_recommendations_subject_id(){}
	 public function get_subject_name($subjectid){}
	 public function get_object_name($objectid){}

/*	 public function get_selection_options(){
		$this->toggle_reverse();
		$r_m_subject = $this->get_matches_subject_id();
		$r_m_subject = $this->get_subject_name($r_m_subject);
		$r_r_subject = $this->get_recommendations_subject_id();
		$r_r_subject = $this->get_subject_name($r_r_subject);
		$this->toggle_reverse();
		
		$result[0] = $this->get_matches_description();
		$result[1] = $this->get_recommendations_description();
		$this->toggle_reverse();
		$result[2] = $this->get_matches_description($r_m_subject);
		$result[3] = $this->get_recommendations_description($r_r_subject);
		$this->toggle_reverse();					   
		return $result;					   
	}*/
	 
}
?>