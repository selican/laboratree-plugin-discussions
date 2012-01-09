<?php
	/* Define Constants */
	if(!defined('DISCUSSIONS_APP'))
	{
		define('DISCUSSIONS_APP', APP . DS . 'plugins' . DS . 'discussions');
	}

	if(!defined('DISCUSSIONS_CONFIGS'))
	{
		define('DISCUSSIONS_CONFIGS', DISCUSSIONS_APP . DS . 'config');
	}

	/* Include Config File */
	require_once(DISCUSSIONS_CONFIGS . DS . 'discussions.php');

	/* Setup Permissions */
	try {
		$parent = $this->addPermission('discussions', 'Discussion');

		$this->addPermission('discussions.view', 'View Discussion', 1, $parent);

		$this->addPermission('discussions.category.add', 'Add Discussion Category', 2, $parent);
		$this->addPermission('discussions.category.edit', 'Edit Discussion Category', 4, $parent);
		$this->addPermission('discussions.category.delete', 'Delete Discussion Category', 8, $parent);

		$this->addPermission('discussions.topic.add', 'Add Discussion Topic', 16, $parent);
		$this->addPermission('discussions.topic.edit', 'Edit Discussion Topic', 32, $parent);
		$this->addPermission('discussions.topic.delete', 'Delete Discussion Topic', 64, $parent);

		$this->addPermission('discussions.post.add', 'Add Discussion Post', 128, $parent);
		$this->addPermission('discussions.post.edit', 'Edit Discussion Post', 256, $parent);
		$this->addPermission('discussions.post.delete', 'Delete Discussion Post', 512, $parent);

		$this->addPermissionDefaults(array(
			'group' => array(
				'discussions' => array(
					'Administrator' => 1022,
					'Manager' => 1008,
					'Member' => 128,
				),
			),
			'project' => array(
				'discussions' => array(
					'Administrator' => 1022,
					'Manager' => 1008,
					'Member' => 128,
				),
			),
		));
	} catch(Exception $e) {
		// TODO; Do something
	}

	/* Add Listeners */
	try {
		$this->addListener('discussions', 'group.add', function($id, $name) {
			App::import('Model', 'Discussions.Discussion');
			$discussion = new Discussion();

			$data = array(
				'table_type' => 'group',
				'table_id' => $id,
				'title' => 'ROOT',
				'type' => 'root',
			);
			$discussion->create();
			if(!$discussion->save($data))
			{
				throw new RuntimeException('Unable to save discussion root');
			}
		});

		$this->addListener('discussions', 'group.delete', function($id, $name) {
			App::import('Model', 'Discussions.Discussion');
			$discussion = new Discussion();

			if(!$discussion->deleteAll(array(
				'Discussion.table_type' => 'group',
				'Discussion.table_id' => $id,
			), true))
			{
				throw new RuntimeException('Unable to delete discussions');
			}
		});

		$this->addListener('discussions', 'project.add', function($id, $name) {
			App::import('Model', 'Discussions.Discussion');
			$discussion = new Discussion();

			$data = array(
				'table_type' => 'project',
				'table_id' => $id,
				'title' => 'ROOT',
				'type' => 'root',
			);
			$discussion->create();
			if(!$discussion->save($data))
			{
				throw new RuntimeException('Unable to save discussion root');
			}
		});

		$this->addListener('discussions', 'project.delete', function($id, $name) {
			App::import('Model', 'Discussions.Discussion');
			$discussion = new Discussion();

			if(!$discussion->deleteAll(array(
				'Discussion.table_type' => 'project',
				'Discussion.table_id' => $id,
			)))
			{
				throw new RuntimeException('Unable to delete discussions');
			}
		});
	} catch(Exception $e) {
		// TODO: Do something
	}
?>
