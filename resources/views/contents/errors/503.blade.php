<?php
$page = [];
$page['title'] = 'Service unavailable.';
$page['headTitle'] = 'Service unavailable';
zbase_view_page_details(['page' => $page]);
?>
<div class="container text-center" id="error">
  <svg height="100" width="100">
    <polygon points="50,25 17,80 82,80" stroke-linejoin="round" style="fill:none;stroke:#ff8a00;stroke-width:8" />
    <text x="42" y="74" fill="#ff8a00" font-family="sans-serif" font-weight="900" font-size="42px">!</text>
  </svg>
 <div class="row">
    <div class="col-md-12">
      <div class="main-icon text-warning"><span class="uxicon uxicon-alert"></span></div>
     <h1>503 Service unavailable</h1>
    </div>
  </div>
  <div class="row">
    <div class="col-md-6 col-md-push-3">
		<p class="lead">
		</p>
    </div>
  </div>
</div>