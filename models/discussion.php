<?php
class Discussion extends DiscussionsAppModel
{
	var $name = 'Discussion';
	var $actsAs = array('Tree');

	var $validate = array(
		'table_id' => array(
			'table_id-1' => array(
				'rule' => 'notEmpty',
				'message' => 'Discussion owner id must not be empty.',
			),
			'table_id-2' => array(
				'rule' => 'numeric',
				'message' => 'Discussion owner id must be a number.',
			),
			'table_id-3' => array(
				'rule' => array('maxLength', 10),
				'message' => 'Table ID must be 10 characters or less.',
			),
		),
		'table_type' => array(
			'table_type-1' => array(
				'rule' => 'notEmpty',
				'message' => 'Discussion type must not be empty.',
			),
			'table_type-2' => array(
				'rule' => array('inList', array('group', 'project')),
			),
		),
		'author_id' => array(
			'author_id-1' => array(
				'rule' => 'notEmpty',
				'message' => 'Author ID must not be empty.',
			),
			'author_id-2' => array(
				'rule' => 'numeric',
				'message' => 'Author ID must be a number.',
			),
			'author_id-3' => array(
				'rule' => array('maxLength', 10),
				'message' => 'Author ID must be 10 characters or less.',
			),
		),
		'title' => array(
			'title-1' => array(
				'rule' => 'notEmpty',
				'message' => 'Title must not be empty.',
			),
			'title-2' => array(
				'rule' => array('maxLength', 255),
				'message' => 'Title must not be longer than 255 characters.',
			),
		),
		'created' => array(
			'rule' => 'notEmpty',
			'message' => 'Created must not be empty.',
		),
		'modified' => array(
			'rule' => 'notEmpty',
			'message' => 'Modified must not be empty.',
		),
		'content' => array(
			'rule' => 'notEmpty',
			'message' => 'Body must not be empty.',
		),
	);

