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

class has_children_test extends manager_base
{
	public function data_has_children()
	{
		return array(
			array(1, true),
			array(2, false),
			array(3, false),
			array(4, false),
		);
	}

	/**
	 * @dataProvider data_has_children
	 */
	public function test_has_children($id, $expected)
	{
		$message = $this->manager->get_message($id);

		self::assertSame($expected, $this->manager->has_children($message));
	}
}
