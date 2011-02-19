<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Arm Auth User Model.
 *
 * @package    Arm Auth
 * @author     Devi Mandiri <devi.mandiri@gmail.com>
 * @copyright  (c) 2011 Devi Mandiri
 * @license    MIT
 */
class User extends Arm {

	static $has_many = array(
		array('user_tokens'),
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
		array('password', 'within' => array(6,32))
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
		// Generate a random 22 character salt
		$this->salt = Text::random('alnum', 22);

		// Hashed password
		$this->password	= Auth::instance()->bonafide_hash($this->password, $this->salt, 10);
	}

	/**
	 * Get unique key based on value.
	 * 
	 * @param	mixed	Key value for match
	 * @return	string	Unique key name to attempt to match against
	 */
	public static function unique_key($value)
	{
		if (Valid::email($value))
		{
			return 'email';
		} 
		elseif (is_string($value))
		{
			return 'username';
		}
		return 'id';
	}

	/**
	 * Update password.
	 * 
	 * @param string	Current/Old password
	 * @param string	New password
	 * @param mixed 	Key value for match
	 * @param string 	New salt
	 * @return boolean
	 */
	public function update_password($old, $new, $user, $new_salt = NULL)
	{
		if ($old === NULL OR $new === NULL)
			return FALSE;

		$user = static::find_user($user);

		if ( ! $user)
		{
			return FALSE;
		}

		$auth = Auth::instance();

		if ( ! $auth->bonafide_check($old, $user->password, $user->salt))
			return FALSE;

		return $user->update_attribute('password', $auth->bonafide_hash($new, $new_salt));
	}

	/**
	 * Complete the login for a user by incrementing the logins and saving login timestamp.
	 *
	 * @return	void
	 */
	public function complete_login()
	{
		if (! $this->loaded())
		{
			return;
		}

		$this->update_attribute('logins', $this->logins + 1);
		$this->update_attribute('last_login', time());
	}

	/**
	 * Check if user has a particular role.
	 * 
	 * @param	mixed	Role to test for, can be Role object, string role name of integer role id
	 * @return	boolean	Whether or not the user has the requested role
	 */
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

	/**
	 * Helper function to delete user account.
	 * 
	 * @param	mixed	Key value for match
	 * @return	boolean
	 */
	public static function delete_user($user)
	{
		$user = static::find_user($user);

		if ( ! $user)
			return FALSE;

		// Are we going to delete our self ?
		$current_user = Auth::instance()->get_user();

		if (is_object($current_user) AND $current_user instanceof User AND $current_user->loaded())
		{
			if ($user->id === $current_user->id)
				return FALSE;
		}
			
		return $user->delete();
	}

	/**
	 * Convert a unique identifier string to a user object
	 * 
	 * @param	mixed	Key value for match
	 * @return	mixed	see ActiveRecord\Model::find()
	 */	
	public static function find_user($user)
	{
		if ( ! is_object($user))
		{
			$user = User::find(array(
				// using *?* marks as placeholders
				// ActiveRecord will escape string in the backend with database's native function to prevent SQL injection
				'conditions' => array(static::unique_key($user).' = ?', $user)
			));
		}

		return $user;
	}

	/**
	 * Helper function to create user account (with validation).
	 * 
	 * @param	string	username
	 * @param	string	plaintext password
	 * @param	string	email
	 * @param	string	role user
	 * @return	mixed	Model if success, Array if validation failed.
	 */
	public static function create_user($username, $password, $email, $role, $activate = TRUE)
	{
		$user = User::create(array(
			'username' => $username,
			'password' => $password,
			'email' => $email
		));

		if ($user AND $user->loaded())
		{
			$role = Role::find_by_name($role);
			if ($role)
				RolesUser::create(array('role_id' => $role->id,'user_id' => $user->id));

			if ($activate === TRUE)
			{
				$role = Role::find_by_name('login');
				if ($role)
					RolesUser::create(array('role_id' => $activate,'user_id' => $user->id));
			}
			return $user;
		}
		else
		{
			return $user->errors->full_messages();
		}
	}
	
	/*
	 * @var  string  Virtual field for password confirm
	 */
	public $password_confirm;
	
	/*
	 * Custom validation to match between password and password confirm.
	 * 
	 */
	public function validate()
	{
		//if ($this->attribute_is_dirty('password')) // don't know why it's not working in my box
		
		if ($this->is_dirty() AND array_key_exists('password', $this->dirty_attributes()))
		{
			if ($this->password !== $this->password_confirm)
			{
				$this->errors->add('password', "must be the same as Password Confirm.");
			}
		}
	}
	
}
