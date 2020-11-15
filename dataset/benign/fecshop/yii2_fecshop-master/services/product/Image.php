<?php

/*
 * FecShop file.
 *
 * @link http://www.fecshop.com/
 * @copyright Copyright (c) 2016 FecShop Software LLC
 * @license http://www.fecshop.com/license/
 */

namespace fecshop\services\product;

use fecshop\services\Service;
use Yii;
use yii\base\InvalidValueException;

/**
 * Product Image Services
 * @author Terry Zhao <2358269014@qq.com>
 * @since 1.0
 */
class Image extends Service
{
    /**
     * absolute image save floder.
     */
    public $imageFloder = 'media/catalog/product';

    /**
     * upload image max size.
     */
    public $maxUploadMSize;

    /**
     * allow image type.
     */
    public $allowImgType = [
        'image/jpeg',
        'image/gif',
        'image/png',
        'image/jpg',
        'image/pjpeg',
    ];
    /**
     * // https://github.com/liip-forks/Imagine/blob/b3705657f1e4513c6351d3aabc4f9efb7f415803/lib/Imagine/Imagick/Image.php#L703
     * png图片resize压缩的质量，范围为  0-9，数越大，质量越高，图片文件的容量越大
     */
    public $pngCompressionLevel = 8;
    /**
      * https://github.com/liip-forks/Imagine/blob/b3705657f1e4513c6351d3aabc4f9efb7f415803/lib/Imagine/Imagick/Image.php#L676   
      * https://secure.php.net/manual/zh/imagick.setimagecompressionquality.php
      * 'jpeg', 'jpg', 'pjpeg' 格式图片进行压缩的质量数，范围：1-100，数越大，质量越高，图片文件的容量越大
      */
    public $jpegQuality = 80;
                
    // 默认产品图片，当产品图片找不到的时候，就会使用该默认图片
    public $defaultImg = '/default.jpg';

    // 产品水印图片。
    public $waterImg   = 'product_water.jpg';

    protected $_defaultImg;

    protected $_md5WaterImgPath;
    
    public function init()
    {
        parent::init();
        // init by store config
        $this->imageFloder = Yii::$app->store->get('product','imageFloder');
        $this->maxUploadMSize = (float)Yii::$app->store->get('product','maxUploadMSize');
        $this->pngCompressionLevel = (int)Yii::$app->store->get('product','pngCompressionLevel');
        $this->jpegQuality = (int)Yii::$app->store->get('product','jpegCompressionLevel');
    }
    /**
     * 得到保存产品图片所在相对根目录的url路径.
     */
    public function getBaseUrl()
    {
        return Yii::$service->image->GetImgUrl($this->imageFloder, 'common');
    }

    /**
     * 得到保存产品图片所在相对根目录的文件夹路径.
     */
    public function getBaseDir()
    {
        return Yii::$service->image->GetImgDir($this->imageFloder, 'common');
    }

    /**
     * 通过产品图片的相对路径得到产品图片的url.
     */
    public function getUrl($str)
    {
        return Yii::$service->image->GetImgUrl($this->imageFloder.$str, 'common');
    }

    /**
     * 通过产品图片的相对路径得到产品图片的绝对路径.
     */
    public function getDir($str)
    {
        return Yii::$service->image->GetImgDir($this->imageFloder.$str, 'common');
    }

    /**
     * @param $param_img_file | Array .
     * upload image from web page , you can get image from $_FILE['XXX'] ,
     * $param_img_file is get from $_FILE['XXX'].
     * return , if success ,return image saved relative file path , like '/b/i/big.jpg'
     * if fail, reutrn false;
     */
    public function saveProductUploadImg($FILE)
    {
        Yii::$service->image->imageFloder = $this->imageFloder;
        Yii::$service->image->allowImgType = $this->allowImgType;
        if ($this->maxUploadMSize) {
            Yii::$service->image->setMaxUploadSize($this->maxUploadMSize);
        }

        return Yii::$service->image->saveUploadImg($FILE);
    }

    /**
     * 获取产品默认图片的完整URL
     */
    public function defautImg()
    {
        if (!$this->_defaultImg) {
            $this->_defaultImg = $this->getUrl($this->defaultImg);
        }

        return $this->_defaultImg;
    }

