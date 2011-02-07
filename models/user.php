<?php defined('SYSPATH') or die('No direct script access.');

class User extends Arm {
	
	// virtual attribute
	static $password_confirm;
		
	// relationships
	static $has_many = array(
		array('roles_users'),
		array('roles', 'through' => 'roles_users')
	);	
	
	// validations	
	static $validates_presence_of = array(
		array('username'),
		array('email'),
		array('password'),		
	);
	
	static $validates_size_of = array(
		array('username', 'within' => array(4,32)),
		array('email', 'within' => array(4,127))
	);
	
	static $validates_format_of = array(
		array('username', 'with' => '/^[-\pL\pN_.]++$/uD'),
		array('email', 'with' => '/^[-_a-z0-9\'+*$^&%=~!?{}]++(?:\.[-_a-z0-9\'+*$^&%=~!?{}]+)*+@(?:(?![-.])[-a-z0-9.]+(?<![-.])\.[a-z]{2,6}|\d{1,3}(?:\.\d{1,3}){3})(?::\d++)?$/iD')		
	);
		
	static $validates_uniqueness_of = array(
		array('username'),
		array('email')
	);
	
	// set value for virtual attribute
	public function set_confirm_password($str)
	{
		self::$password_confirm = $str;
	}
	
	// custom validation check
	public function validate()
	{
		if ($this->password !== self::$password_confirm)
		{
			$this->errors->add('password', "must be the same as Confirm Password");
		}
	}
	
}
