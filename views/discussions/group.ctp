<?php 
	$html->addCrumb('Groups', '/groups/index');
	$html->addCrumb($group['Group']['name'], '/groups/dashboard/' . $group['Group']['id']); 
	$html->addCrumb('Discussions', '/discussions/group/' . $group['Group']['id']); 
?>
<div id="discussions-div"></div>
<script type="text/javascript">
	laboratree.context = <?php echo $javascript->object($context); ?>;
	laboratree.discussions.makeCategories('<?php echo addslashes($group['Group']['name']) . ' - Discussion Categories'; ?>', 'discussions-div', '<?php echo $html->url('/discussions/group/' . $group['Group']['id'] . '.json'); ?>','group','<?php echo $group['Group']['id']; ?>');
</script>
