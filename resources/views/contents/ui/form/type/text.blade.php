<?php
$label = $ui->getLabel();
$wrapperAttributes = $ui->renderHtmlAttributes($ui->wrapperAttributes());
$labelAttributes = $ui->renderHtmlAttributes($ui->labelAttributes());
$inputAttributes = $ui->renderHtmlAttributes($ui->inputAttributes());
?>
<div <?php echo $wrapperAttributes ?>>
    <label <?php echo $labelAttributes ?>><?php echo $label ?></label>
    <input <?php echo $inputAttributes ?> />
	{!! view(zbase_view_file_contents('ui.form.helpblock'), compact('ui')) !!}
</div>