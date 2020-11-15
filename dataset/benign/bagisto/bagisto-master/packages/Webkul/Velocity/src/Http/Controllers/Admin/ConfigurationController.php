<?php

namespace Webkul\Velocity\Http\Controllers\Admin;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Webkul\Velocity\Repositories\VelocityMetadataRepository;

class ConfigurationController extends Controller
{
    /**
     * VelocityMetadataRepository object
     *
     * @var \Webkul\Velocity\Repositories\VelocityMetadataRepository
     */
    protected $velocityMetaDataRepository;

    protected $locale;

    /**
     * Create a new controller instance.
     *
     * @param  \Webkul\Velocity\Repositories\MetadataRepository  $velocityMetaDataRepository
     * @return void
     */
    public function __construct (VelocityMetadataRepository $velocityMetadataRepository)
    {
        $this->_config = request('_config');

        $this->velocityHelper = app('Webkul\Velocity\Helpers\Helper');

        $this->velocityMetaDataRepository = $velocityMetadataRepository;

        $this->locale = request()->get('locale') ?: app()->getLocale();
    }

    /**
     * @return \Illuminate\View\View
     */
    public function renderMetaData()
    {
        $velocityMetaData = $this->velocityHelper->getVelocityMetaData($this->locale, false);

        if (! $velocityMetaData) {
            $this->createMetaData($this->locale);

            $velocityMetaData = $this->velocityHelper->getVelocityMetaData($this->locale);
        }

        $velocityMetaData->advertisement = $this->manageAddImages(json_decode($velocityMetaData->advertisement, true) ?: []);

        return view($this->_config['view'], [
            'metaData' => $velocityMetaData,
        ]);
    }

    /**
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function storeMetaData($id)
    {
        // check if radio button value
        if (request()->get('slides') == "on") {
            $params = request()->all() + [
                'slider' => 1,
            ];
        } else {
            $params = request()->all() + [
                'slider' => 0,
            ];
        }

        $velocityMetaData = $this->velocityMetaDataRepository->findOneWhere([
            'id' => $id,
        ]);

        $advertisement = json_decode($velocityMetaData->advertisement, true);

        $params['advertisement'] = [];

        if (isset($params['images'])) {
            foreach ($params['images'] as $index => $images) {
                $params['advertisement'][$index] =  $this->uploadAdvertisementImages($images, $index, $advertisement);
            }

            if ($advertisement) {
                foreach ($advertisement as $key => $image_array) {
                    if (! isset($params['images'][$key])) {
                        foreach ($advertisement[$key] as $image) {
                            Storage::delete($image);
                        }
                    }
                }
            }
        }

        if (isset($params['product_view_images'])) {
            foreach ($params['product_view_images'] as $index => $productViewImage) {
                if ($productViewImage !== "") {
                    $params['product_view_images'][$index] = $this->uploadImage($productViewImage, $index);
                }
            }

            $params['product_view_images'] = json_encode($params['product_view_images']);
        }

        $params['advertisement'] = json_encode($params['advertisement']);
        $params['home_page_content'] = str_replace('=&gt;', '=>', $params['home_page_content']);

        unset($params['images']);
        unset($params['slides']);

        $params['locale'] = $this->locale;
        
        // update row
        $product = $this->velocityMetaDataRepository->update($params, $id);

        session()->flash('success', trans('admin::app.response.update-success', ['name' => 'Velocity Theme']));

        return redirect()->route($this->_config['redirect'], ['locale' => $this->locale]);
    }

    /**
     * @param  array    $data
     * @param  int      $index
     * @param  array    $advertisement
     * 
     * @return array
     */
    public function uploadAdvertisementImages($data, $index, $advertisement)
    {
        $saveImage = [];

        $saveData = $advertisement;

        foreach ($data as $imageId => $image) {
            if ($image != "") {
                $file = 'images.' . $index . '.' . $imageId;
                $dir = 'velocity/images';
    
                if (Str::contains($imageId, 'image_')) {
                    if (request()->hasFile($file) && $image) {
                        $filter_index = substr($imageId, 6, 1);
                        if ( isset($data[$filter_index]) ) {
                            $size = array_key_last($saveData[$index]);
                            
                            $saveImage[$size + 1] = request()->file($file)->store($dir);
                        } else {
                            $saveImage[substr($imageId, 6, 1)] = request()->file($file)->store($dir);
                        }
                    }
                } else {
                    if ( isset($advertisement[$index][$imageId]) && $advertisement[$index][$imageId] && !request()->hasFile($file)) {
                        $saveImage[$imageId] = $advertisement[$index][$imageId];
    
                        unset($advertisement[$index][$imageId]);
                    }
    
                    if (request()->hasFile($file) && isset($advertisement[$index][$imageId])) {
                        Storage::delete($advertisement[$index][$imageId]);
    
                        $saveImage[$imageId] = request()->file($file)->store($dir);
                    }
                }
            } else {
                if ($saveData) {
                    $subIndex = substr($imageId, -1);

                    if (isset($advertisement[$index][$subIndex])) {
                        $saveImage[$subIndex] = $advertisement[$index][$subIndex];

                        if (sizeof($advertisement[$index]) == 1) {
                            unset($advertisement[$index]);
                        } else {
                            unset($advertisement[$index][$subIndex]);
                        }
                    }
                }
            }
        }

        if (isset($advertisement[$index]) && $advertisement[$index]) {
            foreach ($advertisement[$index] as $imageId) {
                Storage::delete($imageId);
            }
        }

        return $saveImage;
    }