	var $belongsTo = array(
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'table_id',
		),
		'Group' => array(
			'className' => 'Group',
			'foreignKey' => 'table_id',
		),
		'Project' => array(
			'className' => 'Project',
			'foreignKey' => 'table_id',
		),
		'Author' => array(
			'className' => 'User',
			'foreignKey' => 'author_id',
		),
		//TODO: Add Topic as well and add conditions to populate the correct key.
		// or just name it Parent
		'Category' => array(
			'className' => 'Discussion',
			'foreignKey' => 'parent_id',
		),
	);

	/**
	 * Returns a discussion root
	 *
	 * @param string  $table_type Table Type
	 * @param integer $table_id   Table ID
	 * @param integer $recursive  Recursion Level
	 *
	 * @return array Root
	 */
	function root($table_type, $table_id, $recursive = -1)
	{
		if(!is_string($table_type) || !in_array($table_type, array('group', 'project')))
		{
			throw new InvalidArgumentException('Invalid table type.');
		}

		if(!is_numeric($table_id) || $table_id < 1)
		{
			throw new InvalidArgumentException('Invalid table id.');
		}

		return $this->find('first', array(
			'conditions' => array(
				$this->name . '.table_type' => $table_type,
				$this->name . '.table_id' => $table_id,
				$this->name . '.type' => 'root',
			),
			'recursive' => $recursive,
		));
	}

	/**
	 * Returns a list of categories
	 *
	 * @param string  $table_type Table Type
	 * @param integer $table_id   Table ID
	 * @param integer $recursive  Recursion Level
	 *
	 * @return array Categories
	 */
	function categories($table_type, $table_id, $recursive = -1)
	{
		if(!is_string($table_type) || !in_array($table_type, array('group', 'project')))
		{
			throw new InvalidArgumentException('Invalid table type.');
		}

		if(!is_numeric($table_id) || $table_id < 1)
		{
			throw new InvalidArgumentException('Invalid table id.');
		}

		return $this->find('all', array(
			'conditions' => array(
				$this->name . '.table_type' => $table_type,
				$this->name . '.table_id' => $table_id,
				$this->name . '.type' => 'category',
			),
			'order' => $this->name . '.title',
			'recursive' => $recursive,
		));
	}

	/**
	 * Returns a list of topics
	 *
	 * @param string  $table_type  Table Type
	 * @param integer $table_id    Table ID
	 * @param integer $category_id Category ID
	 * @param integer $recursive   Recursion Level
	 *
	 * @return array Topics
	 */
	function topics($table_type, $table_id, $category_id, $recursive = -1)
	{
		if(!is_string($table_type) || !in_array($table_type, array('group', 'project')))
		{
			throw new InvalidArgumentException('Invalid table type.');
		}

		if(!is_numeric($table_id) || $table_id < 1)
		{
			throw new InvalidArgumentException('Invalid table id.');
		}

		if(!is_numeric($category_id) || $category_id < 1)
		{
			throw new InvalidArgumentException('Invalid category id.');
		}

		return $this->find('all', array(
			'conditions' => array(
				$this->name . '.table_type' => $table_type,
				$this->name . '.table_id' => $table_id,
				$this->name . '.type' => 'topic',
				$this->name . '.parent_id' => $category_id,
			),
			'order' => $this->name . '.title',
			'recursive' => $recursive,
		));
	}

	/**
	 * Returns a list of posts
	 *
	 * @param string  $table_type Table Type
	 * @param integer $table_id   Table ID
	 * @param integer $topic_id   Topic ID
	 * @param integer $recursive  Recursion Level
	 *
	 * @return array Posts
	 */
	function posts($table_type, $table_id, $topic_id, $recursive = 1)
	{
		if(!is_string($table_type) || !in_array($table_type, array('group', 'project')))
		{
			throw new InvalidArgumentException('Invalid table type.');
		}

		if(!is_numeric($table_id) || $table_id < 1)
		{
			throw new InvalidArgumentException('Invalid table id.');
		}

		if(!is_numeric($topic_id) || $topic_id < 1)
		{
			throw new InvalidArgumentException('Invalid topic id.');
		}

		return $this->find('all', array(
			'conditions' => array(
				$this->name . '.table_type' => $table_type,
				$this->name . '.table_id' => $table_id,
				$this->name . '.type' => 'post',
				$this->name . '.parent_id' => $topic_id,
			),
			'order' => $this->name . '.title',
			'recursive' => $recursive,
		));
	}

	/**
	 * Converts a record to a ExtJS Store node
	 *
	 * @param array  $post  Post
	 * @param string $model Model
	 *
	 * @return array ExtJS Store Node
	 */
	function toNode($post, $params = array())
	{
		if(empty($post))
		{
			throw new InvalidArgumentException('Invalid Post');
		}

		if(!is_array($post))
		{
			throw new InvalidArgumentException('Invalid Post');
		}

		if(!is_array($params))
		{
			throw new InvalidArgumentException('Invalid Parameters');
		}

		if(!isset($params['model']))
		{
			$params['model'] = $this->name;
		}

		$model = $params['model'];

		if(!isset($post[$model]))
		{
			throw new InvalidArgumentException('Invalid Model Key');
		}

		$required = array(
			'id',
			'table_type',
			'title',
			'created',
			'modified',
			'author_id',
			'content',
		);

		foreach($required as $key)
		{
			if(!array_key_exists($key, $post[$model]))
			{
				throw new InvalidArgumentException('Missing ' . strtoupper($key) . ' Key');
			}
		}

		$node = array(
			'id' => $post[$model]['id'],
			'table_id' => $post[$model]['table_id'],
			'table_type' => $post[$model]['table_type'],
			'title' => $post[$model]['title'],
			'created' => date('m/d/Y g:ia', strtotime($post[$model]['created'])),
			'modified' => date('m/d/Y g:ia', strtotime($post[$model]['modified'])),
			'lastpost_time' => date('m/d/Y g:ia', strtotime($post[$model]['modified'])),
			'lastpost_author' => 'Unknown',
			'lastpost_author_id' => $post[$model]['author_id'],
			'author' => 'Unknown',
			'author_id' => $post[$model]['author_id'],
			'content' => $post[$model]['content'],
			'posts' => 0,
			'category' => '',
			'parent_id' => $post[$model]['parent_id'],

			'text' => $post[$model]['title'],
			'leaf' => true,
		);

		if(in_array($post[$model]['type'], array('category', 'root')))
		{
			$node['leaf'] = false;
		}

		if(isset($post['Author']) && array_key_exists('name', $post['Author']))
		{
			$node['author'] = $post['Author']['name'];
			$node['lastpost_author'] = $post['Author']['name'];
		}

		if(isset($post['Category']) && array_key_exists('title', $post['Category']))
		{
			$node['category'] = $post['Category']['title'];
		}

		if(isset($post['children']))
		{
			if(!empty($post['children']))
			{
				$node['posts'] = count($post['children']);

				usort($post['children'], array($this, '_sort'));
				$child = $post['children'][0];

				if($post[$model]['type'] == 'category')
				{
					if(isset($child['children']) && !empty($child['children']))
					{
						usort($child['children'], array($this, '_sort'));
						$child = $child['children'][0];
					}
				}

				$node['lastpost_time'] = date('m/d/Y g:ia', strtotime($child[$model]['created']));
				if(isset($child['Author']))
				{
					$node['lastpost_author'] = $child['Author']['name'];
					$node['lastpost_author_id'] = $child['Author']['id'];
				}
			}
		}

		if(isset($params['values']))
		{
			if(is_array($params['values']))
			{
				$node = array_merge($node, $params['values']);
			}
		}

		return $node;
	}

	/**
	 * Sort Discussions
	 *
	 * @param array $a Discussion
	 * @param array $b Discussion
	 *
	 * @return Sort Priority
	 */
	function _sort($a, $b)
	{
		return strtotime($b['Discussion']['created']) - strtotime($a['Discussion']['created']);
	}
}
?>
