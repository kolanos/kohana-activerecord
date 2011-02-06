<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Arm extends Activerecord\Model {
	
	public static function factory($model)
	{		
		$model = ucfirst($model);

		return new $model();
	}
		
}
