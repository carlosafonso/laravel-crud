<?php
namespace Afonso\LvCrud\Models;

interface CrudModelInterface
{
	/**
	 * This virtual function will be implemented for setting the model 
	 * validation rules returning an array of them defined as 
	 * explained in the official Laravel documentation:
	 * https://laravel.com/docs/5.2/validation
	 */
	public function getValidationRules();
}
