<?php
if (!defined('InternalAccess')) exit('error: 403 Access Denied');
?>
<ul class="list topic-list">
<?php
if($Page>1){
?>
	<li class="pagination"><a href="<?php echo $Config['WebsitePath']; ?>/page/<?php echo ($Page-1); ?>" data-transition="slide"><?php echo $Lang['Page_Previous']; ?></a></li>
<?php
}
foreach ($TopicsArray as $Topic) {
?>
	<li>
		<div class="avatar">
			<a href="<?php echo $Config['WebsitePath']; ?>/u/<?php echo urlencode($Topic['UserName']); ?>" data-transition="slide">
					<?php echo GetAvatar($Topic['UserID'], $Topic['UserName'], 'middle'); ?>
			</a>
		</div>
		<div class="content">
		<a href="<?php echo $Config['WebsitePath']; ?>/t/<?php echo $Topic['ID']; ?>" data-transition="slide"<?php echo ($Topic['Replies']<$Config['PostsPerPage'])?' data-refresh="true"':''; ?>>
			<h2><?php echo $Topic['Topic']; ?></h2>
		</a>
		<p><?php echo FormatTime($Topic['LastTime']); ?>&nbsp;&nbsp;<?php echo $Topic['LastName']; ?>
		</p>
<?php
if($Topic['Replies']){
?>
		<span class="aside">
			<?php echo $Topic['Replies']; ?>
		</span>
<?php
}
?>
		</div>
		
		<div class="c"></div>
	</li>
<?php
} 
if($Page<$TotalPage){
?>
	<li class="pagination"><a href="<?php echo $Config['WebsitePath']; ?>/page/<?php echo ($Page+1); ?>" data-transition="slide" data-refresh="true"><?php echo $Lang['Page_Next']; ?></a></li>
<?php
}
?>
</ul>