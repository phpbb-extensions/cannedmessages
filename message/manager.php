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
	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var \phpbb\cannedmessages\message\nestedset */
	protected $nestedset;

	/**
	 * Constructor
	 *
	 * @param \phpbb\cache\driver\driver_interface    $cache
	 * @param \phpbb\cannedmessages\message\nestedset $nestedset
	 */
	public function __construct(\phpbb\cache\driver\driver_interface $cache, \phpbb\cannedmessages\message\nestedset $nestedset)
	{
		$this->cache = $cache;
		$this->nestedset = $nestedset;
	}

	/**
	 * Gets messages (all messages, or messages within a given category)
	 * All messages will be cached to optimize posting pages
	 *
	 * @param int $parent_id Parent ID to filter by
	 * @return array  Array
	 */
	public function get_messages($parent_id = null)
	{
		if ($parent_id !== null)
		{
			$messages = $this->nestedset
				->set_sql_where('parent_id = ' . (int) $parent_id)
				->get_all_tree_data();
		}
		else if (($messages = $this->cache->get('_canned_messages')) === false)
		{
			$messages = $this->nestedset->get_all_tree_data();
			$this->cache->put('_canned_messages', $messages, 3600);
		}

		return $messages;
	}

	/**
	 * Gets a specific message
	 *
	 * @param int $id The message ID to retrieve
	 * @return mixed Array of data, or false if no data found
	 */
	public function get_message($id)
	{
		$message = $this->nestedset->get_subtree_data($id);

		return count($message) ? $message[$id] : false;
	}

	/**
	 * Get message categories
	 *
	 * @return array
	 */
	public function get_categories()
	{
		return $this->nestedset
			->set_sql_where('is_cat = 1')
			->get_all_tree_data();
	}

	/**
	 * Get all parents of a message or category
	 *
	 * @param int $id The message ID
	 * @return array A data array of the item and all its ancestors
	 */
	public function get_parents($id)
	{
		return $this->nestedset->get_path_data($id);
	}

	/**
	 * Does the canned message contain children?
	 *
	 * @param array $row A canned message data array
	 * @return bool True if children are present, false otherwise
	 */
	public function has_children($row)
	{
		return $row['right_id'] - $row['left_id'] > 1;
	}

	/**
	 * Is the item a category?
	 *
	 * @param int $id The item ID
	 * @return bool True if it is, false if not
	 */
	public function is_cat($id)
	{
		$message = $this->get_message($id);
		return $message && $message['is_cat'];
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

		// Handle emojis in canned message
		if (!empty($cannedmessage_data['cannedmessage_content']))
		{
			$cannedmessage_data['cannedmessage_content'] = utf8_encode_ucr($cannedmessage_data['cannedmessage_content']);
		}

		if ($error = $this->{$action}($cannedmessage_data))
		{
			return $error;
		}

		$this->cache->destroy('_canned_messages');

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
		if (!$cannedmessage_old)
		{
			return 'CANNEDMESSAGE_INVALID_ITEM';
		}

		if (!$cannedmessage_data['is_cat'] && $this->has_children($cannedmessage_old))
		{
			// Check to see if there are any children and fail out
			// Review this later to see if we can show a "new parent category" field instead of showing an error
			return 'CANNEDMESSAGE_HAS_CHILDREN';
		}

		// Update the parent/tree if needed
		if ((int) $cannedmessage_data['parent_id'] !== (int) $cannedmessage_old['parent_id'] &&
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
			if (!$this->is_cat($cannedmessage_data['parent_id']))
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

		$this->cache->destroy('_canned_messages');
	}

	/**
	 * Moves message up or down depending on what the user wanted
	 *
	 * @param $id         int    The canned message id to be moved
	 * @param $direction  string The direction to move the canned message
	 * @return bool|string False if there the message was not moved, or the name of the message moved over if
	 *                     successful.
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
			$this->cache->destroy('_canned_messages');
			$moved = $this->nestedset->affected_by_move($id, $delta);
			return $moved['cannedmessage_name'];
		}

		return false;
	}

	/**
	 * Update the parent id and re-sync the tree ids
	 *
	 * @param $id     int     The message ID
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
