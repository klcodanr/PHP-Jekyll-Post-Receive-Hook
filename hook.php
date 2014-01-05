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
function info($message){
	$date = date('d.m.Y h:i:s'); 
	error_log($date . ' - INFO - ' . $message);
}
function error($message){
	$date = date('d.m.Y h:i:s'); 
	error_log($date . ' - ERROR - ' . $message);
	exit($message);
}
if (!empty($_POST['payload'])) {
	
	try {
		require('config.php');
	} catch (Exception $e) {
		error('Exception reading global configuration: ' . $e->getMessage());
	}
	
	// set basic settings
	ignore_user_abort(true);
	set_time_limit($global_config->time_limit);
	
	// read the payload from GitHub
	try{
		$payload = json_decode($_POST['payload']);
	} catch(Exception $e) {
		error('Exception decoding GitHub JSON ' . $e->getMessage());
	}
	
	// process the payload
	$url = $payload->repository->url;
	info("Finding configuration for: $url");
	
	$config = $global_config->servers->$url;
	if($config != null){
		try {
			info('Updating configuration ' . $config->id);
			
			$project_dir = $global_config . '/' . $config->id;
			if($config->project_dir != null){
				$project_dir = $config->project_dir;
			}
			
			info('Updating GIT Repository');
			info(syscall($global_config->git_path . ' pull', $project_dir));
			
			$jekyll_args = 'build';
			if($config->jekyll_args != null){
				$jekyll_args = $config->jekyll_args;
			}
			
			info('Running Jekyll');
			info(syscall($global_config->jekyll_path . ' ' . $jekyll_args), $project_dir);
			
			info("Update complete");
		} catch(Exception $e) {
			error('Exception updating target site: ' . $e.getMessage());
		}
	} else {
		error("No configuration found for $url");
	}
}
?>
