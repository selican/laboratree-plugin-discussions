<?php
	$html->addCrumb('Groups', '/groups/index'); 
	$html->addCrumb($group_name, '/groups/dashboard/' . $group_id);
	$html->addCrumb('Projects', '/projects/group/' . $group_id);
	$html->addCrumb($project['Project']['name'], '/projects/dashboard/' . $project['Project']['id']); 
	$html->addCrumb('Discussions', '/discussions/project/' . $project['Project']['id']); 
?>
<div id="discussions-div"></div>
<script type="text/javascript">
	laboratree.discussions.makeCategories('<?php echo $project['Project']['name'] . ' - Discussion Categories'; ?>', 'discussions-div', '<?php echo $html->url('/discussions/project/' . $project['Project']['id'] . '.json'); ?>','project','<?php echo $project['Project']['id']; ?>', '<?php echo $permission['mask']; ?>');
</script>
