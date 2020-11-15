<?php

/*
 * This file is part of Cachet.
 *
 * (c) Alt Three Services Limited
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CachetHQ\Cachet\Bus\Commands\Component;

use CachetHQ\Cachet\Models\Component;

final class UpdateComponentCommand
{
    /**
     * The component to update.
     *
     * @var \CachetHQ\Cachet\Models\Component
     */
    public $component;

    /**
     * The component name.
     *
     * @var string|null
     */
    public $name;

    /**
     * The component description.
     *
     * @var string|null
     */
    public $description;

    /**
     * The component status.
     *
     * @var int|null
     */
    public $status;

    /**
     * The component link.
     *
     * @var string|null
     */
    public $link;

    /**
     * The component order.
     *
     * @var int|null
     */
    public $order;

    /**
     * The component group.
     *
     * @var int|null
     */
    public $group_id;

    /**
     * Is the component enabled?
     *
     * @var bool|null
     */
    public $enabled;

    /**
     * JSON meta data for the component.
     *
     * @var array|null
     */
    public $meta;

    /**
     * The tags.
     *
     * @var string|null
     */
    public $tags;

    /**
     * If this is true, we won't notify subscribers of the change.
     *
     * @var bool
     */
    public $silent;

    /**
     * The validation rules.
     *
     * @var string[]
     */
    public $rules = [
        'name'        => 'nullable|string',
        'description' => 'nullable|string',
        'status'      => 'nullable|int|min:0|max:4',
        'link'        => 'nullable|url',
        'order'       => 'nullable|int',
        'group_id'    => 'nullable|int',
        'enabled'     => 'nullable|bool',
        'meta'        => 'nullable|array',
        'silent'      => 'nullable|bool',
    ];

    /**
     * Create a new update component command instance.
     *
     * @param \CachetHQ\Cachet\Models\Component $component
     * @param string|null                       $name
     * @param string|null                       $description
     * @param int|null                          $status
     * @param string|null                       $link
     * @param int|null                          $order
     * @param int|null                          $group_id
     * @param bool|null                         $enabled
     * @param array|null                        $meta
     * @param string|null                       $tags
     * @param bool                              $silent
     *
     * @return void
     */
    public function __construct(Component $component, $name = null, $description = null, $status = null, $link = null, $order = null, $group_id = null, $enabled = null, $meta = null, $tags = null, $silent = null)
    {
        $this->component = $component;
        $this->name = $name;
        $this->description = $description;
        $this->status = $status;
        $this->link = $link;
        $this->order = $order;
        $this->group_id = $group_id;
        $this->enabled = $enabled;
        $this->meta = $meta;
        $this->tags = $tags;
        $this->silent = $silent;
        $this->tags = $tags;
    }
}
