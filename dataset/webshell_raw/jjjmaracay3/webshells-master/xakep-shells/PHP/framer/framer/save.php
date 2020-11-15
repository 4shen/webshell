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
		
//папка в которой будет размещен архив
$archive_dir = 'archive/';
//папка с исходными файлами
$src_dir = 'archive/';
 
//создание zip архива
$zip = new ZipArchive();
//имя файла архива
$fileName = $key.".zip";
if ($zip->open($src_dir.$fileName, ZIPARCHIVE::CREATE) !== true) {
    fwrite(STDERR, "Error while creating archive file");
    exit(1);
}
 
//добавляем файлы в архив все файлы из папки src_dir

$file = $key.".txt";
$zip->addFile($src_dir.$file, $file);

//закрываем архив
$zip->close();
echo '<a href="archive/'.$key.'.zip">Download</a>&nbsp | &nbsp<a href="archive/'.$key.'.txt" target="_blank">View</a>';
//удаляем исходный файл
//unlink("archive/".$key.".txt");	
		
		
		
		
		
		
		 }

?>