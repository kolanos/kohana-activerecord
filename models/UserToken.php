<?php defined('SYSPATH') or die('No direct access allowed.');

class UserToken extends Arm {

	static $belongs_to = array('user');
	
	static $before_save = array('create_token');
	
	public function create_token()
	{
		$this->token = sha1(uniqid(Text::random('alnum', 32), TRUE));
	}		
	
	public function __construct(array $attributes=array(), $guard_attributes=true, $instantiating_via_find=false, $new_record=true)
	{
		parent::__construct($attributes, $guard_attributes, $instantiating_via_find, $new_record);

		if (mt_rand(1, 100) === 1)
		{
			$this->delete_expired();
		}

		if ($this->expires < time() AND $this->is_loaded())
		{
			$this->delete();
		}
	}

	public function delete_expired()
	{
		static::delete_all(array('conditions' => array('expires < ?', time())));

		return $this;
	}
	


} 
