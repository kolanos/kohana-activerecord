<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Arm extends Activerecord\Model {	
	
	public $_loaded;
	
	static $after_construct = array('we_are_loaded');
	
	public function we_are_loaded()
	{
		$this->_loaded = $this->values_for_pk();
	}
	
	public static function factory($model, array $attributes=array(), $guard_attributes=true, $instantiating_via_find=false, $new_record=true)
	{		
		$model = ucfirst($model);

		return new $model($attributes, $guard_attributes, $instantiating_via_find, $new_record);
	}
	
	public function is_loaded()
	{
		return array_filter($this->values_for_pk());
	}
		
}