    /**
     * @param $imageVal | String ，图片相对路径字符串。
     * @param $imgResize | Array or Int ， 数组 [230,230] 代表生成的图片为230*230，如果宽度或者高度不够，则会用白色填充
     *                  如果 $imgResize设置为 230， 则宽度不变，高度按照原始图的比例计算出来。
     * @param $isWatered | Boolean ， 产品图片是否打水印。
     * 获取相应尺寸的产品图片。
     */
    public function getResize($imageVal, $imgResize, $isWatered = false)
    {
        list($newPath, $newUrl) = $this->getNewPathAndUrl($imageVal, $imgResize, $isWatered);
        
        return $newUrl;
    }

    /**
     * 和上面的方法 getResize 功能类似, getResize是得到按照图片尺寸resize后的图片的url。
     * 本函数是得到resize后图片的 完整文件路径 （绝对文件地址）
     */
    public function getResizeDir($imageVal, $imgResize, $isWatered = false)
    {
        list($newPath, $newUrl) = $this->getNewPathAndUrl($imageVal, $imgResize, $isWatered);
        
        return $newPath;
    }

    /**
     * @param $imageVal | String ，图片相对路径字符串。
     * @param $imgResize | Array or Int ， 数组 [230,230] 代表生成的图片为230*230，如果宽度或者高度不够，则会用白色填充
     *                  如果 $imgResize设置为 230， 则宽度不变，高度按照原始图的比例计算出来。
     * @param $isWatered | Boolean ， 产品图片是否打水印。
     * 获取相应尺寸的产品图片。
     */
    public function getNewPathAndUrl($imageVal, $imgResize, $isWatered = false)
    {
        $originImgPath = $this->getDir($imageVal);
        if (!file_exists($originImgPath)) {
            $originImgPath = $this->getDir($this->defaultImg);
        }
        $waterImgPath = '';
        if ($isWatered) {
            $waterImgPath = $this->getDir('/'.$this->waterImg);
        }
        list($newPath, $newUrl) = $this->getProductNewPath($imageVal, $imgResize, $waterImgPath);
        if ($newPath && $newUrl) {
            if (!file_exists($newPath)) {
                $options = [
                    'png_compression_level' => $this->pngCompressionLevel,   
                    'jpeg_quality'  => $this->jpegQuality,
                ];
                \fec\helpers\CImage::saveResizeMiddleWaterImg($originImgPath, $newPath, $imgResize, $waterImgPath, $options);
            }

            return [$newPath, $newUrl];
        }
    }

    /**
     * @param $imageVal | String ，图片相对路径字符串。
     * @param $imgResize | Array or Int ， 数组 [230,230] 代表生成的图片为230*230，如果宽度或者高度不够，则会用白色填充
     *                  如果 $imgResize设置为 230， 则宽度不变，高度按照原始图的比例计算出来。
     * @param $waterImgPath | String ， 水印图片的路径
     * 获取按照自定义尺寸获取的产品图片的文件绝对路径和完整url
     */
    protected function getProductNewPath($imageVal, $imgResize, $waterImgPath)
    {
        if (!$this->_md5WaterImgPath) {
            if (!$waterImgPath) {
                $waterImgPath = 'defaultWaterPath';
            }
            //echo $waterImgPath;exit;
            $this->_md5WaterImgPath = md5($waterImgPath);
        }
        $baseDir = '/cache/'.$this->_md5WaterImgPath;
        if (is_array($imgResize)) {
            list($width, $height) = $imgResize;
        } else {
            $width = $imgResize;
            $height = '0';
        }
        $imageArr = explode('/', $imageVal);
        $dirArr = ['cache', $this->_md5WaterImgPath, $width, $height];
        foreach ($imageArr as $igf) {
            if ($igf !== '' && !strstr($igf, '.')) {
                $dirArr[] = $igf;
            }
        }
        $createDir = \fec\helpers\CDir::createFloder($this->getBaseDir(), $dirArr);
        if ($createDir) {
            $newPath = $this->getBaseDir().$baseDir .'/'.$width.'/'.$height.$imageVal;
            $newUrl = $this->getBaseUrl().$baseDir .'/'.$width.'/'.$height.$imageVal;
            
            return [$newPath, $newUrl];
        } else {
            
            return [];
        }
    }
}
