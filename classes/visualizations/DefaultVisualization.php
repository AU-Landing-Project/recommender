<?php

class DefaultVisualization implements iVisualization{
	
	
	public function render($recommender, $configurationguid){
		$recommender->set_max_rows(5);
		$recommender->set_matches_resolution(5);
		$content =  '<p>'.$this->render_top_matches($recommender, $configurationguid).'</p>';
		$content .= '<p>'.$this->render_recommendation($recommender, $configurationguid).'</p>';
		return $content;
	}
	
	public function render_top_matches($recommender, $configurationguid){
		$dataset = $recommender->get_data_set();
		$algorithm = $recommender->get_algorithm();
		$title = $dataset->get_matches_description();
		$i=1;
		$content = "<h4>$title:</h4>";		
//		$content =  $content. "(using $algorithm)<br>";
		$result = $recommender->get_top_matches();
		foreach ($result as $object){
			$sim = round($object->similarity, 2);
			$description = $dataset->get_subject_name($object->subjectid2); 
			$content =  $content. "$i. ".recommender_render_link($configurationguid, $object->subjectid2, $dataset->get_subject_type_id(),$description)."<br>";// ($sim)<br>";
			$i++;	
		}
		return $content;
	}


	public function render_recommendation($recommender, $configurationguid){
	
			$dataset = $recommender->get_data_set();
			$algorithm = $recommender->get_algorithm();
			$title = $dataset->get_recommendations_description();
			$rectype = $recommender->get_type();
			$i=1;
			//echo $datasetdef->get_name().'<br>';
			$content = "<p><h4>$title:</h4>";
	//		$content =  $content. "($rectype using $algorithm)<br>";
			$recommendeditems = $recommender->get_recommendation();
			foreach ($recommendeditems as $key=>$item){
				$rank = round($item['rank'],2);
				$description = $dataset->get_object_name($key);			
				$content =  $content. "$i. ".recommender_render_link($configurationguid, $key, $dataset->get_object_type_id(), $description)."<br>";//" ($rank) <br>";
				$i++;
			}
			return $content.'</p>';	
	}
}