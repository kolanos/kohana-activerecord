<?php defined('SYSPATH') or die('No direct script access.');

class RolesUser extends Arm {
	
	static $table_name = 'roles_users';	
	
	static $belongs_to = array(
		array('user'),
		array('role')
	);
}
