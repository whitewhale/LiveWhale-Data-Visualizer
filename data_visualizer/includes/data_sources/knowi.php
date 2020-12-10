<?php

/*

This code generates results from the KNOWI coronavirus Raw County Level Data API. 
The Knowi API draws data from a variety of sources. 
Details: https://www.knowi.com/coronavirus-dashboards/covid-19-api/

This code accepts input as County, State or numeric FIPS county code. It generates the following fields:

date

location_1_title
location_1_cases
location_1_newcases
location_1_7dayaverage

location_2_title
location_2_cases
location_2_newcases
location_2_7dayaverage

etc.

total_population
total_cases
total_newcases
total_7dayaverage
total_7dayaverageper100K
total_newcasesper100K

*/

class DataSourceKnowi {
public $error='';

function getData($params) { // gets data from the data source
global $_LW;
$output=[];
$this->error='';
if (!empty($params['format'])) { // if valid format
	if (!empty($params['counties'])) { // if valid counties
		switch($params['format']) {
			case 'json': // for JSON format
				$output=[];
				$base_url='https://knowi.com/api/data/ipE4xJhLBkn8H8jisFisAdHKvepFR5I4bGzRySZ2aaXlJgie?entityName=Raw%20County%20level%20Data&exportFormat=json&c9SqlFilter='; // set base url
				$query='';
				$counties=[];
				foreach(preg_split('~\s*;\s*~', $params['counties']) as $county) { // build county clause
					if (empty($county)) {
						continue;
					};
					if (strpos($county, ',')) { // if County, State
						$county_parts=explode(',', $county);
						$counties[]='(County LIKE ' . trim($county_parts[0]) . ' AND State LIKE ' . trim($county_parts[1]) . ')';
					}
					else {
						$counties[]='(CountyFIPS=' . trim($county) . ')';
					};
				};
				$query=implode(' OR ', $counties); // create query
				$base_url.=rawurlencode('SELECT * WHERE Type LIKE Confirmed AND ('.$query.')'); // and append query to base url
				if ($json=$_LW->getUrl($base_url, true, false, [CURLOPT_TIMEOUT=>60])) { // fetch the JSON data
					if ($json=@json_decode($json, true)) { // parse the JSON data
						// generate totals:
						foreach($json as $val) { // loop through API results from today, backwards in time
							if (!empty($val['Date']) && !empty($val['County']) && !empty($val['State']) && !empty($val['values'])) { // if valid result
								$val['date']=$_LW->toTS('12:00am', (int)$val['Date']/1000); // remove nanoseconds from date
								if ($val['date']>=$_SERVER['REQUEST_TIME']-(86400*(!empty($params['days']) ? $params['days']+7 : 37))) { // only process last X days
									$val['location']=$_LW->setFormatClean($val['County'].', '.$val['State']);
									$val['cases']=(int)$val['values'];
									if (!isset($results[$val['date']])) {
										$results[$val['date']]=[];
									};
									$results[$val['date']][$val['location']]=[ // store fields
										'date'=>$val['date'],
										'location'=>$val['location'],
										'cases'=>(int)$val['cases']
									];
								};
							};
						};
						ksort($results); // reverse sort the data by date
						foreach(array_keys($results) as $date) { // calculate new cases
							foreach(array_keys($results[$date]) as $location) {
								$results[$date][$location]['newcases']=(isset($last_date) ? $results[$date][$location]['cases']-$results[$last_date][$location]['cases'] : $results[$date][$location]['cases']);
							};
							$last_date=$date;
						};
						// generate averages:
						$tmp=[];
						foreach(array_keys($results) as $key) { // alias the averages without date indexes
							$tmp[]=&$results[$key];
						};
						for ($i=sizeof($tmp)-1;$i>=0;$i--) { // calculate 7-day average for each location
							foreach($tmp[$i] as $location=>$value) {
								$average=[];
								for ($n=0;$n<7;$n++) {
									$x=$i-$n;
									if (isset($tmp[$x])) {
										$average[]=$tmp[$x][$location]['newcases'];
									};
								};
								$tmp[$i][$location]['7dayaverage']=number_format((array_sum($average)/sizeof($average)), 1); // add average
							};
						};
						unset($tmp);
						if (sizeof($results)>=7) {
							for ($i=0;$i<7;$i++) { // trim off the first 7 days because we need 6 prior days for averages
								unset($results[key($results)]);
							};
						};
						$total_newcasesper100K=[];
						foreach($results as $key=>$val) { // format final output
							$item=[];
							$item['date']=$_LW->toDate('m/d/Y', $key);
							$dates[]=$item['date'];
							$count=1;
							$item['total_7dayaverage']=[];
							$item['total_cases']=0;
							$item['total_newcases']=0;
							foreach($val as $key2=>$val2) {
								$item['location_'.$count.'_title']=$key2;
								$item['location_'.$count.'_cases']=(int)$val2['cases'];
								$item['location_'.$count.'_newcases']=(int)$val2['newcases'];
								$item['location_'.$count.'_7dayaverage']=(int)$val2['7dayaverage'];
								$item['total_population']=(!empty($params['population']) ? number_format((int)$params['population']) : 0);
								$item['total_cases']+=$item['location_'.$count.'_cases'];
								$item['total_newcases']+=$item['location_'.$count.'_newcases'];
								$item['total_7dayaverage'][]=(int)$val2['7dayaverage'];
								$count++;
							};
							$item['total_newcasesper100K']=(!empty($params['population']) ? number_format($item['total_newcases']/$params['population']*100000, 1) : 0); // calculate total new cases per 100k
							$total_newcasesper100K[]=$item['total_newcasesper100K'];
							$item['total_7dayaverage']=number_format(array_sum($item['total_7dayaverage'])/sizeof($item['total_7dayaverage']), 1); // calculate the total 7 day average
							$tmp=array_slice($total_newcasesper100K, -7, 7);
							$item['total_7dayaverageper100K']=number_format(array_sum($tmp)/sizeof($tmp), 1); // calculate the total 7 day average per 100K
							$output[]=$item;
						};
					}
					else { // give error if unable to read data
						$this->error='Unable to read data from the knowi API.';
						return false;
					};
				}
				else { // give error if unable to retrieve data
					$this->error='Unable to retrieve data from the knowi API'.(!empty($_LW->last_error) ? ': '.$_LW->last_error : '').'.';
					return false;
				};
				break;
		};
	}
	else { // give error if counties missing
		$this->error='You must specify a semicolon-separated list of counties for the knowi API.';
		return false;
	};
}
else { // give error if format missing
	$this->error='You must specify a format for the knowi API.';
	return false;
};
return $output;
}

}

?>