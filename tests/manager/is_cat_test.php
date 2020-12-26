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

class is_cat_test extends manager_base
{
	public function data_is_cat()
	{
		return array(
			array(0, false),
			array(1, true),
			array(2, false),
			array(3, false),
			array(4, true),
			array(100, false),
		);
	}

	/**
	 * @dataProvider data_is_cat
	 */
	public function test_is_cat($id, $expected)
	{
		self::assertSame($expected, $this->manager->is_cat($id));
	}
}
