<?php
/**
 * FecShop file.
 *
 * @link http://www.fecshop.com/
 * @copyright Copyright (c) 2016 FecShop Software LLC
 * @license http://www.fecshop.com/license/
 */

namespace fecshop\app\apphtml5\modules\Customer\block\account;

use Yii;

/**
 * @author Terry Zhao <2358269014@qq.com>
 * @since 1.0
 */
class Resetpassword
{
    public function getLastData()
    {
        $resetToken = Yii::$app->request->get('resetToken');
        $identity = Yii::$service->customer->findByPasswordResetToken($resetToken);
        //var_dump($identity );exit;
        if ($identity) {
            return [
                'identity'        => $identity,
                'resetToken'    => $resetToken,
                'forgotPasswordUrl'=> Yii::$service->url->getUrl('customer/account/forgotpassword'),
            ];
        } else {
            return [
                'forgotPasswordUrl'=> Yii::$service->url->getUrl('customer/account/forgotpassword'),
            ];
        }
    }

    public function resetPassword($editForm)
    {
        $email = isset($editForm['email']) ? $editForm['email'] : '';
        $password = isset($editForm['password']) ? $editForm['password'] : '';
        $confirmation = isset($editForm['confirmation']) ? $editForm['confirmation'] : '';
        $resetToken = isset($editForm['resetToken']) ? $editForm['resetToken'] : '';
        if (!$resetToken) {
            Yii::$service->page->message->addError(['resetToken is empty!']);

            return;
        }
        if (!$email || !$password || !$confirmation) {
            Yii::$service->page->message->addError(['email or password can not empty']);

            return;
        }
        if ($password != $confirmation) {
            Yii::$service->page->message->addError(['The password and confirmation password are not equal']);

            return;
        }
        $identity = Yii::$service->customer->findByPasswordResetToken($resetToken);

        if ($identity['email'] != $email) {
            Yii::$service->page->message->addError(['email do not match the resetToken ']);

            return;
        }
        if ($identity) {
            $status = Yii::$service->customer->changePasswordAndClearToken($password, $identity);
            if ($status) {
                return true;
            }
        } else {
            Yii::$service->page->message->addError(['Reset Password Token is Expired OR The Email you entered does not match with the resetToekn. ']);

            return;
        }
    }
}
