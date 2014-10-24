<?php
/**
 * 
 * This class implemets all Elgg speciific data handling and interaction
 * it also intoduces elgg specific funcionality related to defining the plugin hook name and type, 
 * transforming the event data inro rating and support for delition of elgg entities
 * Typicaly every Elgg based dataset should be derived from this class as for example BlogLikes
 * @author agiljanovic
 *
 */


abstract class ElggRecommenderDataSet extends BaseRecommenderDataSet{

	const ELGG_OBJECT =  "object";
	const SUBTYPE_RATING =  "rating";
	const SUBTYPE_AGGREGATE =  "aggregated_similarity";//"aggregated_euclidean_pearson";
	const SUBTYPE_RATINGS_QUEUE =  "ratings_queue";
	
	public function __construct($subjecttypeid,$objecttypeid){
		parent::__construct($subjecttypeid, $objecttypeid);	
			
	}
	
	
	public function add_rating_record($subjectid, $objectid, $rating){
		$object = new ElggObject();
		$object->subtype = ElggRecommenderDataSet::SUBTYPE_RATING;
		$object->access_id = 2; //0 (private), 1 (logged in users only), 2 (public).
		$object->save();
		$object->subjectid = $subjectid;
		$object->subjecttype = $this->get_subject_type_id();
		$object->objectid = $objectid;
		$object->objecttype = $this->get_object_type_id();
		$object->rating = $rating;
		$object->save();
		return $object;
	}

	public function get_object_ratings($objectid){
		$options = array(
			'type' => ElggRecommenderDataSet::ELGG_OBJECT,
			'subtype' => ElggRecommenderDataSet::SUBTYPE_RATING,
			'limit'=>0,
			'metadata_name_value_pairs' => array(
				array(
					'name' => 'subjecttype',
					'value' => $this->get_subject_type_id()
				),
				array(
					'name' => 'objectid',
					'value' => $objectid
				),
				array(
					'name' => 'objecttype',
					'value' => $this->get_object_type_id()
				)
			)
		);
		return elgg_get_entities_from_metadata($options); 
	}
	
	public function get_subject_ratings($subjectid){
		$options = array(
			'type' => ElggRecommenderDataSet::ELGG_OBJECT,
			'subtype' => ElggRecommenderDataSet::SUBTYPE_RATING,
			'limit'=>0,
			'metadata_name_value_pairs' => array(
				array(
					'name' => 'subjectid',
					'value' => $subjectid
				),
				array(
					'name' => 'subjecttype',
					'value' => $this->get_subject_type_id()
				),
				
				array(
					'name' => 'objecttype',
					'value' => $this->get_object_type_id()
				)
			)
		);
		return elgg_get_entities_from_metadata($options); 
	}
	
	
	public function get_rating($subjectid, $objectid){

		$options = array(
			'type' => ElggRecommenderDataSet::ELGG_OBJECT,
			'subtype' => ElggRecommenderDataSet::SUBTYPE_RATING,
			'limit'=>1,
			'metadata_name_value_pairs' => array(
				array(
					'name' => 'subjectid',
					'value' => $subjectid
				),
				array(
					'name' => 'subjecttype',
					'value' => $this->get_subject_type_id()
				),
				
				array(
					'name' => 'objectid',
					'value' => $objectid
				),
				
				array(
					'name' => 'objecttype',
					'value' => $this->get_object_type_id()
				)
			)
		);
		return 	elgg_get_entities_from_metadata($options);
	}

public function get_all_ratings(){
		$options = array(
			'type' => ElggRecommenderDataSet::ELGG_OBJECT,
			'subtype' => ElggRecommenderDataSet::SUBTYPE_RATING,
			'limit'=>0,
			'metadata_name_value_pairs' => array(
				array(
					'name' => 'subjecttype',
					'value' => $this->get_subject_type_id()
				),
				array(
					'name' => 'objecttype',
					'value' => $this->get_object_type_id()
				)
			)
		);
		return 	elgg_get_entities_from_metadata($options);
}



public function delete_ratings($subjectid=null,$objectid=null){
	if ($subjectid==null && $objectid==null){
		$entities = $this->get_all_ratings();		
	}
	else 	if ($subjectid!=null && $objectid!=null){
		$entities = $this->get_rating($subjectid,$objectid);
	}
	else 	if ($subjectid!=null){
		$entities = $this->get_subject_ratings($subjectid);
	}
	else 	/*if ($objectid!=null)*/{
		$entities = $this->get_object_ratings($objectid);
	}
	$this->delete_elgg_entities($entities);
}

	public function queue_rating_record($subjectid, $objectid, $rating){
		$object = new ElggObject();
		$object->subtype = ElggRecommenderDataSet::SUBTYPE_RATINGS_QUEUE;
		$object->access_id = 2; //0 (private), 1 (logged in users only), 2 (public).
		$object->save();
		$object->subjectid = $subjectid;
		$object->objectid = $objectid;
		$object->rating = $rating;
		$object->subjecttype = $this->get_subject_type_id();
		$object->objecttype = $this->get_object_type_id();
		$object->save();
		return $object;
	}

	public function get_queued_ratings($maxrecords=0){
		$options = array(
			'type' => ElggRecommenderDataSet::ELGG_OBJECT,
			'subtype' => ElggRecommenderDataSet::SUBTYPE_RATINGS_QUEUE,
			'limit'=>$maxrecords,
				'metadata_name_value_pairs' => array(
		array(
						'name' => 'subjecttype',
						'value' => $this->get_subject_type_id()
		),
		array(
						'name' => 'objecttype',
						'value' => $this->get_object_type_id()
		)
		)
		);
		return 	elgg_get_entities_from_metadata($options);
	}

