<?php

/*

The data visualizer application defines data sources and generates tables representing that data via XPHP vars.

*/

$_LW->REGISTERED_APPS['data_visualizer']=[
	'title'=>'Data Visualizer',
	'handlers'=>['onBeforeOutput'],
	'application'=>[
		'order'=>-1
	],
	'custom'=>[
		'enabled'=>[ // list enabled variables here, then add getVariable() case below to configure
	    'covid_stats_7dayavg', 
	    'covid_stats_7dayavg_per100Kpop', 
		]
	]
]; // configure this module

class LiveWhaleApplicationDataVisualizer {

protected function getVariable($var_name, $params) { // configures all variables for display
global $_LW;
$output='';
switch($var_name) {
  
	/*
	case 'sample-variable':
		$data=$this->getData('my-data-source', $cache_ttl, $params]);
		if ($data!==false) {
			$output=$this->getTable($data, $params);
		}
		else if ($_LW->isLiveWhaleUser()) {
			$output='<p><strong>Error:</strong> '.$this->error.'</p>';
		};
		break;
	*/

	case 'covid_stats_7dayavg-x':
	
		// COVID statistics via the Knowi API 
	  // https://www.knowi.com/coronavirus-dashboards/covid-19-api/
	  // Displays 7 day average of new COVID cases per county per 100K population, last 30 days
    // XPHP usage: <xphp var="covid_stats_7dayavg" format="json" counties="Alameda County, California"/>

		$params['days']=31; // to ensure displaying 30 days, in case today's data isn't available
		$data=$this->getData('knowi', 3600*12, $params); // get data from Knowi data source, caching for 12 hours
		if ($data!==false) {
			$params=[ // set table parameters
				'table_header'=>'7-day average of new cases by county, last 30 days',
				'table_footer'=>'Data updated: '.$this->getLastRefreshedTime('m/d/y'),
				'table_attributes'=>[
					'id'=>'7-day-average',
					'class'=>'covid-chart lw_hidden',
					'data-graph-container-before'=>1,
					'data-graph-type'=>'line',
					'data-graph-color-1'=>'#F60000',
					'data-graph-color-2'=>'#FF8C00',
					'data-graph-color-3'=>'#4815AA',
					'data-graph-color-4'=>'#4DE94C',
					'data-graph-color-5'=>'#3783FF',
					'data-graph-color-6'=>'#FFEE00',
					'data-graph-color-7'=>'#999999',
					'data-graph-color-8'=>'#666666',
					'data-graph-color-9'=>'#333333'
				],
				'columns'=>[
					[ // add date column
						'column_header'=>'Date',
						'column_attributes'=>[
							// 'data-graph-dash-style'=>'shortdot'
						],
						'field'=>'date',
						'field_attributes'=>[]
					]
				]
			];
			$first_result=current($data);
			$count=(sizeof($first_result)-2)/3; // get the number of locations to display
			for ($i=1;$i<=$count;$i++) { // add location columns (displaying average)
				$params['columns'][]=[
					'column_header'=>$first_result['location_'.$i.'_title'],
					'column_attributes'=>[
						'data-graph-dash-style'=>'shortdot'
					],
					'field'=>'location_'.$i.'_average'
				];
			};
			$params['columns'][]=[ // add location average column
				'column_header'=>'Average',
				'column_attributes'=>[
					// 'data-graph-dash-style'=>'shortdot'
				],
				'field'=>'locations_average'
			];
			$output=$this->getTable($data, $params); // get the table
		}
		else if ($_LW->isLiveWhaleUser()) {
			$output='<p><strong>Error:</strong> '.$this->error.'</p>';
		};
		break;
		
		
	case 'covid_stats_7dayavg':  
	
	// COVID statistics via the Knowi API 
	// https://www.knowi.com/coronavirus-dashboards/covid-19-api/
	// Displays 7 day average of new COVID cases per county per 100K population, last 30 days
	// XPHP usage: <xphp var="covid_stats_7dayavg_per100Kpop" format="json" counties="Alameda County, California" population="1671000"/>
	
    $params['days']=31; // to ensure displaying 30 days, in case today's data isn't available
		$data=$this->getData('knowi', 3600*3, $params); // get data from Knowi data source, caching for 3 hours
		if ($data!==false) {
			$params=[ // set table parameters
				'table_header'=>'7-day average of new cases',
				'table_footer'=>'Data updated: '.$this->getLastRefreshedTime('m/d/y'),
				'table_attributes'=>[
  				// (data-graph-container-before or data-graph-container) and data-graph-type are required
  				// options at highcharttable.org
					'id'=>'covid_stats_7dayavg',
					'class'=>'covid-chart',
					'data-graph-container-before'=>1,
					'data-graph-type'=>'line',
				],
				'columns'=>[
					[ // add date column
						'column_header'=>'Date',
						'column_attributes'=>[
  						// options at highcharttable.org
							//'data-graph-dash-style'=>'shortdot'
						],
						'field'=>'date',
						'field_attributes'=>[]
					]
				]
			];
			$first_result=current($data);
			$count=1;
			while (isset($first_result['location_'.$count.'_newcases'])) { // get the number of locations to display
				$count++;
				if ($count===30) {
					break;
				};
			};
			$count--;
			for ($i=1;$i<=$count;$i++) { // add columns per location
				$params['columns'][]=[
					'column_header'=>'Total Cases ('.$first_result['location_'.$i.'_title'].')',
					'column_attributes'=>[
						'data-graph-skip'=>'1' // don't add this column to generated chart
					],
					'field'=>'location_'.$i.'_cases'
				];
  			$params['columns'][]=[ // add total new cases column
  				'column_header'=>'New Cases ('.$first_result['location_'.$i.'_title'].')',
  				'column_attributes'=>[
						'data-graph-skip'=>'1' // don't add this column to generated chart
  				],
  				'field'=>'location_'.$i.'_newcases'
  			];
  			$params['columns'][]=[ // add total 7 day average per 100k column
  				'column_header'=>'7-day average ('.$first_result['location_'.$i.'_title'].')',
  				'column_attributes'=>[
  				],
  				'field'=>'location_'.$i.'_7dayaverage'
  			];
			};
			$output=$this->getTable($data, $params); // get the table
		}
		else if ($_LW->isLiveWhaleUser()) {
			$output='<p><strong>Error:</strong> '.$this->error.'</p>';
		};
		break;


	case 'covid_stats_7dayavg_per100Kpop':  
	
	// COVID statistics via the Knowi API 
	// https://www.knowi.com/coronavirus-dashboards/covid-19-api/
	// Displays 7 day average of new COVID cases per county per 100K population, last 30 days
	// XPHP usage: <xphp var="covid_stats_7dayavg_per100Kpop" format="json" counties="Alameda County, California" population="1671000"/>
	
    $params['days']=31; // to ensure displaying 30 days, in case today's data isn't available
		$data=$this->getData('knowi', 3600*3, $params); // get data from Knowi data source, caching for 3 hours
		if ($data!==false) {
			$params=[ // set table parameters
				'table_header'=>'7-day average of new cases per 100K population',
				'table_footer'=>'Data updated: '.$this->getLastRefreshedTime('m/d/y'),
				'table_attributes'=>[
  				// (data-graph-container-before or data-graph-container) and data-graph-type are required
  				// options at highcharttable.org
					'id'=>'covid_stats_7dayavg_per100Kpop',
					'class'=>'covid-chart',
					'data-graph-container-before'=>1,
					'data-graph-type'=>'line',
				],
				'columns'=>[
					[ // add date column
						'column_header'=>'Date',
						'column_attributes'=>[
  						// options at highcharttable.org
							//'data-graph-dash-style'=>'shortdot'
						],
						'field'=>'date',
						'field_attributes'=>[]
					]
				]
			];
			$first_result=current($data);
			$count=1;
			while (isset($first_result['location_'.$count.'_newcases'])) { // get the number of locations to display
				$count++;
				if ($count===30) {
					break;
				};
			};
			$count--;
			for ($i=1;$i<=$count;$i++) { // add location columns (displaying total cases)
				$params['columns'][]=[
					'column_header'=>'Total Cases '.$first_result['location_'.$i.'_title'],
					'column_attributes'=>[
						'data-graph-skip'=>'1' // don't add this column to generated chart
					],
					'field'=>'location_'.$i.'_cases'
				];
			};
			for ($i=1;$i<=$count;$i++) { // add location columns (displaying new cases)
				$params['columns'][]=[
					'column_header'=>'New Cases '.$first_result['location_'.$i.'_title'],
					'column_attributes'=>[
						'data-graph-skip'=>'1'  // don't add this column to generated chart
					],
					'field'=>'location_'.$i.'_newcases'
				];
			};
			$params['columns'][]=[ // add total new cases column
				'column_header'=>'New Cases, Total',
				'column_attributes'=>[
					'data-graph-skip'=>'1'  // don't add this column to generated chart
				],
				'field'=>'total_newcases'
			];
			$params['columns'][]=[ // add total population column
				'column_header'=>'Population',
				'column_attributes'=>[
					'data-graph-skip'=>'1'  // don't add this column to generated chart
				],
				'field'=>'total_population'
			];
			$params['columns'][]=[ // add total new cases per 100k column
				'column_header'=>'New Cases Per 100,000 Pop',
				'column_attributes'=>[
					'data-graph-skip'=>'1'  // don't add this column to generated chart
				],
				'field'=>'total_newcasesper100K'
			];
			$params['columns'][]=[ // add total 7 day average per 100k column
				'column_header'=>'7-day average of new cases per 100K population',
				'column_attributes'=>[
				],
				'field'=>'total_7dayaverageper100K'
			];
			$output=$this->getTable($data, $params); // get the table
		}
		else if ($_LW->isLiveWhaleUser()) {
			$output='<p><strong>Error:</strong> '.$this->error.'</p>';
		};
		break;
};
return $output;
}


// Code below this line shouldn't require customization


public function onBeforeOutput($buffer) { // parse template before XPHP
global $_LW;
$find=[];
$replace=[];
$matches=[];
@preg_match_all('~<xphp var="('.implode('|', $_LW->REGISTERED_APPS['data_visualizer']['custom']['enabled']).')"[^>]+?>~', $buffer, $matches); // for all enabled variables
if (!empty($matches[1])) {
	foreach($matches[1] as $key=>$val) { // loop through them
		if ($sxe=@simplexml_load_string($matches[0][$key])) { // and parse
			$var_name=(string)$sxe['var']; // get var name
			$params=[];
			foreach($sxe->attributes() as $key2=>$val2) { // and params from the attributes
				if ($key2!='var') {
					$params[$key2]=(string)$val2;
				};
			};
			if ($output=$this->getVariable($var_name, $params)) { // if variable output obtained
				$find[]=$matches[0][$key]; // add it to queue for output
				$replace[]=$output;
			};
		};
	};
	if (!empty($find)) { // swap in all variable output
		$buffer=str_replace($find, $replace, $buffer);
	};
};
return $buffer;
}

public function getData($data_source, $ttl, $params, $mode='async') { // gets data from a data source
global $_LW;
$cache_key='data_visualizer_'.hash('md5', serialize([$data_source, $ttl, $params])); // get cache key
$data_source_key='data_visualizer_'.hash('md5', $data_source); // get cache key for data source
$has_fresh_cache=(file_exists($_LW->INCLUDES_DIR_PATH.'/data/data_visualizer/'.$cache_key) && (empty($ttl) ||  @filemtime($_LW->INCLUDES_DIR_PATH.'/data/data_visualizer/'.$cache_key)>=$_SERVER['REQUEST_TIME']-$ttl)); // check if cache is still fresh
$will_refresh=((!empty($_LW->_GET['refresh']) && $_LW->isLiveWhaleUser()) || empty($has_fresh_cache)); // check if refresh is needed
header('X-Data-Visualizer: '.(!empty($will_refresh) ? 'refresh ('.$mode.')' : 'cached')); // send header
if (!empty($will_refresh) && (!empty($_LW->_GET['refresh']) && $_LW->isLiveWhaleUser())) { // if forcibly refreshing
	$mode='sync'; // use sync
};
if ($mode=='async' && !empty($will_refresh)) { // if refreshing async
	$_LW->getUrl('http://127.0.0.1'.$_LW->CONFIG['LIVE_URL'].'/data_visualizer/refresh/'.base64_encode(serialize(['data_source'=>$data_source, 'ttl'=>$ttl, 'params'=>$params])).'?lw_auth='.rawurlencode($_LW->getAuthToken(0)), false, false, [CURLOPT_HTTPHEADER=>['Host: '.$_LW->CONFIG['HTTP_HOST'], 'X-Forwarded-Proto: https']]); // refresh it async
};
if (!($mode=='sync' && !empty($will_refresh))) { // if not refreshing sync
	if (file_exists($_LW->INCLUDES_DIR_PATH.'/data/data_visualizer/'.$cache_key)) { // if cache entry exists
		$this->last_refreshed_time=filemtime($_LW->INCLUDES_DIR_PATH.'/data/data_visualizer/'.$cache_key); // always return it
		return @unserialize(file_get_contents($_LW->INCLUDES_DIR_PATH.'/data/data_visualizer/'.$cache_key));
	};
};
if (file_exists($_LW->INCLUDES_DIR_PATH.'/client/modules/data_visualizer/includes/data_sources/'.$data_source.'.php') && (!file_exists($_LW->INCLUDES_DIR_PATH.'/data/data_visualizer/'.$cache_key.'.refreshing') || $_LW->isInternalRequest() || @filemtime($_LW->INCLUDES_DIR_PATH.'/data/data_visualizer/'.$cache_key.'.refreshing')<$_SERVER['REQUEST_TIME']-60) && (!file_exists($_LW->INCLUDES_DIR_PATH.'/data/data_visualizer/'.$data_source_key.'.requested') || @filemtime($_LW->INCLUDES_DIR_PATH.'/data/data_visualizer/'.$data_source_key.'.requested')<$_SERVER['REQUEST_TIME']-30)) { // if data source exists and not already refreshing and we haven't requested in the last 30 seconds from this data source
	@touch($_LW->INCLUDES_DIR_PATH.'/data/data_visualizer/'.$data_source_key.'.requested');
	@touch($_LW->INCLUDES_DIR_PATH.'/data/data_visualizer/'.$cache_key.'.refreshing');
	$_LW->protectedInclude($_LW->INCLUDES_DIR_PATH.'/client/modules/data_visualizer/includes/data_sources/'.$data_source.'.php'); // load it
	$class_name='DataSource'.ucfirst($data_source); // get class name
	if (class_exists($class_name)) {  // if data source loaded properly
		if (!isset($this->data_sources)) {
			$this->data_sources=[];
		};
		if (!isset($this->data_sources[$class_name])) { // load new object
			$this->data_sources[$class_name]=new $class_name;
		};
		if (isset($this->data_sources[$class_name]) && is_object($this->data_sources[$class_name])) { // if object loaded
			$this->data_sources[$class_name]->error='';
			$output=$this->data_sources[$class_name]->getData($params); // get the data from the data source
			if (!is_dir($_LW->INCLUDES_DIR_PATH.'/data/data_visualizer')) { // ensure cache dir exists
				@mkdir($_LW->INCLUDES_DIR_PATH.'/data/data_visualizer');
			};
			if (!empty($this->data_sources[$class_name]->error)) { // if failure
				$this->error=$this->data_sources[$class_name]->error; // inherit any errors
				if (file_exists($_LW->INCLUDES_DIR_PATH.'/data/data_visualizer/'.$cache_key)) { // and use previous cache if possible
					@touch($_LW->INCLUDES_DIR_PATH.'/data/data_visualizer/'.$cache_key);
					$output=@unserialize(file_get_contents($_LW->INCLUDES_DIR_PATH.'/data/data_visualizer/'.$cache_key));
					if (!empty($output)) {
						@unlink($_LW->INCLUDES_DIR_PATH.'/data/data_visualizer/'.$cache_key.'.refreshing');
						return $output;
					};
				};
				@unlink($_LW->INCLUDES_DIR_PATH.'/data/data_visualizer/'.$cache_key.'.refreshing');
				return false;
			}
			else { // else cache the output
				if (!empty($ttl)) {
					@file_put_contents($_LW->INCLUDES_DIR_PATH.'/data/data_visualizer/'.$cache_key, serialize($output), LOCK_EX);
					$this->last_refreshed_time=filemtime($_LW->INCLUDES_DIR_PATH.'/data/data_visualizer/'.$cache_key);
				};
				@unlink($_LW->INCLUDES_DIR_PATH.'/data/data_visualizer/'.$cache_key.'.refreshing');
				return $output; // and return the data
			};
		}
		else {
			$this->error='Could not load specified data source "'.$_LW->setFormatClean($data_source).'".';
		};
	}
	else {
		$this->error='Could not load specified data source "'.$_LW->setFormatClean($data_source).'".';
	};
}
else {
	$this->error='Could not locate specified data source "'.$_LW->setFormatClean($data_source).'".';
};
if (!empty($this->error)) {
	$_LW->logError('Data visualizer: '.$this->error);
};
return false;
}

protected function getTable($data, $params) { // renders a data table for output
global $_LW;
$xml=$_LW->getNew('xpresent'); // create XML object
if (!empty($params['table_attributes'])) {
	foreach($params['table_attributes'] as $key=>$val) {
		$params['table_attributes'][$key]=$_LW->setFormatClean($val);
	};
};
$table=$xml->insert($xml->table('', (!empty($params['table_attributes']) ? $params['table_attributes'] : [])));  // add table
if (!empty($params['columns'])) { // if there are columns
	$thead=$table->insert($xml->thead());  // add the table head
	$tr=$thead->insert($xml->tr());
	foreach($params['columns'] as $column) { // add each header row
		if (!empty($column['column_header'])) {
			if (!empty($column['column_attributes'])) {
				foreach($column['column_attributes'] as $key=>$val) {
					$column_attributes[$key]=$_LW->setFormatClean($val);
				};
			};
			$tr->insert($xml->th($_LW->setFormatClean($column['column_header']), (!empty($column['column_attributes']) ? $column['column_attributes'] : [])));
		};
	};
	$body=$table->insert($xml->tbody()); // add the table body
	if (!empty($data)) {
		foreach($data as $row) { // loop through data
			foreach($row as $key=>$val) {
				$row[$key]=$_LW->setFormatClean($val);
			};
			$tr=$body->insert($xml->tr());
			foreach($params['columns'] as $column) { // add each column
				if (!empty($column['column_header'])) {
					$tr->insert($xml->td((!empty($row[$column['field']]) ? $row[$column['field']] : ''), (!empty($column['field_attributes']) ? $column['field_attributes'] : [])));
				};
			};
		};
	};
};
$output=$xml->toXHTML(); // output table
if (!empty($params['table_header'])) { // prepend table header if specified
	$output='<h3>'.$_LW->setFormatClean($params['table_header']).'</h3>'.$output;
};
if (!empty($params['table_footer'])) { // prepend table header if specified
	$output=$output.'<small>'.$_LW->setFormatClean($params['table_footer']).'</small>';
};
return $output;
}

protected function getLastRefreshedTime($format) { // gets the last refresh time of the data
global $_LW;
if (!empty($this->last_refreshed_time)) {
	return $_LW->toDate($format, $this->last_refreshed_time);
};
return '';
}

}

?>