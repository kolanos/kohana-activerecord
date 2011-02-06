<?php defined('SYSPATH') or die('No direct script access.');

class Role extends Arm {

	static $has_many = array(
		array('roles_users'),
		array('users', 'through' => 'roles_users')
	);
	
}
