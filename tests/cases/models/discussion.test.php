<?php 
/* SVN FILE: $Id$ */
/* Discussion Test cases generated on: 2010-12-20 14:54:29 : 1292856869*/
App::import('Model', 'Discussion');

class DiscussionTestCase extends CakeTestCase {
	var $Discussion = null;
	var $fixtures = array('app.helps', 'app.app_category', 'app.app_data', 'app.application', 'app.app_module', 'app.attachment', 'app.discussion', 'app.doc', 'app.docs_permission', 'app.docs_tag', 'app.docs_type_data', 'app.docs_type_field', 'app.docs_type', 'app.docs_type_row', 'app.docs_version', 'app.group', 'app.groups_address', 'app.groups_association', 'app.groups_award', 'app.groups_interest', 'app.groups_phone', 'app.groups_projects', 'app.groups_publication', 'app.groups_setting', 'app.groups_url', 'app.groups_users', 'app.inbox', 'app.inbox_hash', 'app.interest', 'app.message_archive', 'app.message', 'app.note', 'app.ontology_concept', 'app.preference', 'app.project', 'app.projects_association', 'app.projects_interest', 'app.projects_setting', 'app.projects_url', 'app.projects_users', 'app.role', 'app.setting', 'app.site_role', 'app.tag', 'app.type', 'app.url', 'app.user', 'app.users_address', 'app.users_association', 'app.users_award', 'app.users_education', 'app.users_interest', 'app.users_job', 'app.users_phone', 'app.users_preference', 'app.users_publication', 'app.users_url');

	function startTest() {
		$this->Discussion =& ClassRegistry::init('Discussion');
	}

	function testDiscussionInstance() {
		$this->assertTrue(is_a($this->Discussion, 'Discussion'));
	}

	function testDiscussionFind() {
		$this->Discussion->recursive = -1;
		$results = $this->Discussion->find('first');
		$this->assertTrue(!empty($results));

		$expected = array(
			'Discussion' => array(
				'id'  => 1,
				'table_id'  => 1,
				'table_type' => 'group',
				'type' => 'root',
				'author_id'  => 1,
				'title'  => 'Test Root',
				'created'  => $results['Discussion']['created'],
				'modified'  => $results['Discussion']['modified'],
				'content'  => null,
				'parent_id'  => null,
				'lft'  => $results['Discussion']['lft'],
				'rght'  => $results['Discussion']['rght'],
			),
		);
		$this->assertEqual($results, $expected);
	}
	
	function testToNode() {
		$this->Discussion->recursive = 1;
		$results = $this->Discussion->find('first');
		$node = $this->Discussion->toNode($results);

		$expected = array(
			'id'  => 1,
			'table_id'  => 1,
			'table_type' => 'group',
			'title'  => 'Test Root',
			'created'  => $node['created'],
			'modified'  => $node['modified'],
			'lastpost_time' => $node['lastpost_time'],
			'lastpost_author' => 'Test User',
			'lastpost_author_id' => 1,
			'author' => 'Test User',
			'author_id'  => 1,
			'content'  => null,
			'posts' => 0,
			'category' => null,
			'parent_id'  => null,
			'text' => 'Test Root',
			'leaf' => false,
		);

		$this->assertEqual($node, $expected);
	}
	
