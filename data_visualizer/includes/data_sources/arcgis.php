<?php

/*

The arcgis data source returns data from the arcgis.csv file in the format specified by the header row.

*/

class DataSourceArcgis {
public $error='';

function getData($params) { // gets data from the data source
global $_LW;
$output=[];
$this->error='';
$base_url='https://opendata.arcgis.com/datasets/b913e9591eae4912b33dc5b4e88646c5_10.csv?where=NAME%20%3D%20%27Rock%27'; // base url for CSV data
ini_set('auto_detect_line_endings', true); // auto detect line endings
if ($fp=@fopen($base_url, 'r')) { // open CSV
	$results=[];
	while (($row=fgetcsv($fp, 0, ','))!==false) { // get all rows
		$results[]=$row;
	};
	fclose($fp); // close CSV
	if (!empty($results)) { // if there were results
		$keys=$results[0];
		foreach($results as $key=>$val) { // format final results
			if ($key!==0) {
				$item=array_combine($keys, $val);
				if ($ts=$_LW->toTS($item['DATE'])) {
					if ($ts>=$_SERVER['REQUEST_TIME']-(86400*(!empty($params['days']) ? $params['days'] : 30))) { // last X days only
						$output[$ts]=$item;
					};
				};
			};
		};
		ksort($output);
		foreach($output as $key=>$val) { // format data
			$output[$key]['DATE']=$_LW->toDate('m/d/Y', $_LW->toTS($output[$key]['DATE']));
			$output[$key]['NAME'].=' County';
		};
	}
	else { // give error if CSV could not be rread
		$this->error='Could not parse the arcgis CSV.';
		return false;
	};
}
else { // give error if CSV could not be rread
	$this->error='Could not read the arcgis CSV.';
	return false;
};
return $output;
}

}

?>