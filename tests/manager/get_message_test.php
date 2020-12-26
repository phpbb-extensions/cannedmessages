<?php
/**
 *
 * Canned Messages. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\cannedmessages\tests\manager;

class get_message_test extends manager_base
{
	public function data_get_message()
	{
		return array(
			array(1, 'Category 1'),
			array(2, 'Message 1'),
			array(3, 'Message 2'),
		);
	}

	/**
	 * @dataProvider data_get_message
	 */
	public function test_get_message($id, $expected)
	{
		$message = $this->manager->get_message($id);

		self::assertEquals($id, $message['cannedmessage_id']);
		self::assertEquals($expected, $message['cannedmessage_name']);
	}

	public function data_get_message_fails()
	{
		return array(
			array(null),
			array(0),
			array(100),
		);
	}

	/**
	 * @dataProvider data_get_message_fails
	 */
	public function test_get_message_fails($id)
	{
		self::assertFalse($this->manager->get_message($id));
	}
}
