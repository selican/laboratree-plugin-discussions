<?php
App::import('Controller','Discussions');
App::import('Component', 'RequestHandler');

Mock::generatePartial('RequestHandlerComponent', 'DiscussionsControllerMockRequestHandlerComponent', array('prefers'));

class DiscussionsControllerTestDiscussionsController extends DiscussionsController {
	var $name = 'Discussions';
	var $autoRender = false;

	var $redirectUrl = null;
	var $renderedAction = null;
	var $error = null;
	var $stopped = null;
	
	function redirect($url, $status = null, $exit = true)
	{
		$this->redirectUrl = $url;
	}
	function render($action = null, $layout = null, $file = null)
	{
		$this->renderedAction = $action;
	}

	function cakeError($method, $messages = array())
	{
		if(!isset($this->error))
		{
			$this->error = $method;
		}
	}
	function _stop($status = 0)
	{
		$this->stopped = $status;
	}
}

class DiscussionsControllerTest extends CakeTestCase {
	var $Discussions = null;
	var $fixtures = array('app.helps', 'app.app_category', 'app.app_data', 'app.application', 'app.app_module', 'app.attachment', 'app.discussion', 'app.digest', 'app.doc', 'app.docs_permission', 'app.docs_tag', 'app.docs_type_data', 'app.docs_type_field', 'app.docs_type', 'app.docs_type_row', 'app.docs_version', 'app.group', 'app.groups_address', 'app.groups_association', 'app.groups_award', 'app.groups_interest', 'app.groups_phone', 'app.groups_projects', 'app.groups_publication', 'app.groups_setting', 'app.groups_url', 'app.groups_users', 'app.inbox', 'app.inbox_hash', 'app.interest', 'app.message_archive', 'app.message', 'app.note', 'app.ontology_concept', 'app.preference', 'app.project', 'app.projects_association', 'app.projects_interest', 'app.projects_setting', 'app.projects_url', 'app.projects_users', 'app.role', 'app.setting', 'app.site_role', 'app.tag', 'app.type', 'app.url', 'app.user', 'app.users_address', 'app.users_association', 'app.users_award', 'app.users_education', 'app.users_interest', 'app.users_job', 'app.users_phone', 'app.users_preference', 'app.users_publication', 'app.users_url', 'app.ldap_user');
	
	function startTest() {
		$this->Discussions = new DiscussionsControllerTestDiscussionsController();
		$this->Discussions->constructClasses();
		$this->Discussions->Component->initialize($this->Discussions);
		
		$this->Discussions->Session->write('Auth.User', array(
			'id' => 1,
			'username' => 'testuser',
			'changepass' => 0,
		));
	}
	
	function testDiscussionsControllerInstance() {
		$this->assertTrue(is_a($this->Discussions, 'DiscussionsController'));
	}

	function testAdminFixtree() {
//		$this->Discussions->admin_fixtree();
	}

	function testIndex()
	{
		$table_type = 'group';
		$table_id = 1;
	
		$this->Discussions->params = Router::parse('discussions/index/' . $table_type . '/' . $table_id);
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->index($table_type, $table_id);
		
		$this->assertEqual($this->Discussions->redirectUrl, '/discussions/' . $table_type . '/' . $table_id);
	}

	function testIndexNullTableType()
	{
		$table_type = null;
		$table_id = 1;

		$this->Discussions->params = Router::parse('discussions/index/' . $table_type . '/' . $table_id);
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->index($table_type, $table_id);
		
		$this->assertEqual($this->Discussions->error, 'missing_field');
	}

	function testIndexInvalidTableType()
	{
		$table_type = 'invalid';
		$table_id = 1;

		$this->Discussions->params = Router::parse('discussions/index/' . $table_type . '/' . $table_id);
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->index($table_type, $table_id);
		
		$this->assertEqual($this->Discussions->error, 'invalid_field');
	}

	function testIndexNullTableId()
	{
		$table_type = 'group';
		$table_id = null;


		$this->Discussions->params = Router::parse('discussions/index/' . $table_type . '/' . $table_id);
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->index($table_type, $table_id);
		
		$this->assertEqual($this->Discussions->error, 'missing_field');
	}

	function testIndexInvalidTableId()
	{
		$table_type = 'group';
		$table_id = 'invalid';


		$this->Discussions->params = Router::parse('discussions/index/' . $table_type . '/' . $table_id);
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->index($table_type, $table_id);
		
		$this->assertEqual($this->Discussions->error, 'invalid_field');
	}

	function testUserEmptyUserId() {
                $this->Discussions->Session->write('Auth.User', array(
                        'id' => 1,
                        'username' => 'testuser',
                        'changepass' => 0,
                ));

		$this->Discussions->_user(null);
	}

	function testUserEmptyUser() {
		$this->Discussions->_user(100000);
		$this->assertEqual($this->Discussions->error, 'invalid_field');
	}

	function testUserUserPrivacyPrivate() {
		$this->Discussions->_user(1);
	}

	function testUserUserNotPrivate() {
}
	function testUserPrefersJson() {
		$this->Discussions->RequestHandler = new DiscussionsControllerMockRequestHandlerComponent();
		$this->Discussions->RequestHandler->setReturnValue('prefers', true);
		$this->Discussions->params['form']['limit'] = 20;
		$this->Discussions->params['form']['start'] = 1;	
		
                $this->Discussions->_user(1);
	}

