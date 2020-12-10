<?php

if (!empty($LIVE_URL['REQUEST'])) { // if valid request
	$command=@array_shift($LIVE_URL['REQUEST']); // get command
	switch($command) {
		case 'refresh':
			set_time_limit(60);
			require $_SERVER['DOCUMENT_ROOT'].'/livewhale/nocache.php';
			if (!empty($_LW->_GET['lw_auth'])) { // if authenticated
				$args=@array_shift($LIVE_URL['REQUEST']); // get args
				if (!empty($args)) { // decode args
					$args=@unserialize(base64_decode($args));
				};
				if (!empty($args['data_source']) && !empty($args['ttl'])) { // if valid args
					$_LW->initModule('application', 'data_visualizer');
					$_LW->a_data_visualizer->getData($args['data_source'], $args['ttl'], $args['params'], 'sync'); // refresh sync
				};
			};
			break;
	};
};
exit;

?>