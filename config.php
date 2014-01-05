<?php
$conf_str = <<<'EOD'
{
  time_limit: 0,
  jekyll_path: "/usr/bin/jekyll",
  git_path: "git",
  projects_root: "/opt/scratch",
  servers: {
    'https://github.com/user/repo':{
      id: repo,
      jekyll_params: 'build -d /var/www/vhosts/repo'
    }
  }
}
EOD;
$global_config = json_decode($conf_str);
?>