	function testGroup()
	{
		$group_id = 1;
	
		$this->Discussions->params = Router::parse('discussions/group/' . $group_id . '.json');
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->RequestHandler = new DiscussionsControllerMockRequestHandlerComponent();

		$this->Discussions->RequestHandler->setReturnValue('prefers', true);
		$this->Discussions->group($group_id);

		$this->assertTrue(isset($this->Discussions->viewVars['response']));
		$response = $this->Discussions->viewVars['response'];
		$this->assertTrue($response['success']);

		$expected = array(
			array(
				'id'  => 2,
				'table_id'  => 1,
				'table_type' => 'group',
				'title'  => 'Test Category',
				'created'  => '12/20/2010 2:54pm',
				'modified'  => '12/20/2010 2:54pm',
				'lastpost_time' => '12/20/2010 2:54pm',
				'lastpost_author' => 'Test User',
				'lastpost_author_id' => 1,
				'author' => 'Test User',
				'author_id'  => 1,
				'content'  => 'Test Category Description',
				'posts' => 1,
				'category' => 'Test Root',
				'parent_id'  => 1,
				'text'  => 'Test Category',
				'leaf'	=> null,		
				'role'  => 'group.manager',
			),
		);

		$this->assertEqual($response['discussions'], $expected);
	}

	function testGroupAccessDenied()
	{
		
		try {
			$group_id = 1;

			$this->Discussions->Session->write('Auth.User', array(
				'id' => 9000,
				'username' => 'invaliduser',
				'changepass' => 0,
			));

			$this->Discussions->params = Router::parse('discussions/group/' . $group_id . '.json');
			$this->Discussions->beforeFilter();
			$this->Discussions->Component->startup($this->Discussions);

			$this->Discussions->RequestHandler = new DiscussionsControllerMockRequestHandlerComponent();

			$this->Discussions->RequestHandler->setReturnValue('prefers', true);
			$this->Discussions->group($group_id);

			$this->assertEqual($this->Discussions->error, 'access_denied');
		}

		catch (InvalidArgumentException $e) {
			//$this->assertEqual($this->error, 'Invalid user id');
			$this->pass();
		}
	}

	function testGroupNullGroupId()
	{
		$group_id = null;

		$this->Discussions->params = Router::parse('discussions/group/' . $group_id);
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->group($group_id);
		
		$this->assertEqual($this->Discussions->error, 'missing_field');
	}

	function testGroupInvalidGroupId()
	{
		$group_id = 'invalid';

		$this->Discussions->params = Router::parse('discussions/group/' . $group_id);
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->group($group_id);
		
		$this->assertEqual($this->Discussions->error, 'invalid_field');
	}

	function testProject()
	{
		$project_id = 1;
	
		$this->Discussions->params = Router::parse('discussions/project/' . $project_id . '.json');
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->RequestHandler = new DiscussionsControllerMockRequestHandlerComponent();

		$this->Discussions->RequestHandler->setReturnValue('prefers', true);
		$this->Discussions->project($project_id);

		$this->assertTrue(isset($this->Discussions->viewVars['response']));
		$response = $this->Discussions->viewVars['response'];
		$this->assertTrue($response['success']);

		$expected = array(
			array(
				'id' => $response['discussions'][0]['id'],
				'table_id'  => 1,
				'table_type' => 'project',
				'title'  => 'Test Category',
				'created'  => $response['discussions'][0]['created'],
				'modified'  => $response['discussions'][0]['modified'],
				'lastpost_time' => $response['discussions'][0]['lastpost_time'],
				'lastpost_author' => 'Test User',
				'lastpost_author_id' => 1,
				'author' => 'Test User',
				'author_id'  => 1,
				'content'  => 'Test Category Description',
				'posts' => 1,
				'category' => 'Test Root',
				'parent_id'  => $response['discussions'][0]['parent_id'],
				'text' => 'Test Category',
				'leaf' => null,				
				'role'  => 'project.manager',
			),
		);

		$this->assertEqual($response['discussions'], $expected);
	}

	function testProjectAccessDenied()
	{
		try {
			$project_id = 1;

			$this->Discussions->Session->write('Auth.User', array(
				'id' => 9000,
				'username' => 'invaliduser',
				'changepass' => 0,
			));

			$this->Discussions->params = Router::parse('discussions/project/' . $project_id . '.json');
			$this->Discussions->beforeFilter();
			$this->Discussions->Component->startup($this->Discussions);
	
			$this->Discussions->RequestHandler = new DiscussionsControllerMockRequestHandlerComponent();
			$this->Discussions->RequestHandler->setReturnValue('prefers', true);
			$this->Discussions->project($project_id);
	
			$this->assertEqual($this->Discussions->error, 'access_denied');
		}

		catch(InvalidArgumentException $e) {
			$this->pass();
		}
	}

	function testProjectNullProjectId()
	{
		$project_id = null;

		$this->Discussions->params = Router::parse('discussions/project/' . $project_id);
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->project($project_id);
		
		$this->assertEqual($this->Discussions->error, 'missing_field');
	}

