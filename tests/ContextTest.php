<?php
namespace Afonso\LvCrud\Tests;

use Afonso\LvCrud\Context;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Config;

class ContextTest extends BaseTestCase
{
	public function testWith()
	{
		Config::shouldReceive('get');

		$req = $this->getRequest(['with' => 'foo']);
		$ctx = new Context($req);

		$this->assertEquals(['foo'], $ctx->with());

		$req = $this->getRequest(['with' => 'foo,bar']);
		$ctx = new Context($req);

		$this->assertEquals(['foo', 'bar'], $ctx->with());

		$req = $this->getRequest(['with' => '  foo , bar']);
		$ctx = new Context($req);

		$this->assertEquals(['foo', 'bar'], $ctx->with());

		$req = $this->getRequest([]);
		$ctx = new Context($req);

		$this->assertEquals([], $ctx->with());
	}

	public function testPageSizeIsReadFromUrlParams()
	{
		$req = $this->getRequest(['page_size' => 50]);
		$ctx = new Context($req);

		$this->assertEquals(50, $ctx->pageSize());
	}

	private function getRequest($params)
	{
		return Request::create('foo', 'GET', $params);
	}
}
