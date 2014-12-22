<?php
function syscall ($cmd, $cwd) {
	info("Executing command $cmd in directory $cwd");
	$descriptorspec = array(
		1 => array('pipe', 'w'), // stdout
		2 => array('pipe', 'w') // stderr 
	);
	$resource = proc_open($cmd, $descriptorspec, $pipes, $cwd);
	if (is_resource($resource)) {
		$output = stream_get_contents($pipes[1]);
		$output .= "\nErrors: " . stream_get_contents($pipes[2]);
		fclose($pipes[1]);
		fclose($pipes[2]);
		proc_close($resource);
		return $output; 
	}
}
function info($message){
	error_log($message);
}
function error($message){
	error_log($message);
	exit($message);
}
if (!empty($_POST['payload'])) {
	
	$config_str = file_get_contents('config.json');
	$global_config = json_decode($config_str, true);
	if ($global_config == null){
		error('Exception reading global configuration from : ' . $config_str);
	}
	
	// set basic settings
	ignore_user_abort(true);
	set_time_limit($global_config['time_limit']);
	
	// read the payload from GitHub
	try{
		$payload = json_decode($_POST['payload'], true);
	} catch(Exception $e) {
		error('Exception decoding GitHub JSON ' . $e->getMessage());
	}
	
	// process the payload
	$url = $payload['repository']['url'];
	info("Finding configuration for: $url");
	
	$config = $global_config['sites'][$url];
	if($config != null){
		try {
			info('Updating site ' . $config['id']);
			
			$project_dir = $global_config['projects_root'] . '/' . $config['id'];
			if(array_key_exists('project_dir', $config)){
				$project_dir = $config['project_dir'];
			}
			
			info('Updating GIT Repository');
			info(syscall($global_config['git_path'] . ' pull', $project_dir));
			
			$jekyll_args = 'build';
			if(array_key_exists('jekyll_args', $config)){
				$jekyll_args = $config['jekyll_args'];
			}
			
			info('Running Jekyll');
			info(syscall($global_config['jekyll_path'] . ' ' . $jekyll_args, $project_dir));
			
			if(array_key_exists('additional_commands', $config)) {
				foreach($config['additional_commands'] as $additional_command) {
					info(syscall($additional_command, $project_dir));
				}
			}
			
			info("Update complete");
		} catch(Exception $e) {
			error('Exception updating target site: ' . $e.getMessage());
		}
	} else {
		error("No configuration found for $url");
	}
} else {
	error("No payload specified!");
}
?>