	function testProjectInvalidProjectId()
	{
		$project_id = 'invalid';

		$this->Discussions->params = Router::parse('discussions/project/' . $project_id);
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->project($project_id);
		
		$this->assertEqual($this->Discussions->error, 'invalid_field');
	}
/*
	function testView()
	{
		$discussion_id = 3;

		$this->Discussions->params = Router::parse('discussions/view/' . $discussion_id . '.json');
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->RequestHandler = new DiscussionsControllerMockRequestHandlerComponent();
		$this->Discussions->RequestHandler->setReturnValue('prefers', true);

		$this->Discussions->view($discussion_id);

		$this->assertTrue(isset($this->Discussions->viewVars['node']));
		$node = $this->Discussions->viewVars['node'];
		$expected = array(
			array(
				'Discussion' => array(
					'id'  => $node[0]['Discussion']['id'],
					'table_id'  => 1,
					'table_type' => 'group',
					'type' => 'topic',
					'author_id'  => 1,
					'title'  => 'Test Topic',
					'created'  => $node[0]['Discussion']['created'],
					'modified'  => $node[0]['Discussion']['modified'],
					'content'  => 'Test Topic Description',
					'parent_id'  => $node[0]['Discussion']['parent_id'],
					'lft'  => $node[0]['Discussion']['lft'],
					'rght'  => $node[0]['Discussion']['rght'],
				),
				'User' => array(
					'id'  => $node[0]['User']['id'],
					'username'  => 'testuser',
					'password'  => $node[0]['User']['password'],
					'email'  => 'testuser@example.com',
					'alt_email'  => 'testtest@example.com',
					'prefix'  => 'Mr.',
					'first_name'  => 'Test',
					'last_name'  => 'User',
					'name'  => 'Test User',
					'suffix'  => 'Esq.',
					'title'  => 'Programmer',
					'description'  => 'test',
					'status'  => 'Test',
					'gender' => 'male',
					'age'  => 50,
					'picture'  => NULL,
					'privacy' => 'private',
					'activity'  => $node[0]['User']['activity'],
					'registered'  => $node[0]['User']['registered'],
					'hash'  => $node[0]['User']['hash'],
					'private_hash'  => 'hash',
					'auth_token'  => 'AAAAA',
					'auth_timestamp'  => 1269625040,
					'confirmed'  => 1,
					'changepass'  => 0,
					'security_question'  => 1,
					'security_answer'  => 'hash',
					'language_id'  => 1,
					'timezone_id'  => 1,
					'ip'  => '127.0.0.1',
					'admin'  => 0,
					'type' => 'user',
					'vivo'  => NULL 
				),
				'Group' => array(
					'id'  => $node[0]['Group']['id'],
					'name'  => 'Private Test Group',
					'email'  => 'testgrp+private@example.com',
					'description'  => 'Test Group',
					'privacy' => 'private',
					'picture'  => null,
					'created'  => $node[0]['Group']['created'],
				),
				'Project' => array(
					'id'  => $node[0]['Project']['id'],
					'name'  => 'Private Test Project',
					'description'  => 'Private Test Project',
					'privacy' => 'private',
					'picture'  => NULL,
					'email'  => 'testprj+private@example.com',
					'created'  => $node[0]['Project']['created'],
				),
				'Author' => array(
					'id'  => $node[0]['Author']['id'],
					'username'  => 'testuser',
					'password'  => $node[0]['Author']['password'],
					'email'  => 'testuser@example.com',
					'alt_email'  => 'testtest@example.com',
					'prefix'  => 'Mr.',
					'first_name'  => 'Test',
					'last_name'  => 'User',
					'name'  => 'Test User',
					'suffix'  => 'Esq.',
					'title'  => 'Programmer',
					'description'  => 'test',
					'status'  => 'Test',
					'gender' => 'male',
					'age'  => 50,
					'picture'  => NULL,
					'privacy' => 'private',
					'activity'  => $node[0]['Author']['activity'],
					'registered'  => $node[0]['Author']['registered'],
					'hash'  => $node[0]['Author']['hash'],
					'private_hash'  => 'hash',
					'auth_token'  => 'AAAAA',
					'auth_timestamp'  => 1269625040,
					'confirmed'  => 1,
					'changepass'  => 0,
					'security_question'  => 1,
					'security_answer'  => 'hash',
					'language_id'  => 1,
					'timezone_id'  => 1,
					'ip'  => '127.0.0.1',
					'admin'  => 0,
					'type' => 'user',
					'vivo'  => NULL 
				),
				'Category' => array(
					'id'  => $node[0]['Category']['id'],
					'table_id'  => 1,
					'table_type' => 'group',
					'type' => 'category',
					'author_id'  => 1,
					'title'  => 'Test Category',
					'created'  => $node[0]['Category']['created'],
					'modified' => $node[0]['Category']['modified'],
					'content'  => 'Test Category Description',
					'parent_id' => $node[0]['Category']['parent_id'],
					'lft'  => $node[0]['Category']['lft'],
					'rght'  => $node[0]['Category']['rght'],
				),
			),
		);

		$this->assertFalse(empty($node));

		$this->assertTrue(isset($node[0]['children']));
		$expected[0]['children'] = $node[0]['children'];

		$this->assertTrue(isset($node[0]['User']));
		$expected[0]['User']['activity'] = $node[0]['User']['activity'];
		$expected[0]['User']['ip'] = $node[0]['User']['ip'];

		$this->assertTrue(isset($node[0]['Author']));
		$expected[0]['Author']['activity'] = $node[0]['Author']['activity'];
		$expected[0]['Author']['ip'] = $node[0]['Author']['ip'];

		$this->assertEqual($expected, $node);
	}
*/
	function testViewNullDiscussionId()
	{
		$discussion_id = null;

		$this->Discussions->params = Router::parse('discussions/view/' . $discussion_id . '.json');
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->view($discussion_id);

		$this->assertEqual($this->Discussions->error, 'missing_field');
	}

