<?php

namespace Webkul\Sales\Repositories;

use Illuminate\Container\Container as App;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\DB;
use Webkul\Core\Eloquent\Repository;
use Webkul\Sales\Contracts\Shipment;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Repositories\OrderItemRepository;
use Webkul\Sales\Repositories\ShipmentItemRepository;

class ShipmentRepository extends Repository
{
    /**
     * OrderRepository object
     *
     * @var \Webkul\Sales\Repositories\OrderRepository
     */
    protected $orderRepository;

    /**
     * OrderItemRepository object
     *
     * @var \Webkul\Sales\Repositories\OrderItemRepository
     */
    protected $orderItemRepository;

    /**
     * ShipmentItemRepository object
     *
     * @var \Webkul\Sales\Repositories\ShipmentItemRepository
     */
    protected $shipmentItemRepository;

    /**
     * Create a new repository instance.
     *
     * @param  \Webkul\Sales\Repositories\OrderRepository  $orderRepository
     * @param  \Webkul\Sales\Repositories\OrderItemRepository  $orderItemRepository
     * @param  \Webkul\Sales\Repositories\ShipmentItemRepository  $orderItemRepository
     * @return void
     */
    public function __construct(
        OrderRepository $orderRepository,
        OrderItemRepository $orderItemRepository,
        ShipmentItemRepository $shipmentItemRepository,
        App $app
    )
    {
        $this->orderRepository = $orderRepository;

        $this->orderItemRepository = $orderItemRepository;

        $this->shipmentItemRepository = $shipmentItemRepository;

        parent::__construct($app);
    }

    /**
     * Specify Model class name
     *
     * @return string
     */
    function model()
    {
        return Shipment::class;
    }

    /**
     * @param  array  $data
     * @return \Webkul\Sales\Contracts\Shipment
     */
    public function create(array $data)
    {
        DB::beginTransaction();

        try {
            Event::dispatch('sales.shipment.save.before', $data);

            $order = $this->orderRepository->find($data['order_id']);

            $shipment = $this->model->create([
                'order_id'            => $order->id,
                'total_qty'           => 0,
                'carrier_title'       => $data['shipment']['carrier_title'],
                'track_number'        => $data['shipment']['track_number'],
                'customer_id'         => $order->customer_id,
                'customer_type'       => $order->customer_type,
                'order_address_id'    => $order->shipping_address->id,
                'inventory_source_id' => $data['shipment']['source'],
            ]);

            $totalQty = 0;

            foreach ($data['shipment']['items'] as $itemId => $inventorySource) {
                $qty = $inventorySource[$data['shipment']['source']];

                $orderItem = $this->orderItemRepository->find($itemId);

                if ($qty > $orderItem->qty_to_ship) {
                    $qty = $orderItem->qty_to_ship;
                }

                $totalQty += $qty;

                $shipmentItem = $this->shipmentItemRepository->create([
                    'shipment_id'   => $shipment->id,
                    'order_item_id' => $orderItem->id,
                    'name'          => $orderItem->name,
                    'sku'           => $orderItem->getTypeInstance()->getOrderedItem($orderItem)->sku,
                    'qty'           => $qty,
                    'weight'        => $orderItem->weight * $qty,
                    'price'         => $orderItem->price,
                    'base_price'    => $orderItem->base_price,
                    'total'         => $orderItem->price * $qty,
                    'base_total'    => $orderItem->base_price * $qty,
                    'product_id'    => $orderItem->product_id,
                    'product_type'  => $orderItem->product_type,
                    'additional'    => $orderItem->additional,
                ]);

                if ($orderItem->getTypeInstance()->isComposite()) {
                    foreach ($orderItem->children as $child) {
                        if (! $child->qty_ordered) {
                            $finalQty = $qty;
                        } else {
                            $finalQty = ($child->qty_ordered / $orderItem->qty_ordered) * $qty;
                        }

                        $this->shipmentItemRepository->updateProductInventory([
                            'shipment'  => $shipment,
                            'product'   => $child->product,
                            'qty'       => $finalQty,
                            'vendor_id' => isset($data['vendor_id']) ? $data['vendor_id'] : 0,
                        ]);

                        $this->orderItemRepository->update(['qty_shipped' => $child->qty_shipped + $finalQty], $child->id);
                    }
                } else {
                    $this->shipmentItemRepository->updateProductInventory([
                        'shipment'  => $shipment,
                        'product'   => $orderItem->product,
                        'qty'       => $qty,
                        'vendor_id' => isset($data['vendor_id']) ? $data['vendor_id'] : 0,
                    ]);
                }

                $this->orderItemRepository->update(['qty_shipped' => $orderItem->qty_shipped + $qty], $orderItem->id);
            }

            $shipment->update([
                'total_qty'             => $totalQty,
                'inventory_source_name' => $shipment->inventory_source->name,
            ]);

            $this->orderRepository->updateOrderStatus($order);

            Event::dispatch('sales.shipment.save.after', $shipment);
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }

        DB::commit();

        return $shipment;
    }
}