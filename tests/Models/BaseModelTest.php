<?php
namespace Afonso\LvCrud\Tests;

class BaseModelTest extends BaseTestCase
{
	public function testModelShouldHaveNoValidationRulesByDefault()
	{
		$stub = $this->getMockForAbstractClass('Afonso\\LvCrud\\Models\\BaseModel');
		$this->assertEquals([], $stub->getValidationRules());
	}
}
