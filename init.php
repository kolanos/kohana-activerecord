<?php defined('SYSPATH') or die('No direct script access.');

define('PHP_ACTIVERECORD_AUTOLOAD_PREPEND', false);

include Kohana::find_file('vendor', 'activerecord/ActiveRecord');

ActiveRecord\Config::initialize(function($cfg)
{
	$db = Kohana::config('activerecord');
	$cfg->set_model_directory($db->model);
	$cfg->set_connections(array('development' => $db->dsn ));	
});

