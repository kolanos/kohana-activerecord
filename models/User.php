<?php defined('SYSPATH') or die('No direct script access.');

class User extends Arm {
	
	static $has_many = array(
		array('roles_users'),
		array('roles', 'through' => 'roles_users')
	);	
	
	static $validates_presence_of = array(
		array('username'),
		array('email'),
		array('password')
	);
	
	static $validates_size_of = array(
		array('username', 'within' => array(4,32)),
		array('email', 'within' => array(4,127)),
		array('password', 'minimum' => 6)
	);
	
	static $validates_format_of = array(
		array('username', 'with' => '/^[-\pL\pN_.]++$/uD'),
		array('email', 'with' => '/^[-_a-z0-9\'+*$^&%=~!?{}]++(?:\.[-_a-z0-9\'+*$^&%=~!?{}]+)*+@(?:(?![-.])[-a-z0-9.]+(?<![-.])\.[a-z]{2,6}|\d{1,3}(?:\.\d{1,3}){3})(?::\d++)?$/iD')
	);
		
	static $validates_uniqueness_of = array(
		array('username'),
		array('email')
	);	
	
	static $after_validation = array('filters');
	
	public function filters()
	{
		$this->password	= Auth::instance()->hash($this->password);
	}	
	
	public static function unique_key($value)
	{
		return Valid::email($value) ? 'email' : 'username';
	}
	
	public function update_password($old, $new, $key)
	{
		if ($old === NULL OR $new === NULL)
			return FALSE;		
		
		$user = User::find(array(
			static::unique_key($key) => $key,
			'password' => Auth::instance()->hash($old)
		));
		
		return $user->update_attribute('password', Auth::instance()->hash($new));
	}
	
	public function unique_key_exists($value)
	{
		return User::exists(array(static::unique_key($value) => $value));
	}
	
	public function complete_login()
	{
		if ($this->is_loaded())
		{			
			$this->update_attribute('logins', $this->logins + 1); // TODO
			$this->update_attribute('last_login', time());
		}
	}
	
	public function has_role($role)
	{		
		if ($role instanceof Role)
		{
			$key = 'id';
			$val = $role->id;
		}
		elseif (is_string($role))
		{
			$key = 'name';
			$val = $role;
		}
		else
		{
			$key = 'id';
			$val = (int) $role;
		}
		
		foreach ($this->roles as $user_role)
		{
			if ($user_role->{$key} === $val)
			{
				return TRUE;
			}
		}
		
		return FALSE;
	}	
	
}
