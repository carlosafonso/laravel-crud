<?php
namespace Afonso\LvCrud;

use Illuminate\Http\Request;

class Context
{
	protected $with;

	public function __construct(Request $request)
	{
		$this->setWith($request->get('with'));
	}

	public function with()
	{
		return $this->with;
	}

	protected function setWith($with)
	{
		if ($with) {
			$this->with = array_map(function($v) {
				return trim(camel_case($v));
			}, explode(',', $with));
		} else {
			$this->with = [];
		}
	}
}
