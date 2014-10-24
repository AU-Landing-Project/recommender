<?php


class Recommender{

	const ALGORITHM_EUCLIDEAN = "Euclidean Distance";
	const ALGORITHM_PEARSON = "Pearson Correlation";
	const ALGORITHM_COSINE = "Cosine Similarity";
	
	
	const ITEM_BASED =  "Item-based";
	const USER_BASED =  "User-based";
	
	const PROCESSING_ASYNCHRONOUS =  "Asynchronous";
	const PROCESSING_SYNCHRONOUS =  "Synchronous";
		
	protected $dataset;
	protected $algorithm;
	protected $type;
	protected $matches_resolution;
	protected $max_rows;
	protected $processingtype;
	
//Public Methods
	
	public function __construct($dataset, $algorithm, $type, $reverse, $matches_resolution, $max_rows,$processingtype=Recommender::PROCESSING_SYNCHRONOUS) {
		$this->dataset = $dataset;
		$this->algorithm = $algorithm;
		$this->type = $type;
		$this->dataset->set_reverse($reverse);
		$this->matches_resolution = $matches_resolution;
		$this->max_rows = $max_rows;
		$this->processingtype = $processingtype;
	}

	public function import_rating_from_hook($params){
		$ratingarray = $this->dataset->transform($params);
		if(!empty($ratingarray)) $this->import($ratingarray);
	}	
	
	public function import(array $ratings_array){
		foreach ($ratings_array as $rating){
			if($this->processingtype==Recommender::PROCESSING_ASYNCHRONOUS){
				$this->queue_rating( $rating['subjectid'],$rating['objectid'], $rating['rating']);
			}
			else{
				$this->add_rating( $rating['subjectid'],$rating['objectid'], $rating['rating']);
			}
		}
	}

	
	//processes records queued for asynch processing , maxrecords -> number of records to be processed in the call, value of 0 (DEFAULT) means all 
	
	public function process_queue($maxrecords=0){
		$queuedratings=$this->dataset->get_queued_ratings($maxrecords);
		foreach ($queuedratings as $rating){
			$this->add_rating($rating->subjectid, $rating->objectid, $rating->rating);
			$this->dataset->delete_queued_rating($rating);	
		}
	}
	
	
	public function get_similarity($subjectid1, $subjectid2){
		if ($aggreg = current($this->dataset->get_aggregate($subjectid1,$subjectid2))){
			//return $this->get_similarity_value($aggreg);
			return $aggreg->similarity;
		}
		return 0;
	}

	public function get_top_matches($subjectid = null){
		$subjectid = ($subjectid==null)? $this->dataset->get_matches_subject_id() : $subjectid;
		if ($objects = $this->dataset->get_matches($subjectid, $this->get_algorithm(), $this->get_matches_resolution())){
			return $objects;
		}
		return null;
	}

	public function get_recommendation($subjectid=null){
		$subjectid = ($subjectid==null)? $this->dataset->get_recommendations_subject_id() : $subjectid;
		if ($this->type==recommender::ITEM_BASED){
			return $this->get_item_based_recommendation($subjectid);
		}
		elseif($this->type==recommender::USER_BASED){
			return $this->get_user_based_recommendation($subjectid);
		}
	}
	
	public static function get_supported_algorithms(){
		return array(
	      '1' => Recommender::ALGORITHM_EUCLIDEAN,
	      '2' => Recommender::ALGORITHM_PEARSON,
	      '3' => Recommender::ALGORITHM_COSINE		
	  );
	}
	
	public static function get_supported_types(){
		return array(
	      '1' => Recommender::ITEM_BASED,
	      '2' => Recommender::USER_BASED
	  );
	}
	
		public static function get_processing_types(){
		return array(
	      '1' => Recommender::PROCESSING_SYNCHRONOUS,
	      '2' => Recommender::PROCESSING_ASYNCHRONOUS
	  );
	}
	public static function sim_pearson($rsum1,$rsum2,$n,$sqrsum1,$sqrsum2,$psum){
		$result = 0;
		$num = (float)$psum - ((float)$rsum1 * (float)$rsum2/$n);
		$den = sqrt(((float)$sqrsum1 - pow((float)$rsum1,2)/$n) * ((float)$sqrsum2 - pow((float)$rsum2,2)/$n));
		if ($den > 0){
			$result = (float)$num/(float)$den;
		}
		return $result;
	}


	public static function sim_cosine($sqrsum1,$sqrsum2,$productsum){
		return (float)$productsum /(pow($sqrsum1,(float)0.5)*pow($sqrsum2,(float)0.5));	
	}
	

