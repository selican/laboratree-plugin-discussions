<?php
class DiscussionsController extends DiscussionsAppController
{
	var $name = 'Discussions';

	var $uses = array(
		'Discussions.Discussion',
		'User',
		'Group',
		'Project',
		'ProjectsUsers',
	);

	var $components = array(
		'Auth',
		'Security',
		'Session',
		'RequestHandler',
		'Plugin',
	);

	var $types = array(
		'root' => 'category',
		'category' => 'topic',
		'topic' => 'post',
	);

	function beforeFilter()
	{
		$this->Security->validatePost = false;

		parent::beforeFilter();
	}

	/**
	 * Redirects to action based on Table Type
	 *
	 * @param string  $table_type Table Type
	 * @param integer $table_id   Table ID
	 */
	function index($table_type = '', $table_id = '')
	{
		if(empty($table_type))
		{
			$this->cakeError('missing_field', array('field' => 'Discussion Type'));
			return;
		}

		if(empty($table_id))
		{
			$this->cakeError('missing_field', array('field' => 'Discussion Owner'));
			return;
		}

		if(!is_numeric($table_id) || $table_id < 1)
		{
			$this->cakeError('invalid_field', array('field' => 'Discussion Owner'));
			return;
		}

		if(method_exists($this, $table_type))
		{
			$this->redirect('/discussions/' . $table_type . '/' . $table_id);
			return;
		}

		$this->cakeError('invalid_field', array('field' => 'Discussion Type'));
		return;
	}

	/**
	 * Displays Group Discussion Categories
	 *
	 * @param integer $group_id Group ID
	 */
	function group($group_id = '')
	{
		if(empty($group_id))
		{
			$this->cakeError('missing_field', array('field' => 'Group'));
			return;
		}

		if(!is_numeric($group_id) || $group_id < 1)
		{
			$this->cakeError('invalid_field', array('field' => 'Group ID'));
			return;
		}

		$group = $this->Group->find('first', array(
			'conditions' => array(
				'Group.id' => $group_id,
			),
			'recursive' => -1,
		));
		if(empty($group))
		{
			$this->cakeError('invalid_field', array('field' => 'Group'));
			return;
		}

		$permission = $this->PermissionCmp->check('discussion.view', 'group', $group_id);
		if(!$permission)
		{
			$this->cakeError('access_denied', array('resource' => 'Discussion', 'action' => 'View'));
			return;
		}

		$this->pageTitle = "Discussions - {$group['Group']['name']}";
		$this->set('pageName', $group['Group']['name'] . ' - Discussions');

		$this->set('group', $group);
		$this->set('group_id', $group_id);

		$context = array(
			'table_type' => 'group',
			'table_id' => $group_id,

			'group_id' => $group_id,

			'permissions' => array(
				'discussion' => $permission['mask'],
			),
		);
		$this->set('context', $context);

		if($this->RequestHandler->prefers('json'))
		{
			$limit = 30;
			if(isset($this->params['form']['limit']))
			{
				$limit = $this->params['form']['limit'];
			}

			$start = 0;
			if(isset($this->params['form']['start']))
			{
				$start = $this->params['form']['start'];
			}

			$root = $this->Discussion->find('first', array(
				'conditions' => array(
					'Discussion.table_type' => 'group',
					'Discussion.table_id' => $group_id,
					'Discussion.parent_id' => null,
					'Discussion.type' => 'root',
				),
				'recursive' => -1,
			));
			if(empty($root))
			{
				$this->cakeError('internal_error', array('action' => 'View', 'resource' => 'Discussion'));
				return;
			}

			$discussions = $this->Discussion->find('threaded', array(
				'conditions' => array(
					'Discussion.table_type' => 'group',
					'Discussion.table_id' => $group_id,
					'Discussion.lft >' => $root['Discussion']['lft'],
					'Discussion.rght <' => $root['Discussion']['rght']
				),
				'recursive' => 1,
				'order' => 'Discussion.created DESC',
				'limit' => $limit,
				'offset' => $start,
			));

			try {
				$response = $this->Discussion->toList('discussions', $discussions);
			} catch(Exception $e) {
				$this->cakeError('internal_error', array('action' => 'Convert', 'resource' => 'Discussions'));
				return;
			}

			$this->set('response', $response);
		}
	}

