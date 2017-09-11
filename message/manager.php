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

class manager
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var string */
	protected $cannedmessages_table;

	/**
	 * Constructor
	 *
	 * @param    \phpbb\db\driver\driver_interface $db                 		DB driver interface
	 * @param    string                            $cannedmessages_table 	Canned Messages table
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, $cannedmessages_table)
	{
		$this->db = $db;
		$this->cannedmessages_table = $cannedmessages_table;
	}

	/**
	 * Gets messages based on the parent ID
	 *
	 * @param boolean $return_array		Indicates if array should be returned
	 * @param int	  $parent_id		Parent ID to filter by
	 * @param boolean $only_categories	Retrieve categories only
	 * @param int	  $selected_id		Optional selected message ID
	 * @return array|string  Array when $return_array is set to true, string when set to false
	 */
	public function get_messages($return_array = false, $parent_id = null, $only_categories = false, $selected_id = 0)
	{
		$sql_array = array(
			'SELECT' 	=> 'c.cannedmessage_id, c.parent_id, c.left_id, c.right_id, c.is_cat, c.cannedmessage_name, c.cannedmessage_content',
			'FROM'		=> array($this->cannedmessages_table => 'c'),
			'WHERE'		=> array(),
			'ORDER_BY'	=> 'c.left_id ASC'
		);

		if ($parent_id !== null)
		{
			$sql_array['WHERE'][] = 'parent_id = ' . (int)$parent_id;
		}

		if ($only_categories)
		{
			$sql_array['WHERE'][] = 'is_cat = 1';
		}

		$result = $this->db->sql_query($this->db->sql_build_query('SELECT', $sql_array), $parent_id === null ? 600 : 0);
		$rowset = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$rowset[(int) $row['cannedmessage_id']] = $row;
		}
		$this->db->sql_freeresult($result);

		$right = 0;
		$padding_store = array('0' => '');
		$padding = '';
		$cannedmessage_list = ($return_array) ? array() : '';

		foreach ($rowset as $row)
		{
			if ($row['left_id'] < $right)
			{
				$padding .= '&nbsp; &nbsp;';
				$padding_store[$row['parent_id']] = $padding;
			}
			else if ($row['left_id'] > $right + 1)
			{
				$padding = (isset($padding_store[$row['parent_id']])) ? $padding_store[$row['parent_id']] : '';
			}

			$right = $row['right_id'];
			$disabled = $row['is_cat'];
			$selected = $selected_id === (int)$row['cannedmessage_id'];

			if ($return_array)
			{
				// Include some more information...
				$cannedmessage_list[$row['cannedmessage_id']] = array_merge(array('padding' => $padding, 'disabled' => $disabled, 'selected' => $selected), $row);
			}
			else
			{
				$cannedmessage_list .= '<option value="' . $row['cannedmessage_id'] . '"' . (($disabled && !$only_categories) ? ' disabled="disabled" class="disabled-option"' : $selected ? ' selected="selected"' : '') . '>' . $padding . $row['cannedmessage_name'] . '</option>';
			}
		}
		unset($padding_store, $rowset);

		return $cannedmessage_list;
	}

	/**
	 * Gets a specific message
	 *
	 * @param $message_id	integer		The message ID to retrieve
	 * @return array
	 */
	public function get_message($message_id)
	{
		$sql = 'SELECT cannedmessage_id, parent_id, left_id, right_id, is_cat, cannedmessage_name, cannedmessage_content
				FROM ' . $this->cannedmessages_table . '
				WHERE cannedmessage_id = ' . (int)$message_id;

		$result = $this->db->sql_query_limit($sql, 1);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row;
	}

	/**
	 * Saves canned message data
	 *
	 * @param $cannedmessage_data array    Contains the data to save
	 * @return array Results data
	 */
	public function save_message($cannedmessage_data)
	{
		$return_result = [
			'success'	=> true,
			'errors'	=> array(),
		];

		if ($cannedmessage_data['is_cat'])
		{
			$cannedmessage_data['cannedmessage_content'] = '';
		}

		if ($cannedmessage_data['cannedmessage_id'] > 0)
		{
			// Update data check
			$cannedmessage_old = $this->get_message($cannedmessage_data['cannedmessage_id']);
			if ($cannedmessage_old['is_cat'] != $cannedmessage_data['is_cat']
				&& $cannedmessage_old['parent_id'] == $cannedmessage_data['parent_id'])
			{

			}

			$sql = 'UPDATE';
		}
		else
		{
			// Insert data check

			$sql = 'INSERT';
		}

		return $return_result;
	}
}