	public static function sim_euclid($sumsqrsub){
		return (float)1/(1+sqrt((float)$sumsqrsub));	
	}
	
	
	public static function getVectorSimilarity($algorithm,$vector1,$vector2){
		$sum1=0;
		$sum2=0;
		$sum1Sq=0;
		$sum2Sq=0;
		$sumProducts=0;	
		$sumsqrdiff=0;
		
		$vectorlength=count($vector1);
		if ($vectorlength!=count($vector2)){
			register_error(elgg_echo('recommender:error:VectorSim:length'));
			forward(get_input('forward', REFERER));
		}
		reset($vector1);
		reset($vector2);
		for($i=0;$i<$vectorlength;$i++){
		$val1 =current($vector1);
		$val2 =current($vector2);
	
		$sum1+= $val1;
		$sum2+= $val2;
		
		$sum1Sq+=pow($val1,2);
		$sum2Sq+=pow($val2,2);
		$sumProducts+=$val1 * $val2;	
		$sumsqrdiff+=pow($val1-val2,2);
		next($vector1);
		next($vector2);		
		}
	
		if ($algorithm == Recommender::ALGORITHM_EUCLIDEAN){
			return Recommender::sim_euclid($sumsqrdiff);
		}elseif ($algorithm == Recommender::ALGORITHM_PEARSON){
			return Recommender::sim_pearson($sum1, $sum2, $vectorlength, $sum1Sq, $sum2Sq,$sumProducts);
		}elseif ($algorithm == Recommender::ALGORITHM_COSINE){
			return (float)Recommender::sim_cosine($sum1Sq, $sum2Sq, $sumProducts);
		}
				
		return 0;//raise error instead	
	}	
	
	
//enables queueing for asynchronous processing. Incoming ratings are stored in the queue, to process them later call process_queue()
	public function enable_asynch_processing(){
		$this->processingtype=Recommender::PROCESSING_ASYNCHRONOUS;
	}
	
	
	public function disable_asynch_processing(){
		$this->processingtype=Recommender::PROCESSING_SYNCHRONOUS;
	}
	
	
	public function get_data_set(){
		return $this->dataset;
	}
	
	public function set_max_rows($max_rows){
		$this->max_rows = $max_rows;
	}	
	
	public function get_max_rows(){
		return $this->max_rows;
	}	
		
	public function set_matches_resolution($matches_resolution){
		$this->matches_resolution = $matches_resolution;
	}	
	
	public function get_matches_resolution(){
		return $this->matches_resolution;
	}	
	
	public function set_type($type){
		$this->type = $type;
	}	
	
	public function get_type(){
		return $this->type;
	}	
	
	public function set_algorithm($algorithm){
		$this->algorithm = $algorithm;
	}	
	
	public function get_algorithm(){
		return $this->algorithm;
	}	

	public function set_reverse($reverse){
		$this->dataset->set_reverse($reverse);
	}	
	
	public function get_reverse(){
		$this->dataset->get_reverse();
	}	
	
	public function toggle_reverse(){
		$this->dataset->toggle_reverse();;		
	}
	
	public function add_rating($subjectid, $objectid,$rating){
		$test = $this->dataset->get_rating($subjectid, $objectid);//add it only if never rated before
		if(empty($test)){
			$ratingrecord = $this->dataset->add_rating_record($subjectid, $objectid, $rating);
			$this->process_rating($ratingrecord);
			$this->toggle_reverse();
			$ratingrecord = $this->dataset->add_rating_record($objectid, $subjectid, $rating); //reversed
			$this->process_rating($ratingrecord);
			$this->toggle_reverse();
		}
	}
	
	// PROTECTED MEMBER FUNCTIONS
	
	protected function queue_rating($subjectid, $objectid,$rating){
		$this->dataset->queue_rating_record($subjectid, $objectid, $rating);
	}
	

	protected function process_rating($newrating){
		//find all who rated this same thing and update aggregate			
			if ($ratings = 	$this->dataset->get_object_ratings($newrating->objectid)){
				foreach ($ratings as $rating) {
						if  ($rating->subjectid != $newrating->subjectid){
							$this->update_aggregate($newrating->subjectid,$rating->subjectid, $newrating->rating,$rating->rating);
							$this->update_aggregate($rating->subjectid,$newrating->subjectid, $rating->rating,$newrating->rating);
							
						}	
					}
			}
	}
	
