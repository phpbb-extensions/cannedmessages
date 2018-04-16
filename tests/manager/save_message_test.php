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

class save_message_test extends manager_base
{
	public function get_data_template()
	{
		return array(
			'cannedmessage_id'		=> 0,
			'parent_id'				=> 0,
			'is_cat'				=> 0,
			'cannedmessage_name'	=> 'Test name',
			'cannedmessage_content'	=> 'Test content',
		);
	}

	public function data_insert_message()
	{
		return array(
			array($this->get_data_template()), // add new message
			array(array_merge($this->get_data_template(), array('is_cat' => 1))), // add new category
			array(array_merge($this->get_data_template(), array('parent_id' => 1))), // add new message to Category 1
			array(array_merge($this->get_data_template(), array('parent_id' => 1, 'is_cat' => 1))), // add new category to Category 1
		);
	}

	/**
	 * @dataProvider data_insert_message
	 */
	public function test_insert_message($data)
	{
		$this->assertTrue($this->manager->save_message($data));
	}

	public function data_insert_message_fails()
	{
		return array(
			array(array_merge($this->get_data_template(), array('parent_id' => 2)), 'CANNEDMESSAGE_PARENT_IS_NOT_CAT'), // parent id is not a category
			array(array_merge($this->get_data_template(), array('parent_id' => 100)), 'CANNEDMESSAGE_PARENT_IS_NOT_CAT'), // parent id is invalid
		);
	}

	/**
	 * @dataProvider data_insert_message_fails
	 */
	public function test_insert_message_fails($data, $expected)
	{
		$this->assertEquals($expected, $this->manager->save_message($data));
	}

	public function data_update_message()
	{
		return array(
			array(array_merge($this->get_data_template(), array('cannedmessage_id' => 2, 'cannedmessage_name' => 'Updated name'))), // update message title
			array(array_merge($this->get_data_template(), array('cannedmessage_id' => 2, 'cannedmessage_content' => 'Updated content'))), // update message content
			array(array_merge($this->get_data_template(), array('cannedmessage_id' => 2, 'parent_id' => 4))), // update message parent
			array(array_merge($this->get_data_template(), array('cannedmessage_id' => 2, 'is_cat' => 1))), // update message to category
		);
	}

	/**
	 * @dataProvider data_update_message
	 */
	public function test_update_message($data)
	{
		$this->assertTrue($this->manager->save_message($data));
	}

	public function data_update_message_fails()
	{
		return array(
			array(array_merge($this->get_data_template(), array('cannedmessage_id' => 100)), 'CANNEDMESSAGE_INVALID_ITEM'), // message does not exist
			array(array_merge($this->get_data_template(), array('cannedmessage_id' => 2, 'parent_id' => 100)), 'CANNEDMESSAGE_INVALID_PARENT'), // parent does not exist
			array(array_merge($this->get_data_template(), array('cannedmessage_id' => 1, 'is_cat' => 0)), 'CANNEDMESSAGE_HAS_CHILDREN'), // category has children
		);
	}

	/**
	 * @dataProvider data_update_message_fails
	 */
	public function test_update_message_fails($data, $expected)
	{
		$this->assertEquals($expected, $this->manager->save_message($data));
	}
}
