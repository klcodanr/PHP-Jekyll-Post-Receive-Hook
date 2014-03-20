<?php
// Copyright (c) 2014 Daniel Klco and contributors
// Released under the MIT License
// http://opensource.org/licenses/MIT
function syscall ($cmd, $cwd, $env) {
	info("Executing command $cmd in directory $cwd");
	info("Environment variables: ".print_r($env, true););
	$descriptorspec = array(
		1 => array('pipe', 'w'), // stdout
		2 => array('pipe', 'w') // stderr 
	);
	$resource = proc_open($cmd, $descriptorspec, $pipes, $cwd, $env);
	if (is_resource($resource)) {
		$sysout = stream_get_contents($pipes[1]);
		info("Output: $sysout");
		$syserr = stream_get_contents($pipes[2]);
		info("Error: $syserr");
		fclose($pipes[1]);
		fclose($pipes[2]);
		proc_close($resource);
		if($syserr == ''){
			return $sysout;
		} else {
			throw new Exception("Error calling command '$cmd' in directory '$cwd': $syserr");
		}
	}
	throw new Exception("Invalid system call $cmd in directory $cwd");
}
function info($message){
	error_log($message);
}
function error($message){
	error_log($message);
	header("X-Error-Message: Unable to update site", true, 500);
	echo($message);
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
			
			$env = array();
			if(array_key_exists('env', $global_config)){
				$env = $global_config['env'];
			}
			
			$project_dir = $global_config['projects_root'] . '/' . $config['id'];
			if(array_key_exists('project_dir', $config)){
				$project_dir = $config['project_dir'];
			}
			
			info('Updating GIT Repository');
			echo(syscall($global_config['git_path'] . ' pull', $project_dir, $env));
			
			
			$jekyll_args = 'build';
			if(array_key_exists('jekyll_args', $config)){
				$jekyll_args = $config['jekyll_args'];
			}
			
			info('Running Jekyll');
			echo(syscall($global_config['jekyll_path'] . ' ' . $jekyll_args, $project_dir, $env));
			
			if(array_key_exists('additional_commands', $config)) {
				foreach($config['additional_commands'] as $additional_command) {
					info(syscall($additional_command, $project_dir, $env));
				}
			}
			
			info("Update complete");
		} catch(Exception $e) {
			error('Exception updating target site: ' . $e->getMessage());
		}
	} else {
		error("No configuration found for $url");
	}
}
?>
