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

class get_parents_test extends manager_base
{
	public function data_get_parents()
	{
		return array(
			array(0, array()),
			array(1, array(1)),
			array(2, array(1, 2)),
			array(3, array(1, 3)),
			array(4, array(4)),
		);
	}

	/**
	 * @dataProvider data_get_parents
	 */
	public function test_get_parents($id, $expected)
	{
		$parents = $this->manager->get_parents($id);

		$this->assertCount(count($expected), $parents);

		foreach ($expected as $expected_id)
		{
			$this->assertEquals($expected_id, $parents[$expected_id]['cannedmessage_id']);
		}
	}
}
