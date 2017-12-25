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
	 * @param int	  $parent_id		Parent ID to filter by
	 * @param boolean $only_categories	Retrieve categories only
	 * @param int	  $selected_id		Optional selected message ID
	 * @return array  Array
	 */
	public function get_messages($parent_id = null, $only_categories = false, $selected_id = 0)
	{
		$sql_array = array(
			'SELECT' 	=> 'c.cannedmessage_id, c.parent_id, c.left_id, c.right_id, c.is_cat, c.cannedmessage_name, c.cannedmessage_content',
			'FROM'		=> array($this->cannedmessages_table => 'c'),
			'WHERE'		=> array(),
			'ORDER_BY'	=> 'c.left_id ASC'
		);

		if ($parent_id !== null)
		{
			$sql_array['WHERE'][] = 'parent_id = ' . (int) $parent_id;
		}

		if ($only_categories)
		{
			$sql_array['WHERE'][] = 'is_cat = 1';
		}

		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql, 3600);
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

		if ($cannedmessage_data['cannedmessage_id'] > 0)
		{
			// Get the original canned message data
			$cannedmessage_old = $this->get_message($cannedmessage_data['cannedmessage_id']);

			if (!$cannedmessage_data['is_cat'] &&
				$cannedmessage_old['is_cat'] != $cannedmessage_data['is_cat'] &&
				count($this->get_messages($cannedmessage_data['cannedmessage_id'])))
			{
				// Check to see if there are any children and fail out
				// Review this later to see if we can show a "new parent category" field instead of showing an error
				return 'CANNEDMESSAGE_HAS_CHILDREN';
			}

			// Check to see if we need to move things around
			if ($cannedmessage_data['parent_id'] != $cannedmessage_old['parent_id'])
			{
				$sql = 'SELECT cm2.*
					FROM ' . $this->cannedmessages_table . ' cm1
					LEFT JOIN ' . $this->cannedmessages_table . " cm2 ON (cm2.left_id BETWEEN cm1.left_id AND cm1.right_id)
					WHERE cm1.cannedmessage_id = {$cannedmessage_old['cannedmessage_id']}
					ORDER BY cm2.left_id ASC";
				$result = $this->db->sql_query($sql);

				$moved_cannedmessages = array();
				while ($row = $this->db->sql_fetchrow($result))
				{
					$moved_cannedmessages[] = $row;
				}
				$this->db->sql_freeresult($result);

				$from_data = $moved_cannedmessages[0];
				$diff = count($moved_cannedmessages) * 2;

				$moved_ids = array();
				foreach ($moved_cannedmessages as $moved_cannedmessage)
				{
					$moved_ids[] = $moved_cannedmessage['cannedmessage_id'];
				}

				$this->resync_tree($from_data, $diff);

				if ($cannedmessage_data['parent_id'] > 0)
				{
					// Retrieve $to_data again, it may have been changed...
					$to_data = $this->get_message($cannedmessage_data['parent_id']);

					// Re-sync new parents
					$sql = 'UPDATE ' . $this->cannedmessages_table . "
						SET right_id = right_id + $diff
						WHERE " . $to_data['right_id'] . ' BETWEEN left_id AND right_id
						AND ' . $this->db->sql_in_set('cannedmessage_id', $moved_ids, true);
					$this->db->sql_query($sql);

					// Re-sync the right-hand side of the tree
					$sql = 'UPDATE ' . $this->cannedmessages_table . "
						SET left_id = left_id + $diff, right_id = right_id + $diff
						WHERE left_id > " . $to_data['right_id'] . '
						AND ' . $this->db->sql_in_set('cannedmessage_id', $moved_ids, true);
					$this->db->sql_query($sql);

					// Re-sync moved branch
					$to_data['right_id'] += $diff;

					if ($to_data['right_id'] > $from_data['right_id'])
					{
						$diff = '+ ' . ($to_data['right_id'] - $from_data['right_id'] - 1);
					}
					else
					{
						$diff = '- ' . abs($to_data['right_id'] - $from_data['right_id'] - 1);
					}
				}
				else
				{
					$sql = 'SELECT MAX(right_id) AS right_id
						FROM ' . $this->cannedmessages_table . '
						WHERE ' . $this->db->sql_in_set('cannedmessage_id', $moved_ids, true);
					$result = $this->db->sql_query($sql);
					$max_right_id = $this->db->sql_fetchfield('right_id');
					$this->db->sql_freeresult($result);

					$diff = '+ ' . ($max_right_id - $from_data['left_id'] + 1);
				}

				$sql = 'UPDATE ' . $this->cannedmessages_table . "
					SET left_id = left_id $diff, right_id = right_id $diff
					WHERE " . $this->db->sql_in_set('cannedmessage_id', $moved_ids);
				$this->db->sql_query($sql);
			}

			$sql = 'UPDATE ' . $this->cannedmessages_table . '
				SET ' . $this->db->sql_build_array('UPDATE', $cannedmessage_data) . '
				WHERE cannedmessage_id = ' . $cannedmessage_data['cannedmessage_id'];
			$this->db->sql_query($sql);
		}
		else
		{
			if ($cannedmessage_data['parent_id'])
			{
				// Get the selected parent's information
				$row = $this->get_message($cannedmessage_data['parent_id']);

				if (!$row)
				{
					return 'CANNEDMESSAGE_PARENT_NOT_EXIST';
				}

				if (!$row['is_cat'])
				{
					return 'CANNEDMESSAGE_PARENT_IS_NOT_CAT';
				}

				// Update left and right IDs to make space
				$sql = 'UPDATE ' . $this->cannedmessages_table . '
					SET left_id = left_id + 2, right_id = right_id + 2
					WHERE left_id > ' . $row['right_id'];
				$this->db->sql_query($sql);

				$sql = 'UPDATE ' . $this->cannedmessages_table . '
					SET right_id = right_id + 2
					WHERE ' . $row['left_id'] . ' BETWEEN left_id AND right_id';
				$this->db->sql_query($sql);

				$cannedmessage_data['left_id'] = $row['right_id'];
				$cannedmessage_data['right_id'] = $row['right_id'] + 1;
			}
			else
			{
				// No parent so let's get the next maximum ID
				$sql = 'SELECT MAX(right_id) AS right_id
					FROM ' . $this->cannedmessages_table;
				$result = $this->db->sql_query($sql);
				$max_right_id = $this->db->sql_fetchfield('right_id');
				$this->db->sql_freeresult($result);

				$cannedmessage_data['left_id'] = $max_right_id + 1;
				$cannedmessage_data['right_id'] = $max_right_id + 2;
			}

			$sql = 'INSERT INTO ' . $this->cannedmessages_table . ' ' . $this->db->sql_build_array('INSERT', $cannedmessage_data);
			$this->db->sql_query($sql);
		}
		$this->cache->destroy('sql', $this->cannedmessages_table);

		return true;
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
	 * @return bool True if the message was moved or False if the message was not moved
	 */
	public function move_message($id, $direction)
	{
		if ($direction === 'move_up')
		{
			$delta = 1;
		}
		else if ($direction === 'move_down')
		{
			$delta = -1;
		}
		else
		{
			$delta = 0;
		}

		if ($this->nestedset->move($id, $delta))
		{
			$this->cache->destroy('sql', $this->cannedmessages_table);
			return true;
		}

		return false;
	}

	/**
	 * Re-syncs left/right ID tree
	 *
	 * @param $cannedmessage array The canned message data to use
	 * @param $diff int	The difference to take from the right and left IDs
	 */
	protected function resync_tree($cannedmessage, $diff)
	{
		$sql = 'UPDATE ' . $this->cannedmessages_table . "
			SET right_id = right_id - $diff
			WHERE left_id < {$cannedmessage['right_id']} AND right_id > {$cannedmessage['right_id']}";
		$this->db->sql_query($sql);

		$sql = 'UPDATE ' . $this->cannedmessages_table . "
			SET left_id = left_id - $diff, right_id = right_id - $diff
			WHERE left_id > {$cannedmessage['right_id']}";
		$this->db->sql_query($sql);
	}
}
