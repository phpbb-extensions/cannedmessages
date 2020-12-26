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

class get_categories_test extends manager_base
{
	public function data_get_categories()
	{
		return array(
			array(array(1, 4)),
		);
	}

	/**
	 * @dataProvider data_get_categories
	 */
	public function test_get_categories($expected)
	{
		$categories = $this->manager->get_categories();

		self::assertCount(count($expected), $categories);

		foreach ($expected as $expected_id)
		{
			self::assertEquals($expected_id, $categories[$expected_id]['cannedmessage_id']);
		}
	}
}
