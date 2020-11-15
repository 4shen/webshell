<?php
/**
 * FecShop file.
 *
 * @link http://www.fecshop.com/
 * @copyright Copyright (c) 2016 FecShop Software LLC
 * @license http://www.fecshop.com/license/
 */
namespace fecshop\app\appserver\modules\Catalog\helpers;
//use fecshop\app\appfront\modules\Catalog\helpers\Review as ReviewHelper;
use Yii;
/**
 * @author Terry Zhao <2358269014@qq.com>
 * @since 1.0
 */
class Review extends \yii\base\BaseObject
{
    public $product_id;
    public $spu;
    public $filterBySpu = true;
    public $filterOrderBy = 'review_date';
    
    /**
     * Ϊ�˿���ʹ��rewriteMap��use ������ļ�ͳһ��������ķ�ʽ��ͨ��Yii::mapGet()�õ�className��Object
     */
    protected $_reviewHelperName = '\fecshop\app\appfront\modules\Catalog\helpers\Review';
    protected $_reviewHelper;
    public function __construct()
    {
        /**
         * ͨ��Yii::mapGet() �õ���д���class�����Լ�����Yii::mapGet�����ļ�@fecshop\yii\Yii.php��
         */
        list($this->_reviewHelperName,$this->_reviewHelper) = Yii::mapGet($this->_reviewHelperName);  
        $reviewHelper = $this->_reviewHelper;
        // ��ʼ����ǰappfront�����ã�����service�ĳ�ʼ���á�
        $reviewHelper::initReviewConfig();
    }
    /**
     * �õ���ǰspu���������������Ϣ��
     */
    public function getLastData()
    {
        if (!$this->spu || !$this->product_id) {
            return;
        }
        if ($this->filterBySpu) {
            $data = $this->getReviewsBySpu($this->spu);
            $count = $data['count'];
            $coll = $data['coll'];
            if (is_array($coll) && !empty($coll)) {
                foreach ($coll as $k => $v) {
                    $coll[$k]['review_date_str'] = date('Y-m-d H:i:s', $v['review_date']);
                }
            }
            return [
                '_id' => $this->product_id,
                'spu' => $this->spu,
                'review_count'    => $count,
                'coll'            => $coll,
                'noActiveStatus'=> Yii::$service->product->review->noActiveStatus(),
            ];
        }
    }
    /**
     * �õ���ǰspu���������������Ϣ��
     */
    public function getReviewsBySpu($spu)
    {
        // $review = Yii::$app->getModule('catalog')->params['review'];
        $appName = Yii::$service->helper->getAppName();
        $productPageReviewCount = Yii::$app->store->get($appName.'_catalog','review_productPageReviewCount');
        
        $productPageReviewCount = $productPageReviewCount ? $productPageReviewCount: 10;
        $currentIp = \fec\helpers\CFunc::get_real_ip();
        $filter = [
            'numPerPage'    => $productPageReviewCount,
            'pageNum'        => 1,
            'orderBy'    => [$this->filterOrderBy => SORT_DESC],
            'where'            => [
                [
                    '$or' => [
                        [
                            'status' => Yii::$service->product->review->activeStatus(),
                            'product_spu' => $spu,
                        ],
                        [
                            'status' => Yii::$service->product->review->noActiveStatus(),
                            'product_spu' => $spu,
                            'ip' => $currentIp,
                        ],
                    ],
                ],
            ],
        ];
        // ������ review ��Ϣ��
        return Yii::$service->product->review->getListBySpu($filter);
    }
}