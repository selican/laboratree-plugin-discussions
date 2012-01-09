<?php 
	$html->addCrumb('Users', '/users/index');
	$html->addCrumb($user['User']['name'], '/users/dashboard/' . $user['User']['id']); 
	$html->addCrumb('Discussions', '/discussions/user/' . $user['User']['id']); 
?>
<div id="discussions-div"></div>
<script type="text/javascript">
	laboratree.context = <?php echo $javascript->object($context); ?>;
	laboratree.discussions.makeCategories('<?php echo $user['User']['name'] . ' - Discussion Categories'; ?>', 'discussions-div', '<?php echo $html->url('/discussions/user/' . $user['User']['id'] . '.json'); ?>');
</script>
