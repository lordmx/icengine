<?php

namespace Ice;

require_once dirname (__FILE__) . '/../../../../App/Class/Mock.php';

/**
 * Test class for Mock.
 * Generated by PHPUnit on 2011-12-22 at 12:27:56.
 */
class MockTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp ()
	{

	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown ()
	{

	}

	/**
	 * @todo Implement test__get().
	 */
	public function test__get ()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete (
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement test__set().
	 */
	public function test__set ()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete (
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement test__call().
	 */
	public function test__call ()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete (
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testRegisterField().
	 */
	public function testRegisterField ()
	{
		// Remove the following lines when you implement this test.
		$mock = new Mock ();

		$tests = array (
			'field1' => 'value1',
			'field2' => array ('value2')
		);

		foreach ($tests as $field => $expected)
		{
			$mock->registerField ($field, $expected);
			$actual = $mock->$field;

			$this->assertEquals ($expected, $actual);
		}
	}

	/**
	 * @todo Implement testRegisterMethodCallback().
	 */
	public function testRegisterMethodCallback ()
	{
		$mock = new Mock ();

		$mock->registerMethodCallback ('substr', 'substr');

		$expected = substr ('test', 0, 2);
		$actual = $mock->substr ('test', 0, 2);

		$this->assertEquals ($expected, $actual);

		$mock->registerMethodCallback ('toUpper', 'strtoupper');

		$expected = 'HELLO WORLD!';
		$actual = $mock->toUpper ('Hello World!');

		$this->assertEquals ($expected, $actual);
	}

	/**
	 * @todo Implement testRegisterMethodReturn().
	 */
	public function testRegisterMethodReturn ()
	{
		$mock = new Mock ();

		$mock
			->registerMethodReturn ('method1', 'return1')
			->registerMethodReturn ('method2', array('return2'));

		$this->assertEquals (
			'return1',
			$mock->method1 ()
		);

		$this->assertEquals (
			array('return2'),
			$mock->method2 ()
		);
	}

}