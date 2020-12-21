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

class get_messages_test extends manager_base
{
	public function data_get_messages()
	{
		return array(
			array(null, array(1, 2, 3, 4)), // all messages
			array(0, array(1, 4)), // just top level categories
			array(1, array(2, 3)), // all messages inside Category 1
			array(100, array()), // nothing to find here
		);
	}

	/**
	 * @dataProvider data_get_messages
	 */
	public function test_get_messages($id, $expected)
	{
		$messages = $this->manager->get_messages($id);

		self::assertCount(count($expected), $messages);

		foreach ($expected as $expected_id)
		{
			self::assertEquals($expected_id, $messages[$expected_id]['cannedmessage_id']);
		}
	}

	public function test_messages_list()
	{
		self::assertEquals(array(
			1 => array(
				'cannedmessage_id'       => 1,
				'parent_id'              => 0,
				'left_id'                => 1,
				'right_id'               => 6,
				'is_cat'                 => 1,
				'cannedmessage_name'     => 'Category 1',
				'cannedmessage_content'  => '',
				'cannedmessage_parents'  => '',
			),
			2 => array(
				'cannedmessage_id'       => 2,
				'parent_id'              => 1,
				'left_id'                => 2,
				'right_id'               => 3,
				'is_cat'                 => 0,
				'cannedmessage_name'     => 'Message 1',
				'cannedmessage_content'  => 'Message 1 content',
				'cannedmessage_parents'  => '',
			),
			3 => array(
				'cannedmessage_id'       => 3,
				'parent_id'              => 1,
				'left_id'                => 4,
				'right_id'               => 5,
				'is_cat'                 => 0,
				'cannedmessage_name'     => 'Message 2',
				'cannedmessage_content'  => 'Message 2 content',
				'cannedmessage_parents'  => '',
			),
			4 => array(
				'cannedmessage_id'       => 4,
				'parent_id'              => 0,
				'left_id'                => 7,
				'right_id'               => 8,
				'is_cat'                 => 1,
				'cannedmessage_name'     => 'Category 2',
				'cannedmessage_content'  => '',
				'cannedmessage_parents'  => '',
			),
		), $this->manager->get_messages());
	}
}