	/**
	 * Displays Project Discussion Categories
	 *
	 * @param integer $project_id Project ID
	 */
	function project($project_id = '')
	{
		if(empty($project_id))
		{
			$this->cakeError('missing_field', array('field' => 'Project'));
			return;
		}

		if(!is_numeric($project_id) || $project_id < 1)
		{
			$this->cakeError('invalid_field', array('field' => 'Project ID'));
			return;
		}

		$project = $this->Project->find('first', array(
			'conditions' => array(
				'Project.id' => $project_id,
			),
			'recursive' => -1,
		));
		if(empty($project))
		{
			$this->cakeError('invalid_field', array('field' => 'Project'));
			return;
		}

		$group = $this->Group->find('first', array(
			'conditions' => array(
				'Group.id' => $project['Project']['group_id'],
			),
			'recursive' => -1,
		));
		if(empty($group))
		{
			$this->cakeError('internal_error', array('action' => 'View', 'resource' => 'Project Discussions'));
			return;
		}

		$permission = $this->PermissionCmp->check('discussion.view', 'project', $project_id);
		if(!$permission)
		{
			$this->cakeError('access_denied', array('resource' => 'Discussion', 'action' => 'View'));
			return;
		}

		$this->pageTitle = "Discussions - {$project['Project']['name']}";
		$this->set('pageName', $project['Project']['name'] . ' - Discussions');

		$this->set('project', $project);
		$this->set('project_id', $project_id);

		$this->set('group_name', $group['Group']['name']);
		$this->set('group_id', $group['Group']['id']);

		$context = array(
			'table_type' => 'project',
			'table_id' => $project_id,

			'project_id' => $project_id,
			'group_id' => $group['Group']['id'],

			'permissions' => array(
				'discussion' => $permission['mask'],
			),
		);
		$this->set('context', $context);

		if($this->RequestHandler->prefers('json'))
		{
			$limit = 30;
			if(isset($this->params['form']['limit']))
			{
				$limit = $this->params['form']['limit'];
			}

			$start = 0;
			if(isset($this->params['form']['start']))
			{
				$start = $this->params['form']['start'];
			}
		
			$root = $this->Discussion->find('first', array(
				'conditions' => array(
					'Discussion.table_type' => 'project',
					'Discussion.table_id' => $project_id,
					'Discussion.parent_id' => null,
					'Discussion.type' => 'root',
				),
				'recursive' => -1,
			));
			if(empty($root))
			{
				$this->cakeError('internal_error', array('action' => 'View', 'resource' => 'Discussion'));
				return;
			}
			
			$discussions = $this->Discussion->find('threaded', array(
				'conditions' => array(
					'Discussion.table_type' => 'project',
					'Discussion.table_id' => $project_id,
					'Discussion.lft >' => $root['Discussion']['lft'],
					'Discussion.rght <' => $root['Discussion']['rght']
				),
				'recursive' => 1,
				'order' => 'Discussion.created DESC',
				'limit' => $limit,
				'offset' => $start,
			));
			try {
				$response = $this->Discussion->toList('discussions', $discussions);
			} catch(Exception $e) {
				$this->cakeError('internal_error', array('action' => 'Convert', 'resource' => 'Discussions'));
				return;
			}

			$this->set('response', $response);
		}
	}