	function testViewInvalidDiscussionId()
	{
		$discussion_id = 'invalid';

		$this->Discussions->params = Router::parse('discussions/view/' . $discussion_id . '.json');
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->view($discussion_id);

		$this->assertEqual($this->Discussions->error, 'invalid_field');
	}

	function testViewAccessDenied()
	{
		try {
			$discussion_id = 3;

			$this->Discussions->Session->write('Auth.User', array(
				'id' => 9000,
				'username' => 'invaliduser',
				'changepass' => 0,
			));

			$this->Discussions->params = Router::parse('discussions/view/' . $discussion_id . '.json');
			$this->Discussions->beforeFilter();
			$this->Discussions->Component->startup($this->Discussions);
	
			$this->Discussions->view($discussion_id);
	
			$this->assertEqual($this->Discussions->error, 'access_denied');
		}

		catch(InvalidArgumentException $e) {
			$this->pass();
		}
	}

	function testViewAddPost()
	{
		$discussion_id = 3;

		$this->Discussions->data = array(
			'Discussion' => array(
				'content' => 'Test',
			),
		);

		$this->Discussions->params = Router::parse('discussions/view/' . $discussion_id . '.json');
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->RequestHandler = new DiscussionsControllerMockRequestHandlerComponent();
		$this->Discussions->RequestHandler->setReturnValue('prefers', true);

		$this->Discussions->view($discussion_id);
/*
		$this->assertTrue(isset($this->Discussions->viewVars['node']));
		$node = $this->Discussions->viewVars['node'];
		$this->assertTrue(isset($node['discussion']));

		$expected = array(
			'success' => true,
			'discussion' => array(
				'Discussion' => array(
					'id'  => $node['discussion']['Discussion']['id'],
					'table_id'  => 1,
					'table_type' => 'group',
					'type' => 'post',
					'author_id'  => 1,
					'title'  => 'RE: Test Topic',
					'created'  => $node['discussion']['Discussion']['created'],
					'modified'  => $node['discussion']['Discussion']['modified'],
					'content'  => 'Test',
					'parent_id'  => 3,
					'lft'  => $node['discussion']['Discussion']['lft'],
					'rght'  => $node['discussion']['Discussion']['rght']
				),
				'User' => array(
					'id'  => $node['discussion']['User']['id'],
					'username'  => 'testuser',
					'password'  => $node['discussion']['User']['password'],
					'email'  => 'testuser@example.com',
					'alt_email'  => 'testtest@example.com',
					'prefix'  => 'Mr.',
					'first_name'  => 'Test',
					'last_name'  => 'User',
					'name'  => 'Test User',
					'suffix'  => 'Esq.',
					'title'  => 'Programmer',
					'description'  => 'test',
					'status'  => 'Test',
					'gender' => 'male',
					'age'  => 50,
					'picture'  => NULL,
					'privacy' => 'private',
					'activity'  => $node['discussion']['User']['activity'],
					'registered'  => $node['discussion']['User']['registered'],
					'hash'  => $node['discussion']['User']['hash'],
					'private_hash'  => 'hash',
					'auth_token'  => 'AAAAA',
					'auth_timestamp'  => 1269625040,
					'confirmed'  => 1,
					'changepass'  => 0,
					'security_question'  => 1,
					'security_answer'  => 'hash',
					'language_id'  => 1,
					'timezone_id'  => 1,
					'ip'  => $node['discussion']['User']['ip'],
					'admin'  => 0,
					'type' => 'user',
					'vivo'  => NULL 
				),
				'Group' => array(
					'id'  => $node['discussion']['Group']['id'],
					'name'  => 'Private Test Group',
					'email'  => 'testgrp+private@example.com',
					'description'  => 'Test Group',
					'privacy' => 'private',
					'picture'  => null,
					'created'  => $node['discussion']['Group']['created'],
				),
				'Project' => array(
					'id'  => $node['discussion']['Project']['id'],
					'name'  => 'Private Test Project',
					'description'  => 'Private Test Project',
					'privacy' => 'private',
					'picture'  => NULL,
					'email'  => 'testprj+private@example.com',
					'created'  => $node['discussion']['Project']['created'],
				),
				'Author' => array(
					'id'  => $node['discussion']['Author']['id'],
					'username'  => 'testuser',
					'password'  => $node['discussion']['Author']['password'],
					'email'  => 'testuser@example.com',
					'alt_email'  => 'testtest@example.com',
					'prefix'  => 'Mr.',
					'first_name'  => 'Test',
					'last_name'  => 'User',
					'name'  => 'Test User',
					'suffix'  => 'Esq.',
					'title'  => 'Programmer',
					'description'  => 'test',
					'status'  => 'Test',
					'gender' => 'male',
					'age'  => 50,
					'picture'  => NULL,
					'privacy' => 'private',
					'activity'  => $node['discussion']['Author']['activity'],
					'registered'  => $node['discussion']['Author']['registered'],
					'hash'  => $node['discussion']['Author']['hash'],
					'private_hash'  => 'hash',
					'auth_token'  => 'AAAAA',
					'auth_timestamp'  => 1269625040,
					'confirmed'  => 1,
					'changepass'  => 0,
					'security_question'  => 1,
					'security_answer'  => 'hash',
					'language_id'  => 1,
					'timezone_id'  => 1,
					'ip'  => $node['discussion']['Author']['ip'],
					'admin'  => 0,
					'type' => 'user',
					'vivo'  => NULL 
				),
				'Category' => array(
					'id'  => $node['discussion']['Category']['id'],
					'table_id'  => 1,
					'table_type' => 'group',
					'type' => 'topic',
					'author_id'  => 1,
					'title'  => 'Test Topic',
					'created'  => $node['discussion']['Category']['created'],
					'modified' => $node['discussion']['Category']['modified'],
					'content'  => 'Test Topic Description',
					'parent_id'  => $node['discussion']['Category']['parent_id'],
					'lft'  => $node['discussion']['Category']['lft'],
					'rght'  => $node['discussion']['Category']['rght'] 
				),
			),
		);
		$this->assertEqual($node, $expected);
*/	}

