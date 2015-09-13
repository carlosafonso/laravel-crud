<?php
namespace Afonso\LvCrud\Tests;

use Illuminate\Http\Request;
use Illuminate\Routing\Router;

use Afonso\LvCrud\Context;

class ContextTest extends BaseTestCase
{
	public function testContextWith()
	{
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

	private function getRequest($params)
	{
		return Request::create('foo', 'GET', $params);
	}
}
