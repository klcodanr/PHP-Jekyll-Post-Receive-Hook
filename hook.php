<?php

//error_reporting(E_ALL);
ignore_user_abort(true);
set_time_limit(0);

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

// GitHub will hit us with POST (http://help.github.com/post-receive-hooks/)
if (!empty($_POST['payload'])) {
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