	function testViewAddPostNull()
	{
		$discussion_id = 7;

		$this->Discussions->data = array(
			'Discussion' => array(
				'content' => null,
			),
		);

		$this->Discussions->params = Router::parse('discussions/view/' . $discussion_id . '.json');
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->RequestHandler = new DiscussionsControllerMockRequestHandlerComponent();
		$this->Discussions->RequestHandler->setReturnValue('prefers', true);

		$this->Discussions->view($discussion_id);
/*
		$this->assertTrue(isset($this->Discussions->viewVars['node']));
		$node = $this->Discussions->viewVars['node'];

		$expected = array(
			'success' => false,
		);
		$this->assertEqual($node, $expected);
*/	}

	function testAddCategory()
	{
		$table_type = 'group';
		$table_id = 1;
		$parent_id = 1;

		$this->Discussions->data = array(
			'Discussion' => array(
				'title' => 'A Unique Test Title',
				'content' => 'Test content',
			),
		);

		$this->Discussions->params = Router::parse('discussions/add/' . $table_type . '/' . $table_id . '/' . $parent_id);
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->add($table_type, $table_id, $parent_id);
	
		$conditions = array(
			'Discussion.table_type' => $table_type,
			'Discussion.table_id' => $table_id,
			'Discussion.parent_id' => $parent_id,
			'Discussion.title' => 'A Unique Test Title',
		);
		$this->Discussions->Discussion->recursive = -1;
		$result = $this->Discussions->Discussion->find('first', array('conditions' => $conditions));

		$expected = array(
			'Discussion' => array(
				'id' => $result['Discussion']['id'],
				'table_id' => 1,
				'table_type' => 'group',
				'type' => 'category',
				'author_id' => 1,
				'title' => 'A Unique Test Title',
				'created' => $result['Discussion']['created'],
				'modified' => $result['Discussion']['modified'],
				'content' => 'Test content',
				'parent_id' => 1,
				'lft' => 16,
				'rght' => 17,
			),
		);

		$this->assertEqual($result, $expected);
	}

	function testAddTopic()
	{
		$table_type = 'group';
		$table_id = 1;
		$parent_id = 2;

		$this->Discussions->data = array(
			'Discussion' => array(
				'title' => 'A Unique Test Title',
				'content' => 'Test content',
			),
		);

		$this->Discussions->params = Router::parse('discussions/add/' . $table_type . '/' . $table_id . '/' . $parent_id);
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->add($table_type, $table_id, $parent_id);
	
		$conditions = array(
			'Discussion.table_type' => $table_type,
			'Discussion.table_id' => $table_id,
			'Discussion.parent_id' => $parent_id,
			'Discussion.title' => 'A Unique Test Title',
		);
		$this->Discussions->Discussion->recursive = -1;
		$result = $this->Discussions->Discussion->find('first', array('conditions' => $conditions));

		$expected = array(
			'Discussion' => array(
				'id' => $result['Discussion']['id'],
				'table_id' => 1,
				'table_type' => 'group',
				'type' => 'topic',
				'author_id' => 1,
				'title' => 'A Unique Test Title',
				'created' => $result['Discussion']['created'],
				'modified' => $result['Discussion']['modified'],
				'content' => 'Test content',
				'parent_id' => 2,
				'lft' => 15,
				'rght' => 16,
			),
		);

		$this->assertEqual($result, $expected);
	}

	function testAddNullTableType()
	{
		$table_type = null;
		$table_id = 1;
		$parent_id = 2;

		$this->Discussions->params = Router::parse('discussions/add/' . $table_type . '/' . $table_id . '/' . $parent_id);
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->add($table_type, $table_id, $parent_id);

		$this->assertEqual($this->Discussions->error, 'missing_field');
	}

	function testAddInvalidTableType()
	{
		$table_type = 'invalid';
		$table_id = 1;
		$parent_id = 2;

		$this->Discussions->params = Router::parse('discussions/add/' . $table_type . '/' . $table_id . '/' . $parent_id);
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->add($table_type, $table_id, $parent_id);

		$this->assertEqual($this->Discussions->error, 'invalid_field');
	}

	function testAddNullTableId()
	{
		$table_type = 'group';
		$table_id = null;
		$parent_id = 2;

		$this->Discussions->params = Router::parse('discussions/add/' . $table_type . '/' . $table_id . '/' . $parent_id);
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->add($table_type, $table_id, $parent_id);

		$this->assertEqual($this->Discussions->error, 'missing_field');
	}

	function testAddInvalidTableId()
	{
		$table_type = 'group';
		$table_id = 'invalid';
		$parent_id = 2;

		$this->Discussions->params = Router::parse('discussions/add/' . $table_type . '/' . $table_id . '/' . $parent_id);
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->add($table_type, $table_id, $parent_id);

		$this->assertEqual($this->Discussions->error, 'invalid_field');
	}

