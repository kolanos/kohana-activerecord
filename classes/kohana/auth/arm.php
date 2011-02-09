<?php defined('SYSPATH') or die('No direct access allowed.');

class Kohana_Auth_Arm extends Auth {	
	
	protected function _get_object($user)
	{
		if ( ! is_object($user))
		{
			$username = $user;

			$user = User::find(array(
				// using *?* marks as placeholders
				// ActiveRecord will escape string in the backend with database's native function to prevent SQL injection
				'conditions' => array(User::unique_key($username).' = ?', $username)
			));			
		}
		
		return $user;
	}
	
	public function logged_in($role = NULL)
	{
		$status = FALSE;

		$user = $this->get_user();

		if (is_object($user) AND $user instanceof User AND $user->is_loaded())
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
	

	protected function _login($user, $password, $remember)	
	{
		$user = $this->_get_object($user);

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
	
	public function password($user)
	{
		$user = $this->_get_object($user);

		return $user->password;
	}
	
	public function check_password($password)
	{
		$user = $this->get_user();

		if ( ! $user)
			return FALSE;			

		return ($this->hash($password) === $user->password);
	}	
	
	public function get_user($default = NULL)
	{
		$user = parent::get_user($default);

		if ( ! $user)
		{
			$user = $this->auto_login();
		}

		return $user;
	}
	
	public function auto_login()
	{
		if ($token = Cookie::get('authautologin'))
		{
			$token = UserToken::find_by_token($token);

			if ($token->is_loaded() AND is_object($token->user))
			{
				if ($token->user_agent === sha1(Request::$user_agent))
				{
					$token->save();

					Cookie::set('authautologin', $token->token, $token->expires - time());

					$this->complete_login($token->user->{'username'});

					return $token->user;
				}

				$token->delete();
			}
		}

		return FALSE;
	}
	
	protected function complete_login($user)
	{
		$user->complete_login();

		return parent::complete_login($user);
	}
	
	public function logout($destroy = FALSE, $logout_all = FALSE)
	{
		$this->_session->delete('auth_forced');

		if ($token = Cookie::get('authautologin'))
		{
			Cookie::delete('authautologin');

			$token = UserToken::find_by_token($token);

			if ($token->is_loaded() AND $logout_all)
			{
				UserToken::delete_all(array(
					'conditions' => array(
						'user_id' => $token->user_id					
					)
				));
			}
			elseif ($token->is_loaded())
			{
				$token->delete();
			}
		}

		return parent::logout($destroy);
	}	
	
}
