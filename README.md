PHP Jekyll Post Receive Hook
===========================

A post commit hook for Jekyll, created in PHP.  This hook supports multiple sites being
built using Jekyll and managed through GitHub repositories and is perfect for those using 
a VPS or server to host Jekyll sites.

## Dependencies

This Post Receive Hook requires the following to be installed on the server to work:

* Apache httpd
* PHP5
* Jekyll
* GIT

## Setup

To setup and use this Post Receive Hook:

1. Setup a virtual host in Apache for the Post Receive Hook
1. Clone this repository: `git clone https://github.com/klcodanr/PHP-Jekyll-Post-Recieve-Hook [my-vhost-name]` 
    into the virtual host directory
1. Copy the sample-config.json to config.json and update the [settings](#Settings) for your environment
1. Configure the permissions on your server so the web server user has permissions to 
    write to a project and output folder
1. Update your GitHub Repository to add the [webook](https://help.github.com/articles/post-receive-hooks)
    http://[your-virtual-host]/hook.php

## Logging

The hook will log every request received to the error log configured in Apache.  If you 
are unsure if your hook is working, this file should provide detailed logs for you to 
analyze.

## Settings

The config.json file is used to configure the PHP Jekyll Post Receive Hook.  The format of
the file is as follows:

	{
	  "time_limit": 0,
	  "jekyll_path": "/usr/local/bin/jekyll",
	  "git_path": "git",
	  "projects_root": "/var/scratch",
	  "sites": {
	    "https://github.com/user/repo":{
	      "id": "repo",
	      "jekyll_args": "build -d /var/www/html",
	      "project_dir": "/var/somewhereelse/project",
	      "additional_commands": [
	      	"pwd",
	      	"ps -ef | grep ruby"
	      ]
	    }
	  }
	}
	
These settings mean:

* **time_limit** - The limit for the PHP script to run, generally 0 or a very large number
* **jekyll_path** - The path to the Jekyll executable, often `/usr/local/bin/jekyll`
* **git_path** - The path to the git executable, generally should be in the path
* **projects_root** - The root path for the projects folder.  If a site does not have a 
    path set, the path for the project for that site will be {projects_root}/{site.id}
* **sites** - Holds all of the sites configurations, each site configuration is keyed by 
    site's GitHub repository URL
    
Each site can have the following settings:

* **id** - *required*, an id for the site, generally probably the same as the repository name
* **jekyll_args**  - Arguments passed into the jekyll command, if not specified, build will 
    be used
* **project_dir** - The project directory for the site, will be used instead of concatenating 
    the `projects_root` and site `id`
* **additional_commands** - each of these commands will be executed after the Jekyll build 
    is complete