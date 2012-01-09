<?php 
	$type = Inflector::pluralize(Inflector::humanize($table_type));
	$controller = Inflector::pluralize($table_type);
	if(isset($group_id) && !empty($group_id) && $table_type == 'project')
	{
		$html->addCrumb('Groups', '/groups/index'); 
		$html->addCrumb($group_name, '/groups/dashboard/' . $group_id);
		$html->addCrumb($type, '/' . $controller . '/group/' . $group_id);
	}
	else
	{
		$html->addCrumb($type, '/' . $controller . '/index');
	}
	$html->addCrumb($name, '/' . $controller  . '/dashboard/' . $table_id); 
	$html->addCrumb('Discussions', '/discussions/' . $table_type . '/' . $table_id);
	if(isset($parent_id) && $parent['Discussion']['type'] != 'root')
	{
		$html->addCrumb($parent['Discussion']['title'], '/discussions/topics/' . $parent_id);
		$html->addCrumb('Add Discussion ' . $typename, '/discussions/add/' . $table_type . '/' . $table_id . '/' . $parent_id);
	}
	else
	{
		$html->addCrumb('Add Discussion ' . $typename, '/discussions/add/' . $table_type . '/' . $table_id);
	}
?>
<div id="addDiscussion-div"></div>
<script type="text/javascript">
	laboratree.discussions.makePost('addDiscussion-div','<?php echo $table_type; ?>','<?php echo $table_id; ?>','<?php echo $html->url('/discussions/add/' . $table_type . '/' . $table_id); ?>','<?php echo $typename; ?>'); 
</script>

