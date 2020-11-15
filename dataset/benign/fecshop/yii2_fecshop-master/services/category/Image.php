<?php

/*
 * FecShop file.
 *
 * @link http://www.fecshop.com/
 * @copyright Copyright (c) 2016 FecShop Software LLC
 * @license http://www.fecshop.com/license/
 */

namespace fecshop\services\category;

use fecshop\services\Service;
use Yii;

/**
 * 分类图片的一些处理。
 * @author Terry Zhao <2358269014@qq.com>
 * @since 1.0
 */
class Image extends Service
{
    /**
     * absolute image save floder.
     */
    public $imageFloder = 'media/catalog/category';

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
     * 得到保存分类图片所在相对根目录的url路径.
     */
    public function getBaseUrl()
    {
        return Yii::$service->image->GetImgUrl($this->imageFloder, 'common');
    }

    /**
     * 得到保存分类图片所在相对根目录的文件夹路径.
     */
    public function getBaseDir()
    {
        return Yii::$service->image->GetImgDir($this->imageFloder, 'common');
    }

    /**
     * 通过分类图片的相对路径得到产品图片的url.
     */
    public function getUrl($str)
    {
        return Yii::$service->image->GetImgUrl($this->imageFloder.$str, 'common');
    }

    /**
     * 通过分类图片的相对路径得到分类图片的绝对路径.
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
    public function saveCategoryUploadImg($FILE)
    {
        Yii::$service->image->imageFloder = $this->imageFloder;
        Yii::$service->image->allowImgType = $this->allowImgType;
        if ($this->maxUploadMSize) {
            Yii::$service->image->setMaxUploadSize($this->maxUploadMSize);
        }

        return Yii::$service->image->saveUploadImg($FILE);
    }
}
