<?php

/* average check */
function _check_phpfpm_average(array $my = array()) {
	/* get processes matching fpmbin */
	$ps = _ps_grep($my['fpmbin'] . '|grep -v master');
	/* init count and total */
	$count = 0; $total = 0;
	/* split return on new line and sum sizes */
	foreach ($ps as $line => $data) {
		/* check validity */
		if (!empty($data['rss']) && is_numeric($data['rss'])) {
			/* build total */
			$total += ((int)$data['rss'] * 1024);
			/* increase count */
			$count ++;
		}
	}
	/* check results */
	if (($total > 0) && ($count > 0)) {
		print "average.value " . ($total / $count) . "\n";
	} else {
		print "average.value U\n";
	}
}

/* connection check */
function _check_phpfpm_connection(array $my = array()) {
	/* fetch stats */
	if (($data = _fetch_status($my['url'])) === false) {
		/* this will just return U below */
		$data = array();
	}

	/* check and print idle value */
	if (isset($data['accepted conn'])) {
		print "accepted.value " . $data['accepted conn'] . "\n";
	} else {
		print "accepted.value U\n";
	}
}

/* memory check */
function _check_phpfpm_memory(array $my = array()) {
	/* get processes matching fpmbin */
	$ps = _ps_grep($my['fpmbin'] . '|grep -v master');
	/* init count and total */
	$count = 0; $total = 0;
	/* split return on new line and sum sizes */
	foreach ($ps as $line => $data) {
		/* check validity */
		if (!empty($data['rss']) && is_numeric($data['rss'])) {
			/* build total */
			$total += ((int)$data['rss'] * 1024);
			/* increase count */
			$count ++;
		}
	}
	/* check results */
	if (($total > 0) && ($count > 0)) {
		print "memory.value " . $total . "\n";
	} else {
		print "memory.value U\n";
	}
}

/* process check */
function _check_phpfpm_process(array $my = array()) {
	/* fetch stats */
	if (($data = _fetch_status($my['url'])) === false) {
		/* this will just return U below */
		$data = array();
	}

	/* check and print idle value */
	if (isset($data['total processes'])) {
		print "process.value " . $data['total processes'] . "\n";
	} else {
		print "process.value U\n";
	}
}

/* status check */
function _check_phpfpm_status(array $my = array()) {
	/* fetch stats */
	if (($data = _fetch_status($my['url'])) === false) {
		/* this will just return U below */
		$data = array();
	}

	/* check and print idle value */
	if (isset($data['idle processes'])) {
		print "idle.value " . $data['idle processes'] . "\n";
	} else {
		print "idle.value U\n";
	}

	/* check and print active value */
	if (isset($data['active processes'])) {
		print "active.value " . $data['active processes'] . "\n";
	} else {
		print "active.value U\n";
	}

	/* check and print total value */
	if (isset($data['total processes'])) {
		print "total.value " . $data['total processes'] . "\n";
	} else {
		print "total.value U\n";
	}
}


/* little curl helper */
function _fetch_status($url) {
	/* init curl */
	if (($ch = curl_init()) === false) {
		/* return false */
		return false;
	}
	/* curl options array */
	$options = array(
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_HEADER => false,
		CURLOPT_SSL_VERIFYHOST => false,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_URL => $url,
	);
	/* set curl options */
	if (curl_setopt_array($ch, $options) !== true) {
		/* return false */
		return false;
	}
	/* attempt to fetch url */
	$data = curl_exec($ch);
	/* close curl */
	curl_close($ch);
	/* attempt to decode status */
	if (($json = json_decode($data, true)) === null) {
		/* return false */
	}
	/* debug */
	//print "json == " . print_r($json, true) . "\n\n";
	/* return decoded json array */
	return $json;
}

/* grep process list */
function _ps_grep($string) {
	/* declare header */
	$head = array('user', 'pid', 'cpu', 'mem', 'vsz', 'rss', 'tty', 'stat', 'started', 'time');
	/* get process list from system */
	$exec = shell_exec('ps auxww|grep ' . $string . '|grep -v grep|' .
		'awk \'{print $1"|"$2"|"$3"|"$4"|"$5"|"$6"|"$7"|"$8"|"$9"|"$10}\'');
	/* declare process list and init count */
	$procs = array();
	$count = 0;
	/* split return on new line and sum sizes */
	foreach (explode("\n", $exec) as $line) {
		/* explode line */
		$parts = explode("|", $line);
		/* valididty check */
		if (count($parts) < 10) continue;
		/* build an associative array */
		for ($i = 0; $i < 10; $i++) {
			$procs[$count][$head[$i]] = trim($parts[$i]);
		}
		/* increase count */
		$count++;
	}
	/* return process array */
	return $procs;
}