	/**
	 * View Group or Project Discussion Category or Topic
	 *
	 * @param $discussion_id Discussion ID
	 */
	function view($discussion_id = '')
	{
		if(empty($discussion_id))
		{
			$this->cakeError('missing_field', array('field' => 'Discussion Post'));
			return;
		}

		$discussion = $this->Discussion->find('first', array(
			'conditions' => array(
				'Discussion.id' => $discussion_id,
			),
			'recursive' => 1,
		));
		if(empty($discussion))
		{
			$this->cakeError('invalid_field', array('field' => 'Discussion Post'));
			return;
		}

		if($discussion['Discussion']['type'] == 'post')
		{
			$this->redirect('/discussions/view/' . $discussion['Discussion']['parent_id'] . '#discussion-' . $discussion_id);
			return;
		}

		if($discussion['Discussion']['type'] != 'topic')
		{
			$this->cakeError('invalid_field', array('field' => 'Discussion Post'));
			return;
		}

		$permission = $this->PermissionCmp->check('discussion.view', $discussion['Discussion']['table_type'], $discussion['Discussion']['table_id']);
		if(!$permission)
		{
			$this->cakeError('access_denied', array('resource' => 'Discussion', 'action' => 'View'));
			return;
		}

		if($this->RequestHandler->prefers('json'))
		{
			if(!empty($this->data))
			{
				$this->data['Discussion']['table_id'] = $discussion['Discussion']['table_id'];
				$this->data['Discussion']['table_type'] = $discussion['Discussion']['table_type'];
				$this->data['Discussion']['type'] = 'post';
				$this->data['Discussion']['author_id'] = $this->Session->read('Auth.User.id');
				$this->data['Discussion']['title'] = 'RE: ' . $discussion['Discussion']['title'];
				$this->data['Discussion']['parent_id'] = $discussion_id;

				$this->Discussion->create();
				if($this->Discussion->save($this->data))
				{
					$post_id = $this->Discussion->id;

					$response = array(
						'success' => true,
						'discussion' => $this->Discussion->read(),
					);
				}
				else
				{
					//TODO: Add validation errors on errors key
					$response = array(
						'success' => false,
					);
				}

				$this->set('response', $response);
			}
			else
			{
				$conditions = array(
					'Discussion.lft >=' => $discussion['Discussion']['lft'],
					'Discussion.rght <=' => $discussion['Discussion']['rght'],
				);
				$contain = array(
					'Author',
				);
				$fields = array(
					'Discussion.id',
					'Discussion.created',
					'Discussion.content',
					'Discussion.parent_id',
					'Discussion.type',
					'Author.name',
					'Author.picture',
				);
				$discussion = $this->Discussion->find('threaded', array(
					'conditions' => $conditions,
					'contain' => $contain,
					'fields' => $fields,
					'order' => 'Discussion.lft',
					'recursive' => 1,
				));

				$this->set('response', $discussion);
			}
		}
		else
		{
			$category = $this->Discussion->find('first', array(
				'conditions' => array(
					'Discussion.id' => $discussion['Discussion']['parent_id'],
				),
			));
			if(empty($category))
			{
				$this->cakeError('internal_error', array('action' => 'View', 'resource' => 'Discussion'));
				return;
			}

			$name = null;
			switch($discussion['Discussion']['table_type'])
			{
				case 'user':
					$this->User->id = $discussion['Discussion']['table_id'];
					$name = $this->User->field('name');
					break;
				case 'group':
					$this->Group->id = $discussion['Discussion']['table_id'];
					$name = $this->Group->field('name');
					$this->set('group_id', $discussion['Discussion']['table_id']);
					break;
				case 'project':
					$this->Project->id = $discussion['Discussion']['table_id'];
					$name = $this->Project->field('name');
					$group_id = $this->Project->field('group_id');

					$this->set('project_id', $discussion['Discussion']['table_id']);

					$group = $this->Group->find('first', array(
						'conditions' => array(
							'Group.id' => $group_id,
						),
						'recursive' => -1,
					));
					if(empty($group))
					{
						$this->cakeError('internal_error', array('action' => 'View', 'resource' => 'Discussion'));
						return;
					}

					$this->set('group_name', $group['Group']['name']);
					$this->set('group_id', $group['Group']['id']);
					break;
				default:
					$this->cakeError('invalid_field', array('field' => 'Table Type'));
					return;
			}

			$this->pageTitle = 'View Discussion - ' . $discussion['Discussion']['title'] . ' - ' . $category['Discussion']['title'] . ' - ' . $name;
			$this->set('pageName', $name . ' - View Discussion - ' . $category['Discussion']['title'] . ' - ' . $discussion['Discussion']['title']);

			$this->set('discussion', $discussion);
			$this->set('category', $category);
			$this->set('name', $name);

			$this->set('discussion_id', $discussion_id);
			$this->set('parent_id', $discussion['Discussion']['parent_id']);

			$context = array(
				'table_type' => $discussion['Discussion']['table_type'],
				'table_id' => $discussion['Discussion']['table_id'],

				'discussion_id' => $discussion_id,
				'parent_id' => $discussion['Discussion']['parent_id'],

				'permissions' => array(
					'discussion' => $permission['mask'],
				),
			);
			$this->set('context', $context);
		}
	}

