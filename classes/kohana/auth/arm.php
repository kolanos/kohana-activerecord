<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Arm Auth driver.
 * 
 * @package    Arm Auth
 * @author     Devi Mandiri <devi.mandiri@gmail.com>
 * @copyright  (c) 2011 Devi Mandiri
 * @license    MIT
 */
class Kohana_Auth_Arm extends Auth {
	
	/**
	 * Checks if a session is active.
	 *
	 * @param   mixed	role name string, role Arm object, or array with role names
	 * @return  boolean
	 */	
	public function logged_in($role = NULL)
	{
		$status = FALSE;

		$user = $this->get_user();

		if (is_object($user) AND $user instanceof User AND $user->loaded())
		{
			$status = TRUE;

			if ( ! empty($role))
			{

				if (is_array($role))
				{
					foreach ($role as $role_iteration)
					{
						if( ! $user->has_role($role_iteration))
						{
							$status = FALSE;
							break;
						}
					}
				}
				else
				{
					$status = $user->has_role($role);
				}
			}
		}

		return $status;
	}
	
	/**
	 * Logs a user in.
	 *
	 * @param   string   username
	 * @param   string   password
	 * @param   boolean  enable auto-login
	 * @return  boolean
	 */
	protected function _login($user, $password, $remember)	
	{
		$user = $this->_get_object($user);
		
		if (! $user)
		{
			return FALSE;
		}

		if ($user->has_role('login') AND $user->password === $password)
		{
			if ($remember === TRUE)
			{
				// Token data
				$data = array(
					'user_id'    => $user->id,
					'expires'    => time() + $this->_config['lifetime'],
					'user_agent' => sha1(Request::$user_agent),
				);

				$token = UserToken::create($data);

				Cookie::set('authautologin', $token->token, $this->_config['lifetime']);
			}

			$this->complete_login($user);

			return TRUE;
		}

		return FALSE;		
	}
	
	/**
	 * Forces a user to be logged in, without specifying a password.
	 *
	 * @param   mixed    username string, or user Arm object
	 * @param   boolean  mark the session as forced
	 * @return  boolean
	 */
	public function force_login($user)
	{
		$user = $this->_get_object($user);
		
		if (! $user)
		{
			return FALSE;
		}
		
		$this->_session->set('auth_forced', TRUE);
		
		$this->complete_login($user);
	}

	/**
	 * Logs a user in, based on the authautologin cookie.
	 *
	 * @return  mixed
	 */
	public function auto_login()
	{
		if ($token = Cookie::get('authautologin'))
		{
			$token = UserToken::find_by_token($token);
			
			if ($token AND $token->user)
			{
				if ($token->user_agent === sha1(Request::$user_agent))
				{
					$token->save();

					Cookie::set('authautologin', $token->token, $token->expires - time());

					$this->complete_login($token->user);

					return $token->user;
				}

				$token->delete();
			}
		}

		return FALSE;
	}
	
	/**
	 * Gets the currently logged in user from the session (with auto_login check).
	 * Returns FALSE if no user is currently logged in.
	 *
	 * @return  mixed
	 */	
	public function get_user($default = NULL)
	{
		$user = parent::get_user($default);

		if ( ! $user)
		{
			$user = $this->auto_login();
		}

		return $user;
	}

	/**
	 * Log a user out and remove any auto-login cookies.
	 *
	 * @param   boolean  completely destroy the session
	 * @param	boolean  remove all tokens for user
	 * @return  boolean
	 */	
	public function logout($destroy = FALSE, $logout_all = FALSE)
	{
		$this->_session->delete('auth_forced');

		if ($token = Cookie::get('authautologin'))
		{
			Cookie::delete('authautologin');

			$token = UserToken::find_by_token($token);

			if ($token AND $logout_all)
			{
				UserToken::delete_all(array(
					'conditions' => array(
						'user_id' => $token->user_id					
					)
				));
			}
			elseif ($token)
			{
				$token->delete();
			}
		}

		return parent::logout($destroy);
	}

	/**
	 * Get the stored password for a username.
	 *
	 * @param   mixed  username
	 * @return  string
	 */
	public function password($user)
	{
		$user = $this->_get_object($user);
		
		if (! $user)
		{
			return;
		}
		
		return $user->password;
	}

	/**
	 * Complete the login for a user by incrementing the logins and setting
	 * session data: user_id, username, roles
	 *
	 * @param   object   user model object
	 * @return  void
	 */	
	protected function complete_login($user)
	{
		$user->complete_login();

		return parent::complete_login($user);
	}

	
	/**
	 * Convert a unique identifier string to a user object
	 * 
	 * @param mixed $user
	 * @return mixed ActiveRecord\Model::find()
	 */	
	protected function _get_object($user)
	{
		if ( ! is_object($user))
		{
			$user = User::find(array(
				// using *?* marks as placeholders
				// ActiveRecord will escape string in the backend with database's native function to prevent SQL injection
				'conditions' => array(User::unique_key($user).' = ?', $user)
			));
		}
		
		return $user;
	}

	/**
	 * Compare password with original (hashed). Works for current (logged in) user
	 *
	 * @param   string  $password
	 * @return  boolean
	 */
	public function check_password($password)
	{
		$user = $this->get_user();

		if ( ! $user)
			return FALSE;		

		return ($this->hash($password) === $user->password);
	}

}
