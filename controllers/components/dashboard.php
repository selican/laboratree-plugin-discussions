<?php
class DashboardComponent extends Object
{
	var $uses = array(
		'Discussions.Discussion',
		'GroupsUsers',
		'ProjectsUsers',
	);

	function _loadModels(&$object)
	{
		foreach($object->uses as $modelClass)
		{
			$plugin = null;

			if(strpos($modelClass, '.') !== false)
			{
				list($plugin, $modelClass) = explode('.', $modelClass);
				$plugin = $plugin . '.';
			}

			App::import('Model', $plugin . $modelClass);
			$this->{$modelClass} = new $modelClass();

			if(!$this->{$modelClass})
			{
				return false;
			}
		}
	}

	function initialize(&$controller, $settings = array())
	{
		$this->Controller =& $controller;
		$this->_loadModels($this);
	}

	function startup(&$controller) {}

	function process($table_type, $table_id, $params = array())
	{
		$discussions = array();

		if($table_type == 'user')
		{
			if(isset($params['form']['node']) && !preg_match('/xnode-/', $params['form']['node']))
			{
				if(preg_match('/^root-(group|project)-(\d+)$/', $params['form']['node'], $matches))
				{
					switch($matches[1])
					{
						case 'group':
							try {
								$categories = $this->Discussion->categories('group', $matches[2]);
							} catch(Exception $e) {
								throw new RuntimeException('Unable to retrieve discussion categories');
							}

							if(!empty($categories))
							{
								$discussions = array_merge($discussions, $categories);
							}
							break;
						case 'project':
							try {
								$categories = $this->Discussion->categories('project', $matches[2]);
							} catch(Exception $e) {
								throw new RuntimeException('Unable to retrieve discussion cateogires');
							}
							if(!empty($categories))
							{
								$discussions = array_merge($discussions, $categories);
							}
							break;
					}
				}
				else
				{
					$parent_id = $params['form']['node'];

					$this->Discussion->id = $parent_id;
					if(!$this->Discussion->exists())
					{
						throw new RuntimeException('Unable to retrieve discussion');
					}

					$discussions = $this->Discussion->children($parent_id, true, null, 'lft ASC');
				}
			}
			else
			{
				try {
					$groups = $this->GroupsUsers->groups($table_id);
				} catch(Exception $e) {
					throw new RuntimeException('Unable to retreive groups');
				}

				foreach($groups as $group)
				{
					$discussions[] = array(
						'Discussion' => array(
							'id' => 'root-group-' . $group['Group']['id'],
							'table_type' => 'group',
							'table_id' => $group['Group']['id'],
							'type' => 'root',
							'author_id' => $table_id,
							'title' => 'Group: ' . $group['Group']['name'],
							'created' => date('Y-m-d H:i:s'),	
							'modified' => date('Y-m-d H:i:s'),
							'content' => null,
							'parent_id' => null,
							'lft' => 1,
							'rght' => 2,
						),
					);
				}

				try {
					$projects = $this->ProjectsUsers->projects($table_id);
				} catch(Exception $e) {
					throw new RuntimeException('Unable to retrieve projects');
				}

				foreach($projects as $project)
				{
					$discussions[] = array(
						'Discussion' => array(
							'id' => 'root-project-' . $project['Project']['id'],
							'table_type' => 'project',
							'table_id' => $project['Project']['id'],
							'type' => 'root',
							'author_id' => $table_id,
							'title' => 'Project: ' . $project['Project']['name'],
							'created' => date('Y-m-d H:i:s'),	
							'modified' => date('Y-m-d H:i:s'),
							'content' => null,
							'parent_id' => null,
							'lft' => 1,
							'rght' => 2,
						),
					);
				}
			}
		}
		else
		{
			$discussions = $this->Discussion->find('all', array(
				'conditions' => array(
					'Discussion.table_type' => $table_type,
					'Discussion.table_id' => $table_id,
					'Discussion.type' => 'topic',
					'NOT' => array(
						'Discussion.parent_id' => null
					),
				),
				'contain' => array(
					'Category',
				),
				'order' => 'Discussion.title',
			));
		}

		try {
			$list = $this->Discussion->toList('discussions', $discussions);
		} catch(Exception $e) {
			throw new Exception($e);
		}

		return $list;
	}
}
?>