	/**
	 * Adds a Group or Project Discussion Category or Topic
	 *
	 * @param string  $table_type Table Type
	 * @param integer $table_id   Table ID
	 * @param integer $parent_id  Category or Topic ID
	 */
	function add($table_type = '', $table_id = '', $parent_id = null)
	{
		if(empty($table_type))
		{
			$this->cakeError('missing_field', array('field' => 'Discussion Type'));
			return;
		}

		if(!in_array($table_type, array('group', 'project')))
		{
			$this->cakeError('invalid_field', array('field' => 'Discussion Type'));
			return;
		}

		if(empty($table_id))
		{
			$this->cakeError('missing_field', array('field' => 'Discussion Owner'));
			return;
		}

		if(!is_numeric($table_id) || $table_id < 1)
		{
			$this->cakeError('invalid_field', array('field' => 'Discussion Owner'));
			return;
		}

		if(!empty($parent_id) && (!is_numeric($parent_id) || $parent_id < 1))
		{
			$this->cakeError('invalid_field', array('field' => 'Parent ID'));
			return;
		}

		$typename = 'category';
		if(empty($parent_id)) // Adding a Category
		{
			$parent = $this->Discussion->find('first', array(
				'conditions' => array(
					'Discussion.table_type' => $table_type,
					'Discussion.table_id' => $table_id,
					'Discussion.type' => 'root',
				),
				'recursive' => -1,
			));
			if(empty($parent))
			{
				$this->cakeError('internal_error', array('action' => 'Add', 'resource' => 'Discussion'));
				return;
			}

			$permission = $this->PermissionCmp->check('discussion.category.add', $table_type, $table_id);
			if(!$permission)
			{
				$this->cakeError('access_denied', array('action' => 'Add', 'resource' => 'Discussion Post'));
				return;
			}

			$this->set('parent', $parent);
			$parent_id = $parent['Discussion']['id'];
		}
		else // Adding a Topic or Post (although technically could be a Category)
		{
			$parent = $this->Discussion->find('first', array(
				'conditions' => array(
					'Discussion.id' => $parent_id,
				),
				'recursive' => -1,
			));
			if(empty($parent))
			{
				$this->cakeError('invalid_field', array('field' => 'Parent ID'));
				return;
			}
			if($parent['Discussion']['type'] == 'post')
			{
				$this->cakeError('invalid_field', array('field' => 'Parent ID'));
				return;
			}

			switch($parent['Discussion']['type'])
			{
				case 'root':
					$typename = 'category';
					break;
				case 'category':
					$typename = 'topic';
					break;
				case 'topic':
					$typename = 'post';
					break;
				default:
					$this->cakeError('invalid_field', array('field' => 'Type Name'));
					return;
						
			}

			if(!$this->PermissionCmp->check('discussion.' . $typename . '.add', $parent['Discussion']['table_type'], $parent['Discussion']['table_id']))
			{
				$this->cakeError('access_denied', array('action' => 'Add', 'resource' => 'Discussion Post'));
				return;
			}
			$this->set('parent', $parent);
		}

		$name = null;
		switch($table_type)
		{
			case 'group':
				$this->Group->id = $table_id;
				$name = $this->Group->field('name');
				$this->set('group_id', $table_id);
				break;
			case 'project':
				$this->Project->id = $table_id;
				$name = $this->Project->field('name');
				$group_id = $this->Project->field('group_id');

				$this->set('project_id', $table_id);

				$group = $this->Group->find('first', array(
					'conditions' => array(
						'Group.id' => $group_id,
					),
					'recursive' => -1,
				));
				if(empty($group))
				{
					$this->cakeError('internal_error', array('action' => 'Add', 'resource' => 'Discussion Post'));
					return;
				}

				$this->set('group_name', $group['Group']['name']);
				$this->set('group_id', $group['Group']['id']);
				break;
			default:
				$this->cakeError('invalid_field', array('field' => 'Table Type'));
				return;
		}

		$this->pageTitle = 'Add Discussion ' . ucfirst($typename) . ' - ' . $name;
		$this->set('pageName', $name . ' - Add Discussion ' . ucfirst($typename));

		$this->set('table_type', $table_type);
		$this->set('table_id', $table_id);
		$this->set('name', $name);
		$this->set('parent_id', $parent_id);
		$this->set('typename', ucfirst($typename));

		$context = array(
			'table_type' => $table_type,
			'table_id' => $table_id,

			'permissions' => array(
				'discussion' => $permission['mask'],
			),
		);
		$this->set('context', $context);

		if(!empty($this->data))
		{
			if(!isset($this->data['Discussion']))
			{
				$this->cakeError('invalid_field', array('field' => 'Data'));
				return;
			}

			$this->data['Discussion']['table_type'] = $table_type;
			$this->data['Discussion']['table_id'] = $table_id;
			$this->data['Discussion']['type'] = $typename;
			$this->data['Discussion']['author_id'] = $this->Session->read('Auth.User.id');
			$this->data['Discussion']['parent_id'] = $parent_id;

			$this->Discussion->create();
			if($this->Discussion->save($this->data))
			{
				$discussion_id = $this->Discussion->id;

				try {
					$this->Plugin->broadcastListeners('discussions.add', array(
						$discussion_id,
						$table_type,
						$table_id,
						$typename,
						$this->data['Discussion']['title'],
					));
				} catch(Exception $e) {
					$this->cakeError('internal_error', array('action' => 'Add', 'resource' => 'Discussion'));
					return;
				}

				switch($typename)
				{
					case 'category':
						$this->redirect('/discussions/topics/' . $discussion_id);
						break;
					case 'topic':
						$this->redirect('/discussions/view/' . $discussion_id);
						break;
					case 'post':
						$this->redirect('/discussions/view/' . $parent_id . '#discussion-' . $discussion_id);
						break;
					default:
						$this->cakeError('internal_error', array('action' => 'Add', 'resource' => 'Discussion'));
				}
				return;
			}
		}
	}

