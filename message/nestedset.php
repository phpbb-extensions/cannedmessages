<?php
/**
 *
 * Canned Messages. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\cannedmessages\message;

class nestedset extends \phpbb\tree\nestedset
{
	/**
	 * Construct
	 *
	 * @param \phpbb\db\driver\driver_interface $db         Database connection
	 * @param \phpbb\lock\db                    $lock       Lock class used to lock the table when moving forums around
	 * @param string                            $table_name Table name
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\lock\db $lock, $table_name)
	{
		parent::__construct(
			$db,
			$lock,
			$table_name,
			'CANNEDMESSAGE_',
			'',
			array(),
			array(
				'item_id'		=> 'cannedmessage_id',
				'parent_id'		=> 'parent_id',
				'left_id'		=> 'left_id',
				'right_id'		=> 'right_id',
				'item_parents'	=> 'cannedmessage_parents',
			)
		);
	}

	/**
	 * Set additional sql where restrictions
	 *
	 * @param string $where An SQL where condition
	 * @return nestedset $this object for chaining calls
	 */
	public function set_sql_where($where)
	{
		$this->sql_where = '%s' . $where;

		return $this;
	}

	/**
	 * Update a nested item
	 *
	 * @param int   $item_id   The item identifier
	 * @param array $item_data SQL array of data to update
	 * @return mixed Number of the affected rows updated, or false
	 */
	public function update_item($item_id, array $item_data)
	{
		$sql = 'UPDATE ' . $this->table_name . '
			SET ' . $this->db->sql_build_array('UPDATE', $item_data) . '
			WHERE ' . $this->column_item_id . ' = ' . (int) $item_id;
		$this->db->sql_query($sql);

		return $this->db->sql_affectedrows();
	}

	/**
	 * Get the canned message that was affected by a moved message.
	 *
	 * @param $id    int The ID of the canned message that was moved
	 * @param $delta int The direction it moved (1 = up, -1 = down)
	 * @return mixed The name of the canned message that was leaped over, or false if something went wrong.
	 */
	public function affected_by_move($id, $delta)
	{
		$where = ($delta === 1 ? 'left_id' : 'right_id') . ' = (SELECT ' . ($delta === 1 ? 'right_id' : 'left_id') . ' 
			FROM ' . $this->table_name . ' 
			WHERE cannedmessage_id = ' . (int) $id . ') + ' . $delta;

		return current($this->set_sql_where($where)->get_all_tree_data());
	}
}
