<?php

/*
 * FecShop file.
 *
 * @link http://www.fecshop.com/
 * @copyright Copyright (c) 2016 FecShop Software LLC
 * @license http://www.fecshop.com/license/
 */

namespace fecshop\services\admin;

//use fecshop\models\mysqldb\cms\StaticBlock;
use Yii;
use fecshop\services\Service;

/**
 * @author Terry Zhao <2358269014@qq.com>
 * @since 1.0
 */
class UserRole extends Service
{
    public $numPerPage = 20;

    protected $_roleModelName = '\fecshop\models\mysqldb\admin\UserRole';

    protected $_roleModel;

    /**
     *  language attribute.
     */
    protected $_lang_attr = [
    ];

    public function init()
    {
        parent::init();
        list($this->_roleModelName, $this->_roleModel) = Yii::mapGet($this->_roleModelName);
    }
    public function getPrimaryKey()
    {
        return 'id';
    }

    public function getByPrimaryKey($primaryKey)
    {
        if ($primaryKey) {
            $one = $this->_roleModel->findOne($primaryKey);
            foreach ($this->_lang_attr as $attrName) {
                if (isset($one[$attrName])) {
                    $one[$attrName] = unserialize($one[$attrName]);
                }
            }

            return $one;
        } else {
            
            return new $this->_roleModelName();
        }
    }
    /*
     * example filter:
     * [
     * 		'numPerPage' 	=> 20,
     * 		'pageNum'		=> 1,
     * 		'orderBy'	=> ['_id' => SORT_DESC, 'sku' => SORT_ASC ],
            'where'			=> [
                ['>','price',1],
                ['<=','price',10]
     * 			['sku' => 'uk10001'],
     * 		],
     * 	'asArray' => true,
     *  'fetchAll' => true,
     * ]
     */
    public function coll($filter = '')
    {
        $query = $this->_roleModel->find();
        $query = Yii::$service->helper->ar->getCollByFilter($query, $filter);
        $coll = $query->all();
        if (!empty($coll)) {
            foreach ($coll as $k => $one) {
                foreach ($this->_lang_attr as $attr) {
                    $one[$attr] = $one[$attr] ? unserialize($one[$attr]) : '';
                }
                $coll[$k] = $one;
            }
        }
        
        return [
            'coll' => $coll,
            'count'=> $query->limit(null)->offset(null)->count(),
        ];
    }

    
    public function remove($ids)
    {
        if (!$ids) {
            Yii::$service->helper->errors->add('remove id is empty');

            return false;
        }
        if (is_array($ids) && !empty($ids)) {
            foreach ($ids as $id) {
                $model = $this->_roleModel->findOne($id);
                $model->delete();
            }
        } else {
            $id = $ids;
            $model = $this->_roleModel->findOne($id);
            $model->delete();
        }

        return true;
    }

    /**
     * @param $role_id int
     * @return bool
     * 按照$role_id为条件进行删除
     */
    public function removeByRoleId($role_id){
        $this->_roleModel->deleteAll(['role_id' => $role_id]);

        return true;
    }
    /**
     * @param $user_id array int
     * @return bool
     * 按照$user_id为条件进行删除
     */
    public function deleteByUserIds($user_ids){
        $this->_roleModel->deleteAll(['in', 'user_id', $user_ids]);

        return true;
        
    }
    
    /**
     * @param $user_id int 
     * @param $roles array, role id array.
     * @return boolean 
     * 保存userId为$user_id对应的 $roles array, 不在$roles array中其他存在数据库的roles将会被删除
     */
    public function saveUserRole($user_id, $roles){
        $role_ids = [];
        if (!empty($roles)) {
            foreach ($roles as $k=>$role_id) {
                $one = $this->_roleModel->findOne([
                    'role_id' => $role_id,
                    'user_id' => $user_id,
                ]);
                $role_ids[] = $role_id;
                if (!$one['id']) {
                    $one = new $this->_roleModelName;
                    $one->role_id = $role_id;
                    $one->user_id = $user_id;
                    $one->save();
                }
            }
            if (!empty($role_ids) && is_array($role_ids)) {
                $this->_roleModel->deleteAll([
                    'and',
                    ['user_id' => $user_id],
                    ['not in', 'role_id', $role_ids],
                ]);
            } else {
                Yii::$service->helper->errors->add('You must at least select one user role');
                
                return false;
            }

        }else{
            Yii::$service->helper->errors->add('You must at least select one user role');
            
            return false;
        }
        
        return true;
    }
    
}