	/**
	 * Edit a Discussion Category, Topic, or Post
	 *
	 * @param integer $discussion_id Discussion ID
	 */
	function edit($discussion_id = '')
	{
		if(empty($discussion_id))
		{
			$this->cakeError('missing_field', array('field' => 'Discussion Post'));
			return;
		}

		if(!is_numeric($discussion_id) || $discussion_id < 1)
		{
			$this->cakeError('invalid_field', array('field' => 'Discussion ID'));
			return;
		}

		$discussion = $this->Discussion->find('first', array(
			'conditions' => array(
				'Discussion.id' => $discussion_id,
			),
			'recursive' => 0,
		));
		if(empty($discussion))
		{
			$this->cakeError('invalid_field', array('field' => 'Discussion Post'));
			return;
		}

		$permission = $this->PermissionCmp->check('discussion.' . $discussion['Discussion']['type'] . '.edit', $discussion['Discussion']['table_type'], $discussion['Discussion']['table_id']);
		if(!$permission)
		{
			//TODO: We need to make sure that user is still a member of the group/project
			if($discussion['Discussion']['author_id'] != $this->Session->read('Auth.User.id'))
			{
				$this->cakeError('access_denied', array('action' => 'Edit', 'resource' => 'Discussion Post'));
				return;
			}
		}
		if($this->RequestHandler->prefers('json'))
		{
			$discussion = $this->Discussion->find('first', array(
				'conditions' => array(
					'Discussion.id' => $discussion_id,
				),
				'recursive' => 1,
			));
			
			try {
				$node = $this->Discussion->toNode($discussion);
			} catch(Exception $e) {
				$this->cakeError('internal_error', array('action' => 'Convert', 'resource' => 'Discussion'));
				return;
			}
			$this->set('node', $node);
		}
		else
		{
			$table_id = $discussion['Discussion']['table_id'];
			$name = null;
			switch($discussion['Discussion']['table_type'])
			{
				case 'group':
					$this->Group->id = $discussion['Discussion']['table_id'];
					$name = $this->Group->field('name');
					$this->set('group_id', $discussion['Discussion']['table_id']);
					break;
				case 'project':
					$this->Project->id = $discussion['Discussion']['table_id'];
					$name = $this->Project->field('name');
					$group_id = $this->Project->field('group_id');

					$this->set('project_id', $discussion['Discussion']['table_id']);

					$group = $this->Group->find('first', array(
						'conditions' => array(
							'Group.id' => $group_id,
						),
						'recursive' => -1,
					));
					if(empty($group))
					{
						$this->cakeError('internal_error', array('action' => 'Edit', 'resource' => 'Discussion'));
						return;
					}

					$this->set('group', $group);
					$this->set('group_id', $group['Group']['id']);
					break;
				default:
					$this->cakeError('invalid_field', array('field' => 'Table Type'));
					return;
			}

			$typename = Inflector::humanize($discussion['Discussion']['type']);

			$this->pageTitle = 'Edit Discussion ' . $typename . ' - ' . $discussion['Discussion']['title'] . ' - ' . $name;
			$this->set('pageName', $name . ' - ' . $discussion['Discussion']['title'] . ' - Edit Discussion ' . $typename);

			$this->set('typename', $typename);

			$this->set('discussion', $discussion);
			$this->set('discussion_id', $discussion_id);
			$this->set('name', $name);

			$context = array(
				'table_type' => $discussion['Discussion']['table_type'],
				'table_id' => $discussion['Discussion']['table_id'],

				'discussion_id' => $discussion_id,

				'permissions' => array(
					'discussion' => $permission['mask'],
				),
			);
			$this->set('context', $context);
	
			if(!empty($this->data))
			{
				if(!isset($this->data['Discussion']))
				{
					$this->cakeError('invalid_field', array('field' => 'Data'));
					return;
				}

				$this->data['Discussion']['id'] = $discussion_id;
					
				if($this->Discussion->save($this->data))
				{
					try {
						$this->Plugin->broadcastListeners('discussions.edit', array(
							$discussion_id,
							$discussion['Discussion']['table_type'],
							$discussion['Discussion']['table_id'],
							$discussion['Discussion']['type'],
							$this->data['Discussion']['title'],
						));
					} catch(Exception $e) {
						$this->cakeError('internal_error', array('action' => 'Edit', 'resource' => 'Discussion'));
						return;
					}

					switch($discussion['Discussion']['type'])
					{
						case 'category':
							$this->redirect('/discussions/topics/' . $discussion_id);
							break;
						case 'topic':
							$this->redirect('/discussions/view/' . $discussion_id);
							break;
						case 'post':
							$this->redirect('/discussions/view/' . $discussion['Discussion']['parent_id'] . '#discussion-' . $discussion_id);
							break;
						default:
							$this->cakeError('internal_error', array('action' => 'Edit', 'resource' => 'Discussion'));
							return;
					}
					return;
				}
			}
		}
	}

