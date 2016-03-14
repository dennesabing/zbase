<?php
$wrapperAttributes = $ui->renderHtmlAttributes($ui->wrapperAttributes());
?>
<div class="btn-group" role="group" aria-label="...">
	<button type="button" class="btn btn-default">1</button>
	<button type="button" class="btn btn-default">2</button>

	<div class="btn-group" role="group">
		<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
			Dropdown
			<span class="caret"></span>
		</button>
		<ul class="dropdown-menu">
			<li><a href="#">Dropdown link</a></li>
			<li><a href="#">Dropdown link</a></li>
		</ul>
	</div>
</div>