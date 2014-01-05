<?php
function syscall ($cmd, $cwd) {
	$descriptorspec = array(
		1 => array('pipe', 'w') // stdout is a pipe that the child will write to
	);
	$resource = proc_open($cmd, $descriptorspec, $pipes, $cwd);
	if (is_resource($resource)) {
		$output = stream_get_contents($pipes[1]);
		fclose($pipes[1]);
		proc_close($resource);
		return $output; 
	}
}
if (!empty($_POST['payload'])) {
	
	// read the global configuration
	$config = json_decode(file_get_contents('config/global.json'));
	
	// set basic settings
	ignore_user_abort(true);
	set_time_limit($config->time_limit);
	
	// read the payload from GitHub
	$payload = json_decode($_POST['payload']);
	
	// process the payload
	try{
		// pull from master
		error_log("Running Git Pull");
		$result = syscall('git pull', '/var/scratch/[site]');
		error_log($result);
		error_log("Running Jekyll");
		$result2 = syscall('/usr/local/bin/jekyll build -d [out]', '[site dir]');
		error_log($result2);
	
		// send us the output
		error_log("Update complete");
	
	}catch(Exception $e){
		 error_log('Caught exception attempting to update labs: ' .  $e->getMessage()) . "\n" . $e->getTraceAsString();
	}
}

?>