	/**
	 * Delete a Discussion Category, Topic, or Post
	 *
	 * @param integer $discussion_id Discussion ID
	 */
	function delete($discussion_id = '')
	{
		if(!$this->RequestHandler->prefers('json'))
		{
			$this->cakeError('error404');
			return;
		}

		if(empty($discussion_id))
		{
			$this->cakeError('missing_field', array('field' => 'Discussion ID'));
			return;
		}

		if(!is_numeric($discussion_id) || $discussion_id < 1)
		{
			$this->cakeError('invalid_field', array('field' => 'Discussion ID'));
			return;
		}

		$discussion = $this->Discussion->find('first', array(
			'conditions' => array(
				'Discussion.id' => $discussion_id,
			),
			'recursive' => -1,
		));
		if(empty($discussion))
		{
			$this->cakeError('invalid_field', array('field' => 'Discussion ID'));
			return;
		}

		if(!$this->PermissionCmp->check('discussion.' . $discussion['Discussion']['type'] . '.delete', $discussion['Discussion']['table_type'], $discussion['Discussion']['table_id']))
		{
			$this->cakeError('access_denied', array('action' => 'Delete', 'resource' => 'Discussion Post'));
			return;
		}

		$this->Discussion->delete($discussion_id);

		try {
			$this->Plugin->broadcastListeners('discussions.delete', array(
				$discussion_id,
				$discussion['Discussion']['table_type'],
				$discussion['Discussion']['table_id'],
				$discussion['Discussion']['type'],
				$discussion['Discussion']['title'],
			));
		} catch(Exception $e) {
			$this->cakeError('internal_error', array('action' => 'Delete', 'resource' => 'Discussion'));
			return;
		}

		$response = array(
			'success' => true,
		);

		$this->set('response', $response);
	}

