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

	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var \phpbb\cannedmessages\message\nestedset */
	protected $nestedset;

	/**
	 * Constructor
	 *
	 * @param \phpbb\db\driver\driver_interface       $db
	 * @param \phpbb\cache\driver\driver_interface    $cache
	 * @param \phpbb\cannedmessages\message\nestedset $nestedset
	 * @param   string                                $cannedmessages_table
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\cache\driver\driver_interface $cache, \phpbb\cannedmessages\message\nestedset $nestedset, $cannedmessages_table)
	{
		$this->db = $db;
		$this->cache = $cache;
		$this->nestedset = $nestedset;
		$this->cannedmessages_table = $cannedmessages_table;
	}

	/**
	 * Gets messages based on the parent ID
	 *
	 * @param int     $parent_id       Parent ID to filter by
	 * @param boolean $only_categories Retrieve categories only
	 * @param int     $selected_id     Optional selected message ID
	 * @param int     $cache           Time to cache the SQl result for
	 * @return array  Array
	 */
	public function get_messages($parent_id = null, $only_categories = false, $selected_id = 0, $cache = 3600)
	{
		$sql_array = array(
			'SELECT' 	=> 'c.cannedmessage_id, c.parent_id, c.left_id, c.right_id, c.is_cat, c.cannedmessage_name, c.cannedmessage_content',
			'FROM'		=> array($this->cannedmessages_table => 'c'),
			'WHERE'		=> array(),
			'ORDER_BY'	=> 'c.left_id ASC'
		);

		if ($parent_id !== null)
		{
			$sql_array['WHERE'][] = 'c.parent_id = ' . (int) $parent_id;
		}

		if ($only_categories)
		{
			$sql_array['WHERE'][] = 'c.is_cat = 1';
		}

		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql, $cache);
		$rowset = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$rowset[(int) $row['cannedmessage_id']] = $row;
		}
		$this->db->sql_freeresult($result);

		$right = 0;
		$padding_store = array('0' => '');
		$padding = '';
		$cannedmessage_list = array();

		foreach ($rowset as $row)
		{
			if ($row['left_id'] < $right)
			{
				$padding .= '&nbsp; &nbsp;';
				$padding_store[$row['parent_id']] = $padding;
			}
			else if ($row['left_id'] > $right + 1)
			{
				$padding = isset($padding_store[$row['parent_id']]) ? $padding_store[$row['parent_id']] : '';
			}

			$right = $row['right_id'];
			$disabled = $row['is_cat'] && $only_categories ? false : $row['is_cat'];
			$selected = (int) $selected_id === (int) $row['cannedmessage_id'];

			$cannedmessage_list[$row['cannedmessage_id']] = array_merge(array('padding' => $padding, 'disabled' => $disabled, 'selected' => $selected), $row);
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
			WHERE cannedmessage_id = ' . (int) $message_id;

		$result = $this->db->sql_query_limit($sql, 1, 0, 3600);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row;
	}

	/**
	 * Saves canned message data
	 *
	 * @param $cannedmessage_data array    Contains the data to save
	 * @return boolean|string Save result or key of error message
	 */
	public function save_message($cannedmessage_data)
	{
		// Categories don't have message content
		if ($cannedmessage_data['is_cat'])
		{
			$cannedmessage_data['cannedmessage_content'] = '';
		}

		$action = ($cannedmessage_data['cannedmessage_id'] > 0) ? 'update' : 'insert';

		if ($error = $this->{$action}($cannedmessage_data))
		{
			return $error;
		}

		$this->cache->destroy('sql', $this->cannedmessages_table);

		return true;
	}

	/**
	 * Update an existing canned message
	 *
	 * @param $cannedmessage_data array Contains the data to save
	 * @return bool|string Key of error message or false if no error occurred
	 */
	protected function update($cannedmessage_data)
	{
		// Get the original canned message data
		$cannedmessage_old = $this->get_message($cannedmessage_data['cannedmessage_id']);

		if (!$cannedmessage_data['is_cat'] &&
			$cannedmessage_old['is_cat'] != $cannedmessage_data['is_cat'] &&
			count($this->nestedset->get_subtree_data($cannedmessage_data['cannedmessage_id'], false, false)))
		{
			// Check to see if there are any children and fail out
			// Review this later to see if we can show a "new parent category" field instead of showing an error
			return 'CANNEDMESSAGE_HAS_CHILDREN';
		}

		// Update the parent/tree if needed
		if ($cannedmessage_data['parent_id'] != $cannedmessage_old['parent_id'] &&
			($error = $this->change_parent($cannedmessage_data['cannedmessage_id'], $cannedmessage_data['parent_id'])))
		{
			return $error;
		}

		$this->nestedset->update_item($cannedmessage_data['cannedmessage_id'], $cannedmessage_data);

		return false;
	}

	/**
	 * Insert a new canned message
	 *
	 * @param $cannedmessage_data array Contains the data to save
	 * @return bool|string Key of error message or false if no error occurred
	 */
	protected function insert($cannedmessage_data)
	{
		$cannedmessage_new = $this->nestedset->insert($cannedmessage_data);

		if ($cannedmessage_data['parent_id'])
		{
			// Check if the selected parent is a category
			$row = $this->get_message($cannedmessage_data['parent_id']);
			if (!$row['is_cat'])
			{
				$this->delete_message($cannedmessage_new['cannedmessage_id']);
				return 'CANNEDMESSAGE_PARENT_IS_NOT_CAT';
			}

			// Update parent/tree ids
			return $this->change_parent($cannedmessage_new['cannedmessage_id'], $cannedmessage_data['parent_id']);
		}

		return false;
	}

	/**
	 * Deletes a canned message
	 *
	 * @param $id int The canned message id to delete
	 */
	public function delete_message($id)
	{
		$this->nestedset->delete($id);

		$this->cache->destroy('sql', $this->cannedmessages_table);
	}

	/**
	 * Moves message up or down depending on what the user wanted
	 *
	 * @param $id         int    The canned message id to be moved
	 * @param $direction  string The direction to move the canned message
	 * @return bool|string False if there the message was not moved, or the name of the message moved over if successful.
	 */
	public function move_message($id, $direction)
	{
		$delta = 0;

		if ($direction === 'move_up')
		{
			$delta = 1;
		}
		else if ($direction === 'move_down')
		{
			$delta = -1;
		}

		try
		{
			$result = $this->nestedset->move($id, $delta);
		}
		catch (\OutOfBoundsException $e)
		{
			return false;
		}

		if ($result)
		{
			$this->cache->destroy('sql', $this->cannedmessages_table);
			return 	$this->get_leap_over_name($id, $delta);
		}

		return false;
	}

	/**
	 * Get the name of the canned message that was leaped over by a moved message.
	 * This is needed to fulfill the admin log, e.g, "Moved canned message X above Y".
	 * The moved canned message is "X" and we need to get the name of "Y" for logging purposes.
	 *
	 * @param $id    int The ID of the canned message that was moved
	 * @param $delta int The direction it moved (1 = up, -1 = down)
	 * @return mixed The name of the canned message that was leaped over, or false if something went wrong.
	 */
	protected function get_leap_over_name($id, $delta)
	{
		$sql = 'SELECT cannedmessage_name
				FROM ' . $this->cannedmessages_table . '
				WHERE ' . ($delta === 1 ? 'left_id' : 'right_id') . ' = (SELECT ' . ($delta === 1 ? 'right_id' : 'left_id') . ' 
					FROM ' . $this->cannedmessages_table . ' 
					WHERE cannedmessage_id = ' . (int) $id . ') + ' . $delta;
		$result = $this->db->sql_query_limit($sql, 1);
		$name = $this->db->sql_fetchfield('cannedmessage_name');
		$this->db->sql_freeresult($result);

		return $name;
	}

	/**
	 * Update the parent id and re-sync the tree ids
	 *
	 * @param $id int     The message ID
	 * @param $parent int The message's parent ID
	 * @return bool|string Key of error message or false if no error occurred
	 */
	protected function change_parent($id, $parent)
	{
		try
		{
			$this->nestedset->change_parent($id, $parent);
		}
		catch (\OutOfBoundsException $e)
		{
			return $e->getMessage();
		}

		return false;
	}
}
