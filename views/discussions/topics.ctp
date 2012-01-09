<?php
	$type = Inflector::pluralize(Inflector::humanize($discussion['Discussion']['table_type']));
	$controller = Inflector::pluralize($discussion['Discussion']['table_type']);
	if(isset($group_id) && !empty($group_id) && $discussion['Discussion']['table_type'] == 'project')
	{
		$html->addCrumb('Groups', '/groups/index'); 
		$html->addCrumb($group_name, '/groups/dashboard/' . $group_id);
	}
	
	$html->addCrumb($type, '/' . $controller . '/index');
	$html->addCrumb($name, '/' . $controller . '/dashboard/' . $discussion['Discussion']['table_id']); 
	$html->addCrumb('Discussions', '/discussions/' . $discussion['Discussion']['table_type'] . '/' . $discussion['Discussion']['table_id']);
	$html->addCrumb($discussion['Discussion']['title'], '/discussions/topics/' . $discussion['Discussion']['id']); 
?>
<div id="discussions-div"></div> 
<script type="text/javascript">
	laboratree.context = <?php echo $javascript->object($context); ?>;
	laboratree.discussions.makeTopics('<?php echo addslashes($name) . ' - ' . addslashes($discussion['Discussion']['title']) . ' - Topics'; ?>', 'discussions-div', '<?php echo $html->url('/discussions/topics/' . $discussion['Discussion']['id'] . '.json'); ?>','<?php echo $discussion['Discussion']['table_type']; ?>','<?php echo $discussion['Discussion']['table_id']; ?>','<?php echo $discussion['Discussion']['id']; ?>');
</script>
