<?php
namespace Directus\GraphQL\Type\Directus;

use Directus\Application\Application;
use Directus\GraphQL\Types;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;

class DirectusActivityType extends ObjectType
{
    private $container;
    public function __construct()
    {
        $this->container = Application::getInstance()->getContainer();
        $config = [
            'name' => 'DirectusActivityItem',
            'fields' =>  function () {
                return [
                    'id' => Types::id(),
                    'action' => Types::string(),
                    'collection' => Types::string(),
                    'item' => Types::string(),
                    'action_by' => Types::directusUser(),
                    'action_on' => Types::datetime(),
                    'edited_on' => Types::datetime(),
                    'comment_deleted_on' => Types::datetime(),
                    'ip' => Types::string(),
                    'user_agent' => Types::string(),
                    'comment' => Types::string(),
                ];
            },
            'interfaces' => [
                Types::node()
            ],
            'resolveField' => function ($value, $args, $context, ResolveInfo $info) {
                $method = 'resolve' . ucfirst($info->fieldName);
                if (method_exists($this, $method)) {
                    return $this->{$method}($value, $args, $context, $info);
                } else {
                    return $value[$info->fieldName];
                }
            }
        ];
        parent::__construct($config);
    }
}
