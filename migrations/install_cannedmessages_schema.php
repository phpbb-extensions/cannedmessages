<?php
/**
 *
 * Canned Messages. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\cannedmessages\migrations;

class install_cannedmessages_schema extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_table_exists($this->table_prefix . 'cannedmessages');
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v320\v320');
	}

	public function update_schema()
	{
		return array(
			'add_tables'		=> array(
				$this->table_prefix . 'cannedmessages'	=> array(
					'COLUMNS'		=> array(
						'cannedmessage_id'			=> array('UINT', null, 'auto_increment'),
						'parent_id'					=> array('UINT', 0),
						'left_id'					=> array('UINT', 0),
						'right_id'					=> array('UINT', 0),
						'is_cat'					=> array('TINT', 0),
						'cannedmessage_name'		=> array('STEXT_UNI', ''),
						'cannedmessage_content'		=> array('TEXT', ''),
					),
					'PRIMARY_KEY'	=> 'cannedmessage_id',
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_tables'		=> array(
				$this->table_prefix . 'cannedmessages',
			),
		);
	}
}
