<?php defined('SYSPATH') or die('No direct script access.');

class User extends Arm {
		
	static $has_many = array(
		array('roles_users'),
		array('roles', 'through' => 'roles_users')
	);
}
