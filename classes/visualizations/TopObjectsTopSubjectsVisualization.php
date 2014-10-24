<?php

class TopObjectsTopSubjectsVisualization  extends DefaultVisualization{
	
	
	public function render($recommender, $configurationguid){
		$recommender->set_max_rows(5);
		$recommender->set_matches_resolution(5);
		$content =  '<p>'.$this->render_top_matches($recommender, $configurationguid).'</p>';
		$recommender->toggle_reverse();
		$content .= '<p>'.$this->render_top_matches($recommender, $configurationguid).'</p>';
		$recommender->toggle_reverse();
		return $content;
	}
}