<?php
include('init.php');
read_db();
$file=date("d.m.y");
if(($_POST['do']=="save") && isset($_POST['id']) && isset($_POST['key'])) {
if($_POST['key']=='null'){
   exit;
   }
   
$massive=explode(',',$_POST['id']);
		foreach($massive as $id) {
		    if($id=='on')continue;
		 
		    if(stristr($_POST['key'],'^')){
               $exp = explode('^',$_POST['key']);
               $key = $exp[0];
               $string = $database[$id]['url'];
               }
            else{
               $key = $_POST['key'];
               $string = $database[$id]['url'].'|'.$database[$id]['pass'];
               }
		 
		    file_put_contents("archive/".$key.".txt",$string."\r\n",FILE_APPEND);
		    }
		
//����� � ������� ����� �������� �����
$archive_dir = 'archive/';
//����� � ��������� �������
$src_dir = 'archive/';
 
//�������� zip ������
$zip = new ZipArchive();
//��� ����� ������
$fileName = $key.".zip";
if ($zip->open($src_dir.$fileName, ZIPARCHIVE::CREATE) !== true) {
    fwrite(STDERR, "Error while creating archive file");
    exit(1);
}
 
//��������� ����� � ����� ��� ����� �� ����� src_dir

$file = $key.".txt";
$zip->addFile($src_dir.$file, $file);

//��������� �����
$zip->close();
echo '<a href="archive/'.$key.'.zip">Download</a>&nbsp | &nbsp<a href="archive/'.$key.'.txt" target="_blank">View</a>';
//������� �������� ����
//unlink("archive/".$key.".txt");	
		
		
		
		
		
		
		 }

?>