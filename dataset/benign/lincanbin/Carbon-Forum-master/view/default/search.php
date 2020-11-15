<?php
if (!defined('InternalAccess')) exit('error: 403 Access Denied');
//关键字加亮
function KeywordHighlight($Content)
{
	global $KeywordArray;
	if ($KeywordArray) {
		$KeywordHighlightArray = array();
		foreach ($KeywordArray as $Value) {
			$KeywordHighlightArray[] = '<span class="search-keyword">' . $Value . '</span>';
		}
		return str_ireplace($KeywordArray, $KeywordHighlightArray, $Content);
	} else {
		return $Content;
	}
}
?>
<!-- main-content start -->
<div class="main-content">
	<div class="title">
		<a href="<?php echo $Config['WebsitePath']; ?>/">
			<?php echo $Config['SiteName']; ?>
		</a>
		&raquo; <?php echo $Lang['Search']; ?> &raquo; <?php echo $Keyword; ?>
	</div>
	<div class="main-box home-box-list">
		<?php if( $Error) {?>
		    <div class="mess-error"> <?php echo $Error;?> </div>
		<?php } ?>
		<?php
		foreach ($TopicsArray as $Topic) {
		?>
		<div class="post-list">
			<div class="item-avatar">
				<a href="<?php echo $Config['WebsitePath']; ?>/u/<?php echo urlencode($Topic['UserName']); ?>" target="_blank">
						<?php echo GetAvatar($Topic['UserID'], $Topic['UserName'], 'middle'); ?>
					</a>
			</div>
			<div class="item-content">
				<h2>
<?php
if (empty($Topic['PostID'])):
?>
					<a href="<?php echo $Config['WebsitePath']; ?>/t/<?php echo $Topic['ID']; ?>">	
<?php
else:
?>
					<a href="/goto/<?php echo $Topic['ID']; ?>-<?php echo $Topic['PostID']; ?>#Post<?php echo $Topic['PostID']; ?>">
<?php
endif
?>
						<?php echo KeywordHighlight($Topic['Topic']); ?>
					</a>
				</h2>
                <?php if( isset($Topic['MinContent']) ) { ?>
                <div class="topic-dec">
                    <?php echo $PostsSearch ? KeywordHighlight($Topic['MinContent']) : $Topic['MinContent']; ?>
                </div>
                <?php } ?>
				<span class="item-tags">
					<?php
					if($Topic['Tags']){
						foreach (explode("|", $Topic['Tags']) as $Tag) {
						?><a href="<?php echo $Config['WebsitePath']; ?>/tag/<?php echo urlencode($Tag); ?>"><?php echo KeywordHighlight($Tag); ?></a>
						<?php
						}
					}
					?>
				</span>
				<span class="item-date float-right">
					<a href="<?php echo $Config['WebsitePath']; ?>/u/<?php echo urlencode($Topic['UserName']); ?>"><?php echo $Topic['UserName']; ?></a>&nbsp;•&nbsp;
					<?php echo FormatTime($Topic['LastTime']); 
					if($Topic['Replies']){
					?>&nbsp;•&nbsp;<?php echo $Lang['Last_Reply_From']; ?>&nbsp;<a href="<?php echo $Config['WebsitePath']; ?>/u/<?php echo urlencode($Topic['LastName']); ?>"><?php echo $Topic['LastName']; ?></a>
					<?php } ?>
				</span>
			</div>
		<?php if($Topic['Replies']){ ?>
			<div class="item-count">
				<a href="<?php echo $Config['WebsitePath']; ?>/t/<?php echo $Topic['ID']; ?>" target="_blank"><?php echo $Topic['Replies']; ?></a>
			</div>
		<?php } ?>
			<div class="c"></div>
		</div>
		<?php
		}
		?>
		<div class="pagination">
			<?php PaginationSimplified('/search/'.$Keyword.'/page/', $Page, $IsLastPage); ?>
			<div class="c"></div>
		</div>
	</div>
</div>
<!-- main-content end -->
<!-- main-sider start -->
<div class="main-sider">
	<?php
	include($TemplatePath.'sider.php');
	?>
</div>
<!-- main-sider end -->