	function testToNodeNull() {
		try
		{
			$node = $this->Discussion->toNode(null);
			$this->fail('InvalidArgumentException was expected');
		}
		catch (InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testToNodeNotArray() {
		try
		{
			$node = $this->Discussion->toNode('string');
			$this->fail('InvalidArgumentException was expected');
		}
		catch (InvalidArgumentException $e)
		{
			$this->pass();
		}	
	}

	function testToNodeMissingModel() {
		try
		{
			$node = $this->Discussion->toNode(array('id' => 1));
			$this->fail('InvalidArgumentException was expected');
		}
		catch (InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testToNodeMissingKey() {
		try
		{
			$node = $this->Discussion->toNode(array('Discussion' => array('test' => 1)));
			$this->fail('InvalidArgumentException was expected');
		}
		catch (InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testRoot()
	{
		$table_type = 'group';
		$table_id = 1;

		$node = $this->Discussion->root($table_type, $table_id);

		$expected = array(
			'Discussion' => array(
				'id' => 1,
				'table_id' => 1,
				'table_type' => 'group',
				'type' => 'root',
				'author_id' => 1,
				'title' => 'Test Root',
				'created' => $node['Discussion']['created'],
				'modified' => $node['Discussion']['modified'],
				'content' => null,
				'parent_id' => null,
				'lft' => $node['Discussion']['lft'],
				'rght' => $node['Discussion']['rght'],
			),
		);

		$this->assertEqual($node, $expected);
	}

	function testRootNullTableType()
	{
		$table_type = null;
		$table_id = 1;

		try
		{
			$node = $this->Discussion->root($table_type, $table_id);
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testRootInvalidTableType()
	{
		$table_type = 'invalid';
		$table_id = 1;

		try
		{
			$node = $this->Discussion->root($table_type, $table_id);
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testRootNullTableId()
	{
		$table_type = 'group';
		$table_id = null;

		try
		{
			$node = $this->Discussion->root($table_type, $table_id);
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testRootInvalidTableId()
	{
		$table_type = 'group';
		$table_id = 'invalid';

		try
		{
			$node = $this->Discussion->root($table_type, $table_id);
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testCategories()
	{
		$table_type = 'group';
		$table_id = 1;

		$results = $this->Discussion->categories($table_type, $table_id);

		$expected = array(
			array(
				'Discussion' => array(
					'id' => 2,
					'table_id' => 1,
					'table_type' => 'group',
					'type' => 'category',
					'author_id' => 1,
					'title' => 'Test Category',
					'created' => $results[0]['Discussion']['created'],
					'modified' => $results[0]['Discussion']['modified'],
					'content' => 'Test Category Description',
					'parent_id' => 1,
					'lft' => $results[0]['Discussion']['lft'],
					'rght' => $results[0]['Discussion']['rght'],
				),
			),
		);

		$this->assertEqual($results, $expected);
	}

	function testCategoriesNullTableType()
	{
		$table_type = null;
		$table_id = 1;

		try
		{
			$node = $this->Discussion->categories($table_type, $table_id);
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testCategoriesInvalidTableType()
	{
		$table_type = 'invalid';
		$table_id = 1;

		try
		{
			$node = $this->Discussion->categories($table_type, $table_id);
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testCategoriesNullTableId()
	{
		$table_type = 'group';
		$table_id = null;

		try
		{
			$node = $this->Discussion->categories($table_type, $table_id);
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testCategoriesInvalidTableId()
	{
		$table_type = 'group';
		$table_id = 'invalid';

		try
		{
			$node = $this->Discussion->categories($table_type, $table_id);
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testTopics()
	{
		$table_type = 'group';
		$table_id = 1;
		$category_id = 2;

		$results = $this->Discussion->topics($table_type, $table_id, $category_id);

		$expected = array(
			array(
				'Discussion' => array(
					'id' => 3,
					'table_id' => 1,
					'table_type' => 'group',
					'type' => 'topic',
					'author_id' => 1,
					'title' => 'Test Topic',
					'created' => $results[0]['Discussion']['created'],
					'modified' => $results[0]['Discussion']['modified'],
					'content' => 'Test Topic Description',
					'parent_id' => 2,
					'lft' => $results[0]['Discussion']['lft'],
					'rght' => $results[0]['Discussion']['rght'],
				),
			),
		);

		$this->assertEqual($results, $expected);
	}

	function testTopicsNullTableType()
	{
		$table_type = null;
		$table_id = 1;
		$category_id = 2;

		try
		{
			$node = $this->Discussion->topics($table_type, $table_id, $category_id);
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testTopicsInvalidTableType()
	{
		$table_type = 'invalid';
		$table_id = 1;
		$category_id = 2;

		try
		{
			$node = $this->Discussion->topics($table_type, $table_id, $category_id);
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testTopicsNullTableId()
	{
		$table_type = 'group';
		$table_id = null;
		$category_id = 2;

		try
		{
			$node = $this->Discussion->topics($table_type, $table_id, $category_id);
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testTopicsInvalidTableId()
	{
		$table_type = 'group';
		$table_id = 'invalid';
		$category_id = 2;

		try
		{
			$node = $this->Discussion->topics($table_type, $table_id, $category_id);
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testTopicsNullCategoryId()
	{
		$table_type = 'group';
		$table_id = 1;
		$category_id = null;

		try
		{
			$node = $this->Discussion->topics($table_type, $table_id, $category_id);
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testTopicsInvalidCategoryId()
	{
		$table_type = 'group';
		$table_id = 1;
		$category_id = 'invalid';

		try
		{
			$node = $this->Discussion->topics($table_type, $table_id, $category_id);
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testPosts()
	{
		$table_type = 'group';
		$table_id = 1;
		$topic_id = 3;
		$recursive = -1;

		$results = $this->Discussion->posts($table_type, $table_id, $topic_id, $recursive);

		$expected = array(
			array(
				'Discussion' => array(
					'id' => 4,
					'table_id' => 1,
					'table_type' => 'group',
					'type' => 'post',
					'author_id' => 1,
					'title' => 'Test Post',
					'created' => $results[0]['Discussion']['created'],
					'modified' => $results[0]['Discussion']['modified'],
					'content' => 'Test Post Description',
					'parent_id' => 3,
					'lft' => $results[0]['Discussion']['lft'],
					'rght' => $results[0]['Discussion']['rght'],
				),
			),
		);

		$this->assertEqual($results, $expected);
	}

	function testPostsNullTableType()
	{
		$table_type = null;
		$table_id = 1;
		$topic_id = 3;

		try
		{
			$node = $this->Discussion->posts($table_type, $table_id, $topic_id);
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testPostsInvalidTableType()
	{
		$table_type = 'invalid';
		$table_id = 1;
		$topic_id = 3;

		try
		{
			$node = $this->Discussion->posts($table_type, $table_id, $topic_id);
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testPostsNullTableId()
	{
		$table_type = 'group';
		$table_id = null;
		$topic_id = 3;

		try
		{
			$node = $this->Discussion->posts($table_type, $table_id, $topic_id);
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testPostsInvalidTableId()
	{
		$table_type = 'group';
		$table_id = 'invalid';
		$topic_id = 3;

		try
		{
			$node = $this->Discussion->posts($table_type, $table_id, $topic_id);
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testPostsNullCategoryId()
	{
		$table_type = 'group';
		$table_id = 1;
		$topic_id = null;

		try
		{
			$node = $this->Discussion->posts($table_type, $table_id, $topic_id);
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testPostsInvalidCategoryId()
	{
		$table_type = 'group';
		$table_id = 1;
		$topic_id = 'invalid';

		try
		{
			$node = $this->Discussion->posts($table_type, $table_id, $topic_id);
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

}
?>
