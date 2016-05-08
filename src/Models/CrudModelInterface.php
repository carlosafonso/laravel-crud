<?php
namespace Afonso\LvCrud\Models;

interface CrudModelInterface
{
	/**
	 * Returns the validation rules that apply
	 * to the model represented by the implementing
	 * class.
	 *
	 * The returned value is an array of rules as
	 * explained in the official Laravel documentation.
	 *
	 * @param integer $entityId The id of the instance of the model being
	 * processed.
	 * @return array
	 * @link https://laravel.com/docs/5.2/validation
	 */
	public function getValidationRules($entityId = null);
}
