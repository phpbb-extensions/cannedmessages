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

class update_cannedmessages_schema extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_column_exists($this->table_prefix . 'cannedmessages', 'cannedmessage_parents');
	}

	public static function depends_on()
	{
		return array('\phpbb\cannedmessages\migrations\install_cannedmessages_schema');
	}

	public function update_schema()
	{
		return array(
			'add_columns'	=> array(
				$this->table_prefix . 'cannedmessages'	=> array(
					'cannedmessage_parents'	=> array('MTEXT_UNI', ''),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_columns'	=> array(
				$this->table_prefix . 'cannedmessages'	=> array(
					'cannedmessage_parents',
				),
			),
		);
	}
}
