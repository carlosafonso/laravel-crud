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
	 * @return array
	 * @link https://laravel.com/docs/5.2/validation
	 */
	public function getValidationRules();
}