	function testAddNullParentId()
	{
		$table_type = 'group';
		$table_id = 1;
		$parent_id = null;

		$this->Discussions->data = array(
			'Discussion' => array(
				'title' => 'A Unique Test Title',
				'content' => 'Test content',
			),
		);

		$this->Discussions->params = Router::parse('discussions/add/' . $table_type . '/' . $table_id . '/' . $parent_id);
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->add($table_type, $table_id, $parent_id);
	
		$conditions = array(
			'Discussion.table_type' => $table_type,
			'Discussion.table_id' => $table_id,
			'Discussion.title' => 'A Unique Test Title',
		);
		$this->Discussions->Discussion->recursive = -1;
		$result = $this->Discussions->Discussion->find('first', array('conditions' => $conditions));

		$expected = array(
			'Discussion' => array(
				'id' => $result['Discussion']['id'],
				'table_id' => $table_id,
				'table_type' => $table_type,
				'type' => 'category',
				'author_id' => $result['Discussion']['author_id'],
				'title' => 'A Unique Test Title',
				'created' => $result['Discussion']['created'],
				'modified' => $result['Discussion']['modified'],
				'content' => 'Test content',
				'parent_id' => 1,
				'lft' => 16,
				'rght' => 17,
			),
		);

		$this->assertEqual($result, $expected);

	}

	function testAddInvalidParentId()
	{
		$table_type = 'group';
		$table_id = 1;
		$parent_id = 'invalid';

		$this->Discussions->params = Router::parse('discussions/add/' . $table_type . '/' . $table_id . '/' . $parent_id);
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->add($table_type, $table_id, $parent_id);

		$this->assertEqual($this->Discussions->error, 'invalid_field');
	}

	function testAddInvalidParentIdBadType()
	{
		$table_type = 'group';
		$table_id = 1;
		$parent_id = 4;

		$this->Discussions->params = Router::parse('discussions/add/' . $table_type . '/' . $table_id . '/' . $parent_id);
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->add($table_type, $table_id, $parent_id);

		$this->assertEqual($this->Discussions->error, 'invalid_field');
	}

	function testAddBadData()
	{
		$table_type = 'group';
		$table_id = 1;
		$parent_id = 1;

		$this->Discussions->data = array(
			'test' => 'invalid'
		);

		$this->Discussions->params = Router::parse('discussions/add/' . $table_type . '/' . $table_id . '/' . $parent_id);
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->add($table_type, $table_id, $parent_id);

		$this->assertEqual($this->Discussions->error, 'invalid_field');
	}

	function testAddInvalidData()
	{
		$table_type = 'group';
		$table_id = 1;
		$parent_id = 1;

		$this->Discussions->data = array(
			'Discussion' => array(
				'title' => null,
				'content' => 'Invalid Data',
			),
		);

		$this->Discussions->params = Router::parse('discussions/add/' . $table_type . '/' . $table_id . '/' . $parent_id);
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->add($table_type, $table_id, $parent_id);

		$this->assertTrue(empty($this->Discussions->redirectUrl));
		$this->assertTrue(empty($this->Discussions->error));
	}

	function testAddAccessDenied()
	{
		$table_type = 'group';
		$table_id = 2;
		$parent_id = null;

		$this->Discussions->params = Router::parse('discussions/add/' . $table_type . '/' . $table_id . '/' . $parent_id);
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->add($table_type, $table_id, $parent_id);

		$this->assertEqual($this->Discussions->error, 'access_denied');
	}

	function testAddAccessDeniedParentId()
	{
		$table_type = 'group';
		$table_id = 1;
		$parent_id = 9;

		$this->Discussions->params = Router::parse('discussions/add/' . $table_type . '/' . $table_id . '/' . $parent_id);
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->add($table_type, $table_id, $parent_id);

		$this->assertEqual($this->Discussions->error, 'access_denied');

	}

	function testEditCategory()
	{
		$discussion_id = 2;

		$this->Discussions->data = array(
			'Discussion' => array(
				'title' => 'Test Category',
				'content' => 'Test Category Description',
			),
		);

		$this->Discussions->params = Router::parse('discussions/edit/' . $discussion_id);
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->edit($discussion_id);
	
		$conditions = array(
			'Discussion.id' => $discussion_id,
		);
		$this->Discussions->Discussion->recursive = -1;
		$result = $this->Discussions->Discussion->find('first', array('conditions' => $conditions));

		$expected = array(
			'Discussion' => array(
				'id' => $result['Discussion']['id'],
				'table_id' => 1,
				'table_type' => 'group',
				'type' => 'category',
				'author_id' => 1,
				'title' => 'Test Category',
				'created' => $result['Discussion']['created'],
				'modified' => $result['Discussion']['modified'],
				'content' => 'Test Category Description',
				'parent_id' => 1,
				'lft' => $result['Discussion']['lft'],
				'rght' => $result['Discussion']['rght'],
			),
		);

		$this->assertEqual($result, $expected);

		$this->assertEqual($this->Discussions->redirectUrl, '/discussions/topics/' . $discussion_id);
	}

	function testEditTopic()
	{
		$discussion_id = 3;

		$this->Discussions->data = array(
			'Discussion' => array(
				'title' => 'Edited Topic Test Title',
				'content' => 'Edited topic test content',
			),
		);

		$this->Discussions->params = Router::parse('discussions/edit/' . $discussion_id);
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->edit($discussion_id);
	
		$conditions = array(
			'Discussion.id' => $discussion_id,
		);
		$this->Discussions->Discussion->recursive = -1;
		$result = $this->Discussions->Discussion->find('first', array('conditions' => $conditions));

		$expected = array(
			'Discussion' => array(
				'id' => $result['Discussion']['id'],
				'table_id' => 1,
				'table_type' => 'group',
				'type' => 'topic',
				'author_id' => 1,
				'title' => 'Edited Topic Test Title',
				'created' => $result['Discussion']['created'],
				'modified' => $result['Discussion']['modified'],
				'content' => 'Edited topic test content',
				'parent_id' => 2,
				'lft' => $result['Discussion']['lft'],
				'rght' => $result['Discussion']['rght'],
			),
		);

		$this->assertEqual($result, $expected);

		$this->assertEqual($this->Discussions->redirectUrl, '/discussions/view/' . $discussion_id);
	}

