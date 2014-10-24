<?php

class SimpleVisualization implements iVisualization{
	
	
	public function render($recommender,$configurationguid){
		$recommender->set_max_rows(5);
		$recommender->set_matches_resolution(5);
		$content =  $this->render_top_matches($recommender);
		$content .=  $this->render_recommendation($recommender);
		$recommender->toggle_reverse();
		$content .=  $this->render_top_matches($recommender);
		$content .=  $this->render_recommendation($recommender);
		return $content;
	}
	
	public function render_top_matches($recommender){
		$dataset = $recommender->get_data_set();
		$algorithm = $recommender->get_algorithm();
		$title = $dataset->get_matches_description();
		$i=1;
		$content = "<h4>$title:</h4>";		
		$content =  $content. "(using $algorithm)<br>";
		$result = $recommender->get_top_matches();
		foreach ($result as $object){
			$sim = round($object->similarity, 2);
			$description = $dataset->get_subject_name($object->subjectid2); 
			$content =  $content. "$i. $description ($sim)<br>";
			$i++;	
		}
		return $content;
	}


	public function render_recommendation($recommender){
	
			$dataset = $recommender->get_data_set();
			$algorithm = $recommender->get_algorithm();
			$title = $dataset->get_recommendations_description();
			$rectype = $recommender->get_type();
			$i=1;
			//echo $datasetdef->get_name().'<br>';
			$content = "<h4>$title:</h4>";
			$content =  $content. "($rectype using $algorithm)<br>";
			$recommendeditems = $recommender->get_recommendation();
			foreach ($recommendeditems as $key => $item){
				$rank = round($item['rank'],2);
				$description = $dataset->get_object_name($key);			
				$content =  $content. "$i. $description ($rank) <br>";
				$i++;
			}
			return $content;	
	}
}