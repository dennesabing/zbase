<?php

/**
 */
class HelpersControllerTest extends TestCase
{
	/**
	 * @return void
	 * @test
	 */
	public function testController()
	{
		$name = Zbase\Http\Controllers\__FRAMEWORK__\Page::class;
		$this->assertEquals(Zbase\Http\Controllers\Laravel\Page::class, zbase_controller_create_name($name));
	}

}
