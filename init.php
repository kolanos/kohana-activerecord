<?php defined('SYSPATH') or die('No direct script access.');

include Kohana::find_file('vendor', 'activerecord/ActiveRecord');

/*$name = 'default';
	
$db_config = Kohana::config('database')->$name;
		
extract($db_config['connection'] + array(
	'database'   => '',
	'hostname'   => '',
	'username'   => '',
	'password'   => '',
	'persistent' => FALSE,
));
		
unset($db_config['connection']['username'], $db_config['connection']['password']);
		        
$dsn = $db_config['type'].'://'.$username.':'.$password.'@'.$hostname.'/'.$database;
       
ActiveRecord\Config::instance()
	->set_model_directory(APPPATH.'classes/model')
	->set_connections(array('development' => $dsn ));
*/	

ActiveRecord\Config::initialize(function($cfg)
{
	$db = Kohana::config('mesin');
	$cfg->set_model_directory($db->model);
	$cfg->set_connections(array('development' => $db->dsn ));	
});

