<?php defined('SYSPATH') or die('No direct script access.');
/**
 * [PHP-ActiveRecord][ref-arm] integration for Kohana Framework.
 *
 * [ref-arm]: http://phpactiverecord.org
 * 
 * @package    Arm
 * @author     Devi Mandiri <devi.mandiri@gmail.com>
 * @copyright  (c) 2011 Devi Mandiri
 * @license    MIT
 */
class Kohana_Arm extends Activerecord\Model {	

	/**
	 * Constructs a model.	 
	 *
	 * @param string $model	Model name
	 * @param array $attributes Hash containing names and values to mass assign to the model
	 * @param boolean $guard_attributes Set to true to guard protected/non-accessible attributes
	 * @param boolean $instantiating_via_find Set to true if this model is being created from a find call
	 * @param boolean $new_record Set to true if this should be considered a new record
	 * @return Model
	 */
	public static function factory($model, array $attributes=array(), $guard_attributes=true, $instantiating_via_find=false, $new_record=true)
	{		
		$model = ucfirst($model);

		return new $model($attributes, $guard_attributes, $instantiating_via_find, $new_record);
	}
	
	/**
	 * Helper to check if a model has been loaded and it has values for the primary key(s).
	 * 
	 * @return  boolean
	 */
	public function loaded()
	{
		return array_filter($this->values_for_pk());
	}		
}
