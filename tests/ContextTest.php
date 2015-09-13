<?php
namespace Afonso\LvCrud\Tests;

use Illuminate\Http\Request;
use Illuminate\Routing\Router;

use Afonso\LvCrud\Context;

class ContextTest extends BaseTestCase
{
	public function testContextWith()
	{
		$req = Request::create('foo', 'GET', ['with' => 'foo']);
		$ctx = new Context($req);

		$this->assertEquals(['foo'], $ctx->with());

		$req = Request::create('foo', 'GET', ['with' => 'foo,bar']);
		$ctx = new Context($req);

		$this->assertEquals(['foo', 'bar'], $ctx->with());

		$req = Request::create('foo', 'GET', ['with' => '  foo , bar']);
		$ctx = new Context($req);

		$this->assertEquals(['foo', 'bar'], $ctx->with());

		$req = Request::create('foo', 'GET', []);
		$ctx = new Context($req);

		$this->assertEquals([], $ctx->with());
	}
}
