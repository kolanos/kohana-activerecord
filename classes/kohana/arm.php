<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Arm extends Activerecord\Model {
	
	public static function factory($model, array $attributes=array(), $guard_attributes=true, $instantiating_via_find=false, $new_record=true)
	{		
		$model = ucfirst($model);

		return new $model($attributes, $guard_attributes, $instantiating_via_find, $new_record);
	}
		
}