	protected function update_aggregate($subjectid1,$subjectid2,$rating1,$rating2){
		
		$aggreg = current($this->dataset->get_aggregate($subjectid1, $subjectid2));
		
		if (empty($aggreg)){
			$aggreg = $this->dataset->add_aggregate_record($subjectid1,$subjectid2);
		}
		$aggreg->entrycount +=  1;
		$aggreg->sumsq += pow((float)$rating1 - (float)$rating2, 2);
		$aggreg->euclidean_value = $this->sim_euclid((float)$aggreg->sumsq);
		$aggreg->ratingsum1 += (float)$rating1;
		$aggreg->ratingsum2 += (float)$rating2;
		$aggreg->sqratingsum1 += pow((float)$rating1,2);
		$aggreg->sqratingsum2 += pow((float)$rating2,2);
		$aggreg->productsum += (float)$rating1 * (float)$rating2;
		$aggreg->pearson_value = $this->sim_pearson($aggreg->ratingsum1,
														$aggreg->ratingsum2,
														$aggreg->entrycount,
														$aggreg->sqratingsum1,
														$aggreg->sqratingsum2,
														$aggreg->productsum);
		$aggreg->cosine_value = $this->sim_cosine($aggreg->sqratingsum1,
														$aggreg->sqratingsum2,
														$aggreg->productsum);
														
		$this->dataset->save_aggregate($aggreg);
	}
	
	protected function get_user_based_recommendation($subjectid){
		//other subject similar  to the given subject 
		$similarsubjects = $this->get_top_matches($subjectid);
		//objects that given subject rated, we do not need reccomendations for these
		$subjectratings = $this->dataset->get_subject_ratings($subjectid);
		//array to hold the ids of the objects rated by the given subject
		foreach ($subjectratings as $srating){
			$srarray[] = $srating->objectid; 
		}
		foreach ($similarsubjects as $similarsubject){
			// do not need if it is same as the given subject
			if ($similarsubject->subjectid2 == $subjectid) continue;
				//$similarity = $this->get_similarity_value($similarsubject);
				$similarity = $similarsubject->similarity;
				// do not calc if similarity is les than treshold
				if ($similarity <= 0) continue;
				// get all objects rated by this subject
				$similarsubjectratings = $this->dataset->get_subject_ratings($similarsubject->subjectid2);		
	
			foreach ($similarsubjectratings as $similarsrating){
				$key = $similarsrating->objectid;
				if (!in_array($key,$srarray)){
					$recommendations[$key]["sumsimrating"]+= (float)$similarity*(float)$similarsrating->rating;	
					$recommendations[$key]["sumsim"]+= (float)$similarity;
					$recommendations[$key]["rank"]= (float)$recommendations[$key]["sumsimrating"]/(float)$recommendations[$key]["sumsim"];
				}
			}
		}
		return $this->slice_recommendations($recommendations);
	}

	protected function get_item_based_recommendation($subjectid){
		//objects (e.g. movies) that given subject rated
		$subjectratings = $this->dataset->get_subject_ratings($subjectid);
		
		//array to hold the ids of the objects (e.g. movies) rated by the given subject
		foreach ($subjectratings as $srating){
			$srarray[] = $srating->objectid; 
		}
		foreach ($subjectratings as $rating){
			// get top matches for the given item (eg movie)
			$this->toggle_reverse(); //flipping reverse for the following call
			$similaritems = $this->get_top_matches($rating->objectid);//TO DO note THIS is reversed , $objecttype, $subjecttype, $algorithm, $resolution);
			$this->toggle_reverse(); //flipping it back affter the call
			foreach ($similaritems as $similaritem){
				$key=$similaritem->subjectid2;
				if (!in_array($key,$srarray)){
					//$similarity = $this->get_similarity_value($similaritem);
					$similarity = $similaritem->similarity;
					if ($similarity <= 0) continue; 
					$recommendations[$key]["sumsimrating"]+= (float)$similarity*(float)$rating->rating;	
					$recommendations[$key]["sumsim"]+= (float)$similarity;
					$recommendations[$key]["rank"]= (float)$recommendations[$key]["sumsimrating"]/(float)$recommendations[$key]["sumsim"];
				}
			}
		}
		return $this->slice_recommendations($recommendations);
	}

	protected function slice_recommendations($recommendations){
		reset($recommendations);
		uasort($recommendations, "gr_compare");
		$count = count($recommendations);
		$maxrows = $this->get_max_rows();
		if ($maxrows >= $count) $maxrows = 0; // show all
		return ($maxrows > 0) ? array_slice($recommendations, 0, $maxrows, true) : $recommendations;
	}
	
	// PRIVATE MEMBER FUNCTIONS
/*	
	private function get_similarity_value($object){
		if ($this->get_algorithm()== Recommender::ALGORITHM_EUCLIDEAN){
			return (float)$object->euclidean_value;
		}elseif ($this->get_algorithm()== Recommender::ALGORITHM_PEARSON){
			return (float)$object->pearson_value;
		}elseif ($this->get_algorithm()== Recommender::ALGORITHM_COSINE){
			return (float)$object->cosine_value;
		}
	}
*/

}
?>