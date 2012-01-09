<?php 
	$type = Inflector::pluralize(Inflector::humanize($discussion['Discussion']['table_type']));
	$controller = Inflector::pluralize($discussion['Discussion']['table_type']);
	if(isset($group_id) && !empty($group_id) && $discussion['Discussion']['table_type'] == 'project')
	{
		$html->addCrumb('Groups', '/groups/index'); 
		$html->addCrumb($group_name, '/groups/dashboard/' . $group_id);
		$html->addCrumb($type, '/' . $controller . '/group/' . $group_id);

	}
	else
	{	
		$html->addCrumb($type, '/' . $controller . '/index');
	}
	$html->addCrumb($name, '/' . $controller . '/dashboard/' . $discussion['Discussion']['table_id']); 
	$html->addCrumb('Discussions', '/discussions/' . $discussion['Discussion']['table_type'] . '/' . $discussion['Discussion']['table_id']);
	if($discussion['Discussion']['type'] == 'topic')
	{
		$html->addCrumb($discussion['Discussion']['title'], '/discussions/view/' . $discussion['Discussion']['id']); 
	}
	$html->addCrumb('Edit ' . $typename . ' ' . $discussion['Discussion']['title'], '/discussions/edit/' . $discussion['Discussion']['id']); 
?>
<div id="editDiscussion-div"></div>
<script type="text/javascript">
	laboratree.discussions.makeEditor('editDiscussion-div','<?php echo $discussion_id; ?>','<?php echo $html->url('/discussions/edit/' . $discussion_id . '.json'); ?>','<?php echo $typename; ?>'); 
</script>
