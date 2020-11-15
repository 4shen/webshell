<?php
/**
 * FecShop file.
 *
 * @link http://www.fecshop.com/
 * @copyright Copyright (c) 2016 FecShop Software LLC
 * @license http://www.fecshop.com/license/
 */

namespace fecshop\models\mongodb;

use yii\mongodb\ActiveRecord;

/**
 * @author Terry Zhao <2358269014@qq.com>
 * @since 1.0
 */
class ErrorHandlerLog extends ActiveRecord
{
    //const CATEGORY_APPSERVER    = 'appfront';
    //const CATEGORY_APPADMIN     = 'appadmin';
    //const CATEGORY_APPHTML5     = 'apphtml5';
    //const CATEGORY_APPSERVER    = 'appserver';
    //const CATEGORY_APPAPI       = 'appapi';
    //const CATEGORY_CONSOLE      = 'console';
    /**
     * mongodb collection �����֣��൱��mysql��table name
     */
    public static function collectionName()
    {
        return 'error_handler_log';
    }
    /**
     * mongodb��û�б�ṹ�ģ���˲�����mysql����ȡ������ṹ���ֶ���Ϊmodel������
     * ��ˣ���Ҫ�Լ�����model�����ԣ�����ķ��������������
     */
    public function attributes()
    {
        return [
            '_id',
            'category',     // �������
            'code',         // http ������
            'message',      // ������Ϣ
            'file',         // ����������ļ�
            'line',         // �������������ļ��Ĵ�����
            'created_at',   // ���������ִ��ʱ��
            'ip',           // �����˵�ip
            'name',         // ���������
            'trace_string', // �����׷����Ϣ
            'url',          // 
            'request_info', // request ��Ϣ
       ];
    }
    /**
     * "code": 500,
     * "message": "syntax error, unexpected '}'",
     * "file": "/www/web/develop/fecshop/vendor/fancyecommerce/fecshop/app/appserver/modules/Customer/controllers/TestController.php",
     * "line": 27,
     * "time": "2017-11-30 14:26:34",
     * "ip": "183.14.76.88"
     */

}
