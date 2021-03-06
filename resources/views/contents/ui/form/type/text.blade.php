<?php
$label = $ui->getLabel();
$wrapperAttributes = $ui->renderHtmlAttributes($ui->wrapperAttributes());
$labelAttributes = $ui->renderHtmlAttributes($ui->labelAttributes());
$inputAttributes = $ui->renderHtmlAttributes($ui->inputAttributes());
$inputAppend = $ui->getInputAppend();
$inputPrepend = $ui->getInputPrepend();
$inputType = $ui->getType();
?>
<?php if($inputType != 'hidden'): ?>
	<div <?php echo $wrapperAttributes ?>>
		<?php if($label !== false):?>
			<label <?php echo $labelAttributes ?>><?php echo $label ?></label>
		<?php endif;?>
		<?php if(empty($inputAppend) && !empty($inputPrepend)): ?>
			<div class="input-prepend">
				<span class="add-on"><?php echo $inputPrepend ?></span>
				<input <?php echo $inputAttributes ?> />
			</div>
		<?php endif; ?>
		<?php if(!empty($inputAppend) && empty($inputPrepend)): ?>
			<div class="input-append">
				<input <?php echo $inputAttributes ?> />
				<span class="add-on"><?php echo $inputAppend ?></span>
			</div>
		<?php endif; ?>
		<?php if(empty($inputAppend) && empty($inputPrepend)): ?>
			<input <?php echo $inputAttributes ?> />
		<?php endif; ?>
		{!! view(zbase_view_file_contents('ui.form.helpblock'), compact('ui')) !!}
	</div>
<?php else: ?>
	<input <?php echo $inputAttributes ?> />
<?php endif; ?>