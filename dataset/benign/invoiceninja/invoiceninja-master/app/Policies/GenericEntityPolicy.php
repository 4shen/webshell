<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Str;
use Utils;

/**
 * Class GenericEntityPolicy.
 */
class GenericEntityPolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @param $entityType
     * @param $ownerUserId
     *
     * @return bool|mixed
     */
    public static function editByOwner(User $user, $entityType, $ownerUserId)
    {
        $className = static::className($entityType);
        if (method_exists($className, 'editByOwner')) {
            return call_user_func([$className, 'editByOwner'], $user, $ownerUserId);
        }

        return false;
    }

    /**
     * @param User $user
     * @param $entityTypee
     * @param $ownerUserId
     * @param mixed $entityType
     *
     * @return bool|mixed
     */
    public static function viewByOwner(User $user, $entityType, $ownerUserId)
    {
        $className = static::className($entityType);
        if (method_exists($className, 'viewByOwner')) {
            return call_user_func([$className, 'viewByOwner'], $user, $ownerUserId);
        }

        return false;
    }

    /**
     * @param User $user
     * @param $entityType
     *
     * @return bool|mixed
     */
    public static function create(User $user, $entityType)
    {
        /*
        $className = static::className($entityType);
        if (method_exists($className, 'create')) {
            return call_user_func([$className, 'create'], $user, $entityType);
        }

        return false;
        */
        if($user->hasPermission('create_'.$entityType))
            return true;
        else
            return false;
    }

    /**
     * @param User $user
     * @param $entityType
     *
     * @return bool|mixed
     */
    public static function view(User $user, $entityType)
    {
        /*
        $className = static::className($entityType);
        if (method_exists($className, 'view')) {
            return call_user_func([$className, 'view'], $user, $entityType);
        }

        return false;*/

        if($user->hasPermission('view_'.$entityType))
            return true;
        else
            return false;
    }

    /**
     * @param User $user
     * @param $item - entity name or object
     *
     * @return bool
     */

    public static function edit(User $user, $item)
    {
        if (! static::checkModuleEnabled($user, $item))
            return false;


        $entityType = is_string($item) ? $item : $item->getEntityType();
        return $user->hasPermission('edit_' . $entityType) || $user->owns($item);
    }

    /**
     * @param User $user
     * @param $item - entity name or object
     * @return bool
     */

    private static function checkModuleEnabled(User $user, $item)
    {
        $entityType = is_string($item) ? $item : $item->getEntityType();
        return $user->account->isModuleEnabled($entityType);
    }



    private static function className($entityType)
    {
        if (! Utils::isNinjaProd()) {
            if ($module = \Module::find($entityType)) {
                return "Modules\\{$module->getName()}\\Policies\\{$module->getName()}Policy";
            }
        }

        $studly = Str::studly($entityType);

        return "App\\Policies\\{$studly}Policy";
    }
}
