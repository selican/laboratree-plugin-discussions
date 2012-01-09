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
	$html->addCrumb($category['Discussion']['title'], '/discussions/topics/' . $category['Discussion']['id']);
	$html->addCrumb($discussion['Discussion']['title'], '/discussions/view/' . $discussion['Discussion']['id']); 
?>
<div id="discussion-view-div"></div>
<script type="text/javascript">
	laboratree.context = <?php echo $javascript->object($context); ?>;	
	laboratree.discussions.makeView('discussion-view-div', '<?php echo addslashes($discussion['Discussion']['title']); ?>', '<?php echo $discussion['Discussion']['id']; ?>', '<?php echo $html->url('/discussions/view/' . $discussion['Discussion']['id'] . '.json'); ?>');
</script>