/* config print function */
function _print_config(array $config = array()) {
	/* loop through config array and print */
	foreach ($config as $name => $value) {
		print "$name $value\n";
	}
	/* exit */
	exit(0);
}

/* supported operating system check */
function _check_osname() {
	/* get os name */
	$osname = php_uname('s');
	/* process list parsing is os specific
	 * we have only tested the following */
	switch (strtolower($osname)) {
		case 'linux':
		case 'freebsd':
		case 'openbsd':
		case 'netbsd':
		case 'darwin':
			return true;
		default:
			return false;
			break;
	}
}

/* default autoconf */
function _autoconf_default(array $my = array()) {
	/* ensure we have curl */
	if (!extension_loaded('curl')) {
		print "no (curl support missing in php binary)\n";
		exit(1);
	}
	/* ensure we jave json */
	if (!extension_loaded('json') || !function_exists('json_decode')) {
		print "no (json support missing in php binary)\n";
		exit(1);
	}
	/* okay make sure we can connect and fetch status */
	if (($data = _fetch_status($my['url'])) === false) {
		print "no (could not fetch or decode status)\n";
		exit(1);
	}
	/* default - all okay */
	print "yes\n";
	exit(0);
}


/* average config */
function _config_phpfpm_average(array $my = array()) {
	/* set config options */
	$config = array(
		'graph_title' => 'PHP-FPM Average Process Size - ' . $my['pool'],
		'graph_args' => '--base 1024 -l 0',
		'graph_vlabel' => 'Average Process Size',
		'graph_category' => 'PHP',
		'graph_info' => 'This graph shows the average size of all php-fpm processes',
		'average.label' => 'Average Process Size',
		'average.draw' => 'LINE2',
	);
	/* print the config */
	_print_config($config);
}

/* average autoconf */
function _autoconf_phpfpm_average(array $my = array()) {
	if (_check_osname() === true) {
		print "yes\n";
		exit(0);
	} else {
		print "no (possibly unsupported operating system)\n";
		exit(1);
	}
}

/* connections config */
function _config_phpfpm_connection(array $my = array()) {
	/* set config options */
	$config = array(
		'graph_title' => 'PHP-FPM Accepted Connections - ' . $my['pool'],
		'graph_args' => '--base 1024 -l 0',
		'graph_vlabel' => 'Accepted Connections',
		'graph_category' => 'PHP',
		'graph_info' => 'This graph shows the connections accepted by all php-fpm processes',
		'accepted.label' => 'Accepted',
		'accepted.draw' => 'AREA',
		'accepted.type' => 'DERIVE',
		'accepted.min' => 0,
	);
	/* print the config */
	_print_config($config);
}

/* memory config */
function _config_phpfpm_memory(array $my = array()) {
	/* set config options */
	$config = array(
		'graph_title' => 'PHP-FPM Memory Usage - ' . $my['pool'],
		'graph_args' => '--base 1024 -l 0',
		'graph_vlabel' => 'RAM',
		'graph_category' => 'PHP',
		'graph_info' => 'This graph shows the total memory usage of all php-fpm processes',
		'memory.label' => 'Memory',
	);
	/* print the config */
	_print_config($config);
}

/* memory autoconf */
function _autoconf_phpfpm_memory(array $my = array()) {
	if (_check_osname() === true) {
		print "yes\n";
		exit(0);
	} else {
		print "no (possibly unsupported operating system)\n";
		exit(1);
	}
}

/* process config */
function _config_phpfpm_process(array $my = array()) {
	/* set config options */
	$config = array(
		'graph_title' => 'PHP-FPM Total Processes - ' . $my['pool'],
		'graph_args' => '--base 1024 -l 0',
		'graph_vlabel' => 'Total Processes',
		'graph_category' => 'PHP',
		'graph_info' => 'This graph shows the total number of all running php-fpm processes',
		'process.label' => 'Processes',
		'process.draw' => 'LINE2',
		'process.info' => 'The current number of php-fpm processes',
	);
	/* print the config */
	_print_config($config);
}

/* status config */
function _config_phpfpm_status(array $my = array()) {
	/* set config options */
	$config = array(
		'graph_title' => 'PHP-FPM Status - ' . $my['pool'],
		'graph_args' => '--base 1024 -l 0',
		'graph_vlabel' => 'Connections',
		'graph_category' => 'PHP',
		'graph_order' => 'idle active total',
		'graph_info' => 'This graph shows php-fpm connection status',
		'idle.label' => 'Idle',
		'idle.draw' => 'AREA',
		'active.label' => 'Active',
		'active.draw' => 'AREA',
		'total.label' => 'Total',
		'total.draw' => 'STACK',
	);
	/* print the config */
	_print_config($config);
}