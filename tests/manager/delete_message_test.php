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

class delete_message_test extends manager_base
{
	public function test_delete_message()
	{
		// Assert message ID 3 exists
		self::assertNotEmpty($this->manager->get_message(3));

		// Delete message 3
		$this->manager->delete_message(3);

		// Assert message ID 3 is deleted
		self::assertEmpty($this->manager->get_message(3));
	}

	public function data_delete_message_fails()
	{
		return array(
			array(0),
			array(10),
		);
	}

	/**
	 * @dataProvider data_delete_message_fails
	 */
	public function test_delete_message_fails($id)
	{
		$this->expectException(\OutOfBoundsException::class);
		$this->expectExceptionMessage('CANNEDMESSAGE_INVALID_ITEM');
		$this->manager->delete_message($id);
	}
}