	public function delete_queued_rating($queuedrating){
		$queuedrating->delete();
	}	

	public function add_aggregate_record($subjectid1,$subjectid2){
		$object = new ElggObject();
		$object->subtype = ElggRecommenderDataSet::SUBTYPE_AGGREGATE;
		$object->access_id = 2; //0 (private), 1 (logged in users only), 2 (public).
		$object->save();
		$object->subjectid1 = $subjectid1;
		$object->subjectid2 = $subjectid2;
		$object->subjecttype = $this->get_subject_type_id();
		$object->objecttype = $this->get_object_type_id();
		$object->save();
		return $object;
	}
	
	public function save_aggregate($record){
		$record->save();
	}
	
	public function get_aggregate($subjectid1,$subjectid2){
		$options = array(
				'type' => ElggRecommenderDataSet::ELGG_OBJECT,
				'subtype' => ElggRecommenderDataSet::SUBTYPE_AGGREGATE,
				'limit'=>1,
				'metadata_name_value_pairs' => array(
		array(
						'name' => 'subjectid1',
						'value' => $subjectid1
		),
		array(
						'name' => 'subjectid2',
						'value' => $subjectid2
		),
		array(
						'name' => 'subjecttype',
						'value' => $this->get_subject_type_id()
		),
		array(
						'name' => 'objecttype',
						'value' => $this->get_object_type_id()
		)
		)
		);
		return elgg_get_entities_from_metadata($options);
	}
	
	
	public function get_subject1_aggregates($subjectid){
		
		$options = array(
				'type' => ElggRecommenderDataSet::ELGG_OBJECT,
				'subtype' => ElggRecommenderDataSet::SUBTYPE_AGGREGATE,
				'limit'=>0,
				'metadata_name_value_pairs' => array(
		array(
						'name' => 'subjectid1',
						'value' => $subjectid
		),
		array(
						'name' => 'subjecttype',
						'value' => $this->get_subject_type_id()
		),
		array(
						'name' => 'objecttype',
						'value' => $this->get_object_type_id()
		)
		)
		);
		return elgg_get_entities_from_metadata($options);
	}

	public function get_subject2_aggregates($subjectid){
		
		$options = array(
				'type' => ElggRecommenderDataSet::ELGG_OBJECT,
				'subtype' => ElggRecommenderDataSet::SUBTYPE_AGGREGATE,
				'limit'=>0,
				'metadata_name_value_pairs' => array(
		array(
						'name' => 'subjectid2',
						'value' => $subjectid
		),
		array(
						'name' => 'subjecttype',
						'value' => $this->get_subject_type_id()
		),
		array(
						'name' => 'objecttype',
						'value' => $this->get_object_type_id()
		)
		)
		);
		return elgg_get_entities_from_metadata($options);
	}
	
	public function get_all_aggregates(){
		$options = array(
				'type' => ElggRecommenderDataSet::ELGG_OBJECT,
				'subtype' => ElggRecommenderDataSet::SUBTYPE_AGGREGATE,
				'limit'=>0,
				'metadata_name_value_pairs' => array(
		array(
						'name' => 'subjecttype',
						'value' => $this->get_subject_type_id()
		),
		array(
						'name' => 'objecttype',
						'value' => $this->get_object_type_id()
		)
		)
		);
		return elgg_get_entities_from_metadata($options);
	}	
	
	public function delete_aggregates($subjectid1=null,$subjectid2=null){
		if ($subjectid1==null && $subjectid2==null){
			$entities = $this->get_all_aggregates();		
		}
		else 	if ($subjectid1!=null && $subjectid2!=null){
			$entities = $this->get_aggregate($subjectid1,$subjectid2);
		}
		else 	if ($subjectid1!=null){
			$entities = $this->get_subject1_aggregates($subjectid1);
		}
		else 	/*if ($subjectid2!=null)*/{
			$entities = $this->get_subject2_aggregates($subjectid2);
		}
		$this->delete_elgg_entities($entities);
	}	

	public function get_matches($subjectid, $algorithm, $limit=0){
		
		
		if($algorithm == Recommender::ALGORITHM_EUCLIDEAN){
			$orderby = 'euclidean_value';
		}
		elseif($algorithm == Recommender::ALGORITHM_PEARSON){
			$orderby = 'pearson_value';
		}
		elseif($algorithm == Recommender::ALGORITHM_COSINE){
			$orderby = 'cosine_value';
		}
		
		$options = array(
				'type' => ElggRecommenderDataSet::ELGG_OBJECT,
				'subtype' => ElggRecommenderDataSet::SUBTYPE_AGGREGATE,
				'limit'=>$limit,
				'metadata_name_value_pairs' => array(
		array(
						'name' => 'subjectid1',
						'value' => $subjectid
		),
		array(
						'name' => 'subjecttype',
						'value' => $this->get_subject_type_id()
		),
		
		array(
						'name' => 'objecttype',
						'value' => $this->get_object_type_id()
		)
		),
		order_by_metadata => array('name' => $orderby,
                           'direction' => DESC,
                           )
		);
		
		$topmatches = elgg_get_entities_from_metadata($options);
		foreach($topmatches as $record){
			$record->similarity = $record->$orderby;
		}
		return $topmatches;
		//return elgg_get_entities_from_metadata($options);
	}
	public static abstract function get_hook_name();
	public static abstract function get_hook_type();
	public abstract function transform($params);

	
	protected function delete_elgg_entities($entities){
	 foreach ($entities as $item) {
		 $item->delete();
	 }
 }
	
}
?>