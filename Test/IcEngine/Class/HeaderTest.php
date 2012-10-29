<?php

require_once dirname(__FILE__) . '/../../../Class/Header.php';

/**
 * Test class for Header.
 * Generated by PHPUnit on 2011-07-08 at 05:34:33.
 */
class HeaderTest extends PHPUnit_Framework_TestCase {

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown() {
		
	}

	/**
	 * @todo Implement testSetStatus().
	 */
	public function testSetStatus() {
		$this->assertNull (Header::setStatus (-1));
		
		Header::setStatus (Header::E403);
		
		$headers = headers_list ();
		
		print_r ($headers);
		
		$exists = false;
		
		foreach ($headers as $h)
		{
			if ($h == $hh [0])
			{
				$exists = true;
			}
		}
		
		$this->assertTrue ($exists);
	}

	/**
	 * @todo Implement testRedirect().
	 */
	public function testRedirect() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

}

?>