	function testEditPost()
	{
		$discussion_id = 4;

		$this->Discussions->data = array(
			'Discussion' => array(
				'title' => 'Edited Post Test Title',
				'content' => 'Edited post test content',
			),
		);

		$this->Discussions->params = Router::parse('discussions/edit/' . $discussion_id);
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->edit($discussion_id);
	
		$conditions = array(
			'Discussion.id' => $discussion_id,
		);
		$this->Discussions->Discussion->recursive = -1;
		$result = $this->Discussions->Discussion->find('first', array('conditions' => $conditions));

		$expected = array(
			'Discussion' => array(
				'id' => $result['Discussion']['id'],
				'table_id' => 1,
				'table_type' => 'group',
				'type' => 'post',
				'author_id' => 1,
				'title' => 'Test Post',
				'created' => $result['Discussion']['created'],
				'modified' => $result['Discussion']['modified'],
				'content' => 'Test Post Description',
				'parent_id' => 3,
				'lft' => $result['Discussion']['lft'],
				'rght' => $result['Discussion']['rght'],
			),
		);

		$this->assertEqual($result, $expected);

		$this->assertEqual($this->Discussions->redirectUrl, '/discussions/view/' . $result['Discussion']['parent_id'] . '#discussion-' . $discussion_id);
	}

	function testEditNullDiscussionId()
	{
		$discussion_id = null;

		$this->Discussions->params = Router::parse('discussions/edit/' . $discussion_id);
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->edit($discussion_id);

		$this->assertEqual($this->Discussions->error, 'missing_field');
	}

	function testEditInvalidDiscussionId()
	{
		$discussion_id = 'invalid';

		$this->Discussions->params = Router::parse('discussions/edit/' . $discussion_id);
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->edit($discussion_id);

		$this->assertEqual($this->Discussions->error, 'invalid_field');
	}

	function testEditInvalidDiscussionNotFound()
	{
		$discussion_id = 9000;

		$this->Discussions->params = Router::parse('discussions/edit/' . $discussion_id);
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->edit($discussion_id);

		$this->assertEqual($this->Discussions->error, 'invalid_field');
	}

	function testEditAccessDenied()
	{
		$discussion_id = 9;

		$this->Discussions->params = Router::parse('discussions/edit/' . $discussion_id);
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->edit($discussion_id);

		$this->assertEqual($this->Discussions->error, 'access_denied');
	}

	function testEditAuthor()
	{
		/**
		 * This needs to be a discussion that was authored by a user
		 * who is not a manager of that group.
		 */
		$discussion_id = 8;

		$this->Discussions->params = Router::parse('discussions/edit/' . $discussion_id);
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->edit($discussion_id);

		$this->assertNotEqual($this->Discussions->error, 'access_denied');
	}

	function testEditJson()
	{
		$discussion_id = 4;
	
		$this->Discussions->params = Router::parse('discussions/edit/' . $discussion_id . '.json');
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->RequestHandler = new DiscussionsControllerMockRequestHandlerComponent();

		$this->Discussions->RequestHandler->setReturnValue('prefers', true);
		$this->Discussions->edit($discussion_id);

		$this->assertTrue(isset($this->Discussions->viewVars['node']));
		$node = $this->Discussions->viewVars['node'];

		$expected = array(
			'id'  => 4,
			'table_id'  => 1,
			'table_type' => 'group',
			'title'  => 'Test Post',
			'created'  => '12/20/2010 2:54pm',
			'modified'  => '12/20/2010 2:54pm',
			'lastpost_time' => '12/20/2010 2:54pm',
			'lastpost_author' => 'Test User',
			'lastpost_author_id' => 1,
			'author' => 'Test User',
			'author_id'  => 1,
			'content'  => 'Test Post Description',
			'posts' => 0,
			'category' => 'Test Topic',
			'text' => 'Test Post',
			'leaf' => true,
			'parent_id'  => 3,
		);

		$this->assertEqual($node, $expected);
	}

	function testEditBadData()
	{
		$discussion_id = 2;

		$this->Discussions->data = array(
			'test' => 'bad',
		);

		$this->Discussions->params = Router::parse('discussions/edit/' . $discussion_id);
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->edit($discussion_id);

		$this->assertEqual($this->Discussions->error, 'invalid_field');
	}

	function testEditInvalidData()
	{
		$discussion_id = 2;

		$this->Discussions->data = array(
			'Discussion' => array(
				'title' => null,
				'content' => 'Bad Data',
			),
		);

		$this->Discussions->params = Router::parse('discussions/edit/' . $discussion_id);
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->edit($discussion_id);

		// TODO: Find a more reliable way to test if we are being passed to render()
		$this->assertTrue(empty($this->Discussions->redirectUrl));
		$this->assertTrue(empty($this->Discussions->error));
	}

