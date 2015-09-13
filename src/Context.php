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
		$this->with = $with ? explode(',', $with) : [];
	}
}
