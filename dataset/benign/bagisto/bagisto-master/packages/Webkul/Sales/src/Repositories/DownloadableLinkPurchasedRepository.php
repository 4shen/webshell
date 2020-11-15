<?php

namespace Webkul\Sales\Repositories;

use Illuminate\Container\Container as App;
use Webkul\Core\Eloquent\Repository;
use Webkul\Sales\Contracts\DownloadableLinkPurchased;
use Webkul\Product\Repositories\ProductDownloadableLinkRepository;

class DownloadableLinkPurchasedRepository extends Repository
{

    /**
     * ProductDownloadableLinkRepository object
     *
     * @var \Webkul\Product\Repositories\ProductDownloadableLinkRepository
     */
    protected $productDownloadableLinkRepository;

    /**
     * Create a new repository instance.
     *
     * @param  \Webkul\Product\Repositories\ProductDownloadableLinkRepository  $productDownloadableLinkRepository
     * @return void
     */
    public function __construct(
        ProductDownloadableLinkRepository $productDownloadableLinkRepository,
        App $app
    )
    {
        $this->productDownloadableLinkRepository = $productDownloadableLinkRepository;

        parent::__construct($app);
    }

    /**
     * Specify Model class name
     *
     * @return string
     */
    function model()
    {
        return DownloadableLinkPurchased::class;
    }

    /**
     * @param  \Webkul\Sales\Contracts\OrderItem  $orderItem
     * @return void
     */
    public function saveLinks($orderItem)
    {
        if (! $this->isValidDownloadableProduct($orderItem)) {
            return;
        }

        foreach ($orderItem->additional['links'] as $linkId) {
            if (! $productDownloadableLink = $this->productDownloadableLinkRepository->find($linkId)) {
                continue;
            }

            $this->create([
                'name'            => $productDownloadableLink->title,
                'product_name'    => $orderItem->name,
                'url'             => $productDownloadableLink->url,
                'file'            => $productDownloadableLink->file,
                'file_name'       => $productDownloadableLink->file_name,
                'type'            => $productDownloadableLink->type,
                'download_bought' => $productDownloadableLink->downloads * $orderItem->qty_ordered,
                'status'          => 'pending',
                'customer_id'     => $orderItem->order->customer_id,
                'order_id'        => $orderItem->order_id,
                'order_item_id'   => $orderItem->id,
            ]);
        }
    }

    /**
     * Return true, if ordered item is valid downloadable product with links
     *
     * @param  \Webkul\Sales\Contracts\OrderItem  $orderItem
     * @return bool
     */
    private function isValidDownloadableProduct($orderItem) : bool {
        if (stristr($orderItem->type,'downloadable') !== false && isset($orderItem->additional['links'])) {
            return true;
        }
        
        return false;
    }

    /**
     * @param  \Webkul\Sales\Contracts\OrderItem  $orderItem
     * @param  string    $status
     * @return void
     */
    public function updateStatus($orderItem, $status)
    {
        $purchasedLinks = $this->findByField('order_item_id', $orderItem->id);

        foreach ($purchasedLinks as $purchasedLink) {
            $this->update([
                'status' => $status,
            ], $purchasedLink->id);
        }
    }
}