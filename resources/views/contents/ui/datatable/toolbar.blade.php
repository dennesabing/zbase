<?php
$rows = $ui->getRows();
$actionCreateButton = $ui->getActionCreateButton()->setAttribute('size', 'default');
?>
<div role="toolbar" class="btn-toolbar pull-left">
	<?php echo zbase_view_render(zbase_view_file_contents('ui.datatable.pagination'), ['paginator' => $rows]); ?>
</div>
<div class="btn-toolbar pull-right" role="toolbar" aria-label="Buttons">
	<?php if(!empty($actionCreateButton)): ?>
		<?php echo $actionCreateButton ?>
	<?php endif; ?>
</div>