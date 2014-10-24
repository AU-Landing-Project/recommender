  <p>
  <?php 
  
  /** 
  * recommender widget instance settings
  */
        
  echo elgg_echo('recommender:select:configuration'); 
  echo "<br />";
  echo elgg_view(	'input/dropdown',
                  array(
                      'name' => 'params[configuration]',
                      'options_values' => get_configurations_array(),
                      'value' => $vars['entity']->configuration,
                  )
  );
?>
  </p>
  
  