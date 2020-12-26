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

class move_message_test extends manager_base
{
	public function data_move_message()
	{
		return array(
			array(
				3,
				'move_up', // Move message 3 up
				array(
					array('cannedmessage_id' => 1, 'left_id' => 1),
					array('cannedmessage_id' => 3, 'left_id' => 2),
					array('cannedmessage_id' => 2, 'left_id' => 4),
					array('cannedmessage_id' => 4, 'left_id' => 7),
				),
				'Message 1',
			),
			array(
				2,
				'move_down', // Move message 2 down
				array(
					array('cannedmessage_id' => 1, 'left_id' => 1),
					array('cannedmessage_id' => 3, 'left_id' => 2),
					array('cannedmessage_id' => 2, 'left_id' => 4),
					array('cannedmessage_id' => 4, 'left_id' => 7),
				),
				'Message 2',
			),
			array(
				1,
				'move_up', // Move category 1 up (not expected to move)
				array(
					array('cannedmessage_id' => 1, 'left_id' => 1),
					array('cannedmessage_id' => 2, 'left_id' => 2),
					array('cannedmessage_id' => 3, 'left_id' => 4),
					array('cannedmessage_id' => 4, 'left_id' => 7),
				),
				false,
			),
			array(
				1,
				'move_down', // Move category 1 down
				array(
					array('cannedmessage_id' => 4, 'left_id' => 1),
					array('cannedmessage_id' => 1, 'left_id' => 3),
					array('cannedmessage_id' => 2, 'left_id' => 4),
					array('cannedmessage_id' => 3, 'left_id' => 6),
				),
				'Category 2',
			),
			array(
				4,
				'move_down', // Move category 4 down (not expected to move)
				array(
					array('cannedmessage_id' => 1, 'left_id' => 1),
					array('cannedmessage_id' => 2, 'left_id' => 2),
					array('cannedmessage_id' => 3, 'left_id' => 4),
					array('cannedmessage_id' => 4, 'left_id' => 7),
				),
				false,
			),
		);
	}

	/**
	 * @dataProvider data_move_message
	 */
	public function test_move_message($id, $direction, $expected, $affected_message)
	{
		$test = $this->manager->move_message($id, $direction);

		$result = $this->db->sql_query('SELECT cannedmessage_id, left_id
			FROM phpbb_cannedmessages
			ORDER BY left_id ASC');

		self::assertEquals($expected, $this->db->sql_fetchrowset($result));
		$this->db->sql_freeresult($result);

		self::assertEquals($affected_message, $test);
	}

	/**
	 * Test move_message() method fails
	 */
	public function test_move_message_fails()
	{
		self::assertFalse($this->manager->move_message(100, 'move_up'));
	}
}