	function testDelete()
	{
		$discussion_id = 3;

		$this->Discussions->params = Router::parse('discussions/delete/' . $discussion_id . '.json');
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->RequestHandler = new DiscussionsControllerMockRequestHandlerComponent();
		$this->Discussions->RequestHandler->setReturnValue('prefers', true);

		$this->Discussions->delete($discussion_id);
	}

	function testDeleteNotJson()
	{
		$discussion_id = 3;

		$this->Discussions->params = Router::parse('discussions/delete/' . $discussion_id);
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->RequestHandler = new DiscussionsControllerMockRequestHandlerComponent();
		$this->Discussions->RequestHandler->setReturnValue('prefers', false);

		$this->Discussions->delete($discussion_id);

		$this->assertEqual($this->Discussions->error, 'error404');
	}

	function testDeleteNullDiscussionId()
	{
		$discussion_id = null;

		$this->Discussions->params = Router::parse('discussions/delete/' . $discussion_id . '.json');
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->RequestHandler = new DiscussionsControllerMockRequestHandlerComponent();
		$this->Discussions->RequestHandler->setReturnValue('prefers', true);

		$this->Discussions->delete($discussion_id);

		$this->assertEqual($this->Discussions->error, 'missing_field');
	}

	function testDeleteInvalidDiscussionId()
	{
		$discussion_id = 'invalid';

		$this->Discussions->params = Router::parse('discussions/delete/' . $discussion_id . '.json');
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->RequestHandler = new DiscussionsControllerMockRequestHandlerComponent();
		$this->Discussions->RequestHandler->setReturnValue('prefers', true);

		$this->Discussions->delete($discussion_id);

		$this->assertEqual($this->Discussions->error, 'invalid_field');
	}

	function testDeleteInvalidDiscussionIdNotFound()
	{
		$discussion_id = 9000;

		$this->Discussions->params = Router::parse('discussions/delete/' . $discussion_id . '.json');
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->RequestHandler = new DiscussionsControllerMockRequestHandlerComponent();
		$this->Discussions->RequestHandler->setReturnValue('prefers', true);

		$this->Discussions->delete($discussion_id);

		$this->assertEqual($this->Discussions->error, 'invalid_field');
	}

	function testDeleteAccessDenied()
	{
		$discussion_id = 9;

		$this->Discussions->params = Router::parse('discussions/delete/' . $discussion_id . '.json');
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->RequestHandler = new DiscussionsControllerMockRequestHandlerComponent();
		$this->Discussions->RequestHandler->setReturnValue('prefers', true);

		$this->Discussions->delete($discussion_id);

		$this->assertEqual($this->Discussions->error, 'access_denied');
	}

	function testTopicsJson()
	{
		$discussion_id = 2;
	
		$this->Discussions->params = Router::parse('discussions/topics/' . $discussion_id . '.json');
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->RequestHandler = new DiscussionsControllerMockRequestHandlerComponent();

		$this->Discussions->RequestHandler->setReturnValue('prefers', true);
		$this->Discussions->topics($discussion_id);
		
		$this->assertTrue(isset($this->Discussions->viewVars['response']));
		$response = $this->Discussions->viewVars['response'];
		$this->assertTrue($response['success']);

		$expected =  array(
			array(
				'id' => 3,
				'table_id' => 1,
				'table_type' => 'group',
				'title' => 'Test Topic',
				'created' => '12/20/2010 2:54pm',
				'modified' => '12/20/2010 2:54pm',
				'lastpost_time' => '12/20/2010 2:54pm',
				'lastpost_author' => 'Test User',
				'lastpost_author_id' => 1,
				'author' => 'Test User',
				'author_id' => 1,
				'content' => 'Test Topic Description',
				'posts' => 1,
				'category' => 'Test Category',
				'parent_id' => 2,
				'text' => 'Test Topic',
				'leaf' => true,	
				'role' => 'group.manager',
			),
		);

		$this->assertEqual($response['discussions'], $expected);
	}

	function testTopicsNullDiscussionID()
	{
		$discussion_id = null;
	
		$this->Discussions->params = Router::parse('discussions/topics/' . $discussion_id);
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->topics($discussion_id);
		
		$this->assertEqual($this->Discussions->error, 'missing_field');
	}

	function testTopicsInvalidDiscussionID()
	{
		$discussion_id = 'invalid';
	
		$this->Discussions->params = Router::parse('discussions/topics/' . $discussion_id);
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->topics($discussion_id);
		
		$this->assertEqual($this->Discussions->error, 'invalid_field');
	}

	function testTopicsInvalidDiscussionIDNotFound()
	{
		$discussion_id = 9000;
	
		$this->Discussions->params = Router::parse('discussions/topics/' . $discussion_id);
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->topics($discussion_id);
		
		$this->assertEqual($this->Discussions->error, 'invalid_field');
	}

	function testTopicsInvalidDiscussionType()
	{
		$discussion_id = 12;
	
		$this->Discussions->params = Router::parse('discussions/topics/' . $discussion_id);
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->topics($discussion_id);
		
		$this->assertEqual($this->Discussions->error, 'invalid_field');
	}

	function testTopicsAccessDenied()
	{
		$discussion_id = 11;
	
		$this->Discussions->params = Router::parse('discussions/topics/' . $discussion_id);
		$this->Discussions->beforeFilter();
		$this->Discussions->Component->startup($this->Discussions);

		$this->Discussions->topics($discussion_id);
		
		$this->assertEqual($this->Discussions->error, 'access_denied');
	}

	function endTest() {
		unset($this->Discussions);
		ClassRegistry::flush();	
	}
}
?>