    /**
     * @param  array    $data
     * @param  int      $index
     * 
     * @return mixed
     */
    public function uploadImage($data, $index)
    {
        $type = 'product_view_images';
        $request = request();

        $image = '';
        $file = $type . '.' . $index;
        $dir = "velocity/$type";

        if ($request->hasFile($file)) {
            Storage::delete($dir . $file);

            $image = $request->file($file)->store($dir);
        }

        return $image;
    }

    /**
     * @param  array  $addImages
     * 
     * @return array
     */
    public function manageAddImages($addImages)
    {
        $imagePaths = [];

        foreach ($addImages as $id => $images) {
            foreach ($images as $key => $image) {
                if ($image) {
                    continue;
                }

                $imagePaths[$id][] = [
                    'id'   => $key,
                    'type' => null,
                    'path' => $image,
                    'url'  => Storage::url($image),
                ];
            }
        }
        
        return $imagePaths;
    }

    private function createMetaData($locale)
    {
        \DB::table('velocity_meta_data')->insert([
            'locale'                   => $locale,

            'home_page_content'        => "<p>@include('shop::home.advertisements.advertisement-four')@include('shop::home.featured-products') @include('shop::home.product-policy') @include('shop::home.advertisements.advertisement-three') @include('shop::home.new-products') @include('shop::home.advertisements.advertisement-two')</p>",
            'footer_left_content'      => __('velocity::app.admin.meta-data.footer-left-raw-content'),

            'footer_middle_content'    => '<div class="col-lg-6 col-md-12 col-sm-12 no-padding"><ul type="none"><li><a href="https://webkul.com/about-us/company-profile/">About Us</a></li><li><a href="https://webkul.com/about-us/company-profile/">Customer Service</a></li><li><a href="https://webkul.com/about-us/company-profile/">What&rsquo;s New</a></li><li><a href="https://webkul.com/about-us/company-profile/">Contact Us </a></li></ul></div><div class="col-lg-6 col-md-12 col-sm-12 no-padding"><ul type="none"><li><a href="https://webkul.com/about-us/company-profile/"> Order and Returns </a></li><li><a href="https://webkul.com/about-us/company-profile/"> Payment Policy </a></li><li><a href="https://webkul.com/about-us/company-profile/"> Shipping Policy</a></li><li><a href="https://webkul.com/about-us/company-profile/"> Privacy and Cookies Policy </a></li></ul></div>',
            'slider'                   => 1,

            'subscription_bar_content' => '<div class="social-icons col-lg-6"><a href="https://webkul.com" target="_blank" class="unset" rel="noopener noreferrer"><i class="fs24 within-circle rango-facebook" title="facebook"></i> </a> <a href="https://webkul.com" target="_blank" class="unset" rel="noopener noreferrer"><i class="fs24 within-circle rango-twitter" title="twitter"></i> </a> <a href="https://webkul.com" target="_blank" class="unset" rel="noopener noreferrer"><i class="fs24 within-circle rango-linked-in" title="linkedin"></i> </a> <a href="https://webkul.com" target="_blank" class="unset" rel="noopener noreferrer"><i class="fs24 within-circle rango-pintrest" title="Pinterest"></i> </a> <a href="https://webkul.com" target="_blank" class="unset" rel="noopener noreferrer"><i class="fs24 within-circle rango-youtube" title="Youtube"></i> </a> <a href="https://webkul.com" target="_blank" class="unset" rel="noopener noreferrer"><i class="fs24 within-circle rango-instagram" title="instagram"></i></a></div>',

            'product_policy'           => '<div class="row col-12 remove-padding-margin"><div class="col-lg-4 col-sm-12 product-policy-wrapper"><div class="card"><div class="policy"><div class="left"><i class="rango-van-ship fs40"></i></div> <div class="right"><span class="font-setting fs20">Free Shipping on Order $20 or More</span></div></div></div></div> <div class="col-lg-4 col-sm-12 product-policy-wrapper"><div class="card"><div class="policy"><div class="left"><i class="rango-exchnage fs40"></i></div> <div class="right"><span class="font-setting fs20">Product Replace &amp; Return Available </span></div></div></div></div> <div class="col-lg-4 col-sm-12 product-policy-wrapper"><div class="card"><div class="policy"><div class="left"><i class="rango-exchnage fs40"></i></div> <div class="right"><span class="font-setting fs20">Product Exchange and EMI Available </span></div></div></div></div></div>',
        ]);
    }
}