<?php /** @var $l \OCP\IL10N */ ?>
<div id='notification'></div>

<div id="emptycontent" class="hidden"></div>

<div class="nofilterresults hidden">
	<div class="icon-search"></div>
	<h2><?php p($l->t('No entries found in this folder')); ?></h2>
	<p></p>
</div>

<table id="filestable">
	<thead>
		<tr>
			<th id='headerName' class="hidden column-name">
				<div id="headerName-container">
					<a class="name sort columntitle" data-sort="name"><span><?php p($l->t('Name')); ?></span><span class="sort-indicator"></span></a>
				</div>
			</th>
			<th id="headerState" class="hidden column-sharestate">
				<a id="modified" class="columntitle" data-sort="sharestate"><span><?php p($l->t('State')); ?></span><span class="sort-indicator"></span></a>
			</th>
			<th id="headerDate" class="hidden column-mtime">
				<a id="modified" class="columntitle" data-sort="mtime"><span><?php p($l->t('Share time')); ?></span><span class="sort-indicator"></span></a>
			</th>
			<th class="hidden column-expiration">
				<a class="columntitle"><span><?php p($l->t('Expiration date')); ?></span></a>
			</th>
		</tr>
	</thead>
	<tbody id="fileList">
	</tbody>
	<tfoot>
	</tfoot>
</table>
