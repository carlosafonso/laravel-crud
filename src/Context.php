<?php
namespace Afonso\LvCrud;

use Illuminate\Http\Request;

class Context
{
	/**
	 * The set of related entities to fetch
	 * alongside the main entity.
	 *
	 * @var	string[]
	 */
	protected $with;

	/**
	 * The number of items in a page, used
	 * when paginating results.
	 *
	 * @var	int
	 */
	protected $pageSize;

	public function __construct(Request $request)
	{
		$this->setWith($request->get('with'));
		$this->pageSize = $request->get('page_size', 15);
	}

	public function with()
	{
		return $this->with;
	}

	protected function setWith($with)
	{
		if ($with) {
			$this->with = array_map(function ($v) {
				return trim(camel_case($v));
			}, explode(',', $with));
		} else {
			$this->with = [];
		}
	}

	public function pageSize()
	{
		return $this->pageSize;
	}
}