	/**
	 * Displays Discussion Topics for a Group or Project
	 *
	 * @param integer $discussion_id Discussion ID
	 */
	function topics($discussion_id = '')
	{
		if(empty($discussion_id))
		{
			$this->cakeError('missing_field', array('field' => 'Category ID'));
			return;
		}
		
		if(!is_numeric($discussion_id) || $discussion_id < 1)
		{
			$this->cakeError('invalid_field', array('field' => 'Discussion ID'));
			return;
		}

		$discussion = $this->Discussion->find('first', array(
			'conditions' => array(
				'Discussion.id' => $discussion_id,
			),
			'recursive' => 1,
		));
		if(empty($discussion))
		{
			$this->cakeError('invalid_field', array('field' => 'Discussion ID'));
			return;
		}

		if($discussion['Discussion']['type'] != 'category')
		{
			$this->cakeError('invalid_field', array('field' => 'Discussion ID'));
			return;
		}

		$permission = $this->PermissionCmp->check('discussion.view', $discussion['Discussion']['table_type'], $discussion['Discussion']['table_id']);
		if(!$permission)
		{
			$this->cakeError('access_denied', array('action' => 'View', 'resource' => 'Discussion Topics'));
			return;
		}

		$name = null;
		switch($discussion['Discussion']['table_type'])
		{
			case 'group':
				$this->Group->id = $discussion['Discussion']['table_id'];
				$name = $this->Group->field('name');
				$this->set('group_id', $discussion['Discussion']['table_id']);
				break;
			case 'project':
				$this->Project->id = $discussion['Discussion']['table_id'];
				$name = $this->Project->field('name');
				$group_id = $this->Project->field('group_id');

				$this->set('project_id', $discussion['Discussion']['table_id']);

				$group = $this->Group->find('first', array(
					'conditions' => array(
						'Group.id' => $group_id,
					),
					'recursive' => -1,
				));
				if(empty($group))
				{
					$this->cakeError('internal_error', array('action' => 'View', 'resource' => 'Discussion Topics'));
					return;
				}

				$this->set('group_name', $group['Group']['name']);
				$this->set('group_id', $group['Group']['id']);
				break;
			default:
				$this->cakeError('invalid_field', array('field' => 'Table Type'));
				return;
		}

		$this->pageTitle = 'Discussion Topics - ' . $discussion['Discussion']['title'] . ' - ' . $name;
		$this->set('pageName', $name . ' - Discussion Topics - ' . $discussion['Discussion']['title']);

		$this->set('name', $name);
		$this->set('discussion', $discussion);
		$this->set('discussion_id', $discussion_id);
		$this->set('category_id', $discussion_id);

		$context = array(
			'table_type' => $discussion['Discussion']['table_type'],
			'table_id' => $discussion['Discussion']['table_id'],

			'discussion_id' => $discussion_id,

			'permissions' => array(
				'discussion' => $permission['mask'],
			),
		);
		$this->set('context', $context);

		if($this->RequestHandler->prefers('json'))
		{
			$limit = 30;
			if(isset($this->params['form']['limit']))
			{
				$limit = $this->params['form']['limit'];
			}

			$start = 0;
			if(isset($this->params['form']['start']))
			{
				$start = $this->params['form']['start'];
			}

			$conditions = array(
			);

			$discussions = $this->Discussion->find('threaded', array(
				'conditions' => array(
					'Discussion.table_type' => $discussion['Discussion']['table_type'],
					'Discussion.table_id' => $discussion['Discussion']['table_id'],
					'Discussion.lft >' => $discussion['Discussion']['lft'],
					'Discussion.rght <' => $discussion['Discussion']['rght']
				),
				'recursive' => 1,
				'order' => 'Discussion.created DESC',
				'limit' => $limit,
				'offset' => $start,
			));
			try {
				$response = $this->Discussion->toList('discussions', $discussions);
			} catch(Exception $e) {
				$this->cakeError('internal_error', array('action' => 'Convert', 'resource' => 'Discussions'));
				return;
			}

			$this->set('response', $response);
		}
	}

	/**
	 * Help for Discussions
	 */
	function help_index()
	{
		$this->pageTitle = 'Help - Index - Dicscussions';
		$this->set('pageName', 'Discussions - Index - Help');
	}

	/**
	 * Help for Group
	 */
	function help_group()
	{
		$this->pageTitle = 'Help - Group - Dicscussions';
		$this->set('pageName', 'Discussions - Group - Help');
	}

	/**
	 * Help for Projet
	 */
	function help_project()
	{
		$this->pageTitle = 'Help - Project - Dicscussions';
		$this->set('pageName', 'Discussions - Project - Help');
	}

	/**
	 * Help for Add
	 */
	function help_add()
	{
		$this->pageTitle = 'Help - Add - Dicscussions';
		$this->set('pageName', 'Discussions - Add - Help');
	}

	/**
	 * Help for Edit
	 */
	function help_edit()
	{
		$this->pageTitle = 'Help - Edit - Dicscussions';
		$this->set('pageName', 'Discussions - Edit - Help');
	}

	/**
	 * Help for Topics
	 */
	function help_topics()
	{
		$this->pageTitle = 'Help - Topics - Dicscussions';
		$this->set('pageName', 'Discussions - Topics - Help');
	}
}
?>
