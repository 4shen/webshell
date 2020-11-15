<?php

namespace Webkul\Sales\Repositories;

use Illuminate\Container\Container as App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Webkul\Core\Eloquent\Repository;
use Webkul\Sales\Contracts\Order;
use Webkul\Sales\Models\Order as OrderModel;

class OrderRepository extends Repository
{
    /**
     * OrderItemRepository object
     *
     * @var \Webkul\Sales\Repositories\OrderItemRepository
     */
    protected $orderItemRepository;

    /**
     * DownloadableLinkPurchasedRepository object
     *
     * @var \Webkul\Sales\Repositories\DownloadableLinkPurchasedRepository
     */
    protected $downloadableLinkPurchasedRepository;

    /**
     * Create a new repository instance.
     *
     * @param  \Webkul\Sales\Repositories\OrderItemRepository  $orderItemRepository
     * @param  \Webkul\Sales\Repositories\DownloadableLinkPurchasedRepository  $downloadableLinkPurchasedRepository
     * @return void
     */
    public function __construct(
        OrderItemRepository $orderItemRepository,
        DownloadableLinkPurchasedRepository $downloadableLinkPurchasedRepository,
        App $app
    ) {
        $this->orderItemRepository = $orderItemRepository;

        $this->downloadableLinkPurchasedRepository = $downloadableLinkPurchasedRepository;

        parent::__construct($app);
    }

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Order::class;
    }

    /**
     * @param  array  $data
     * @return \Webkul\Sales\Contracts\Order
     */
    public function create(array $data)
    {
        DB::beginTransaction();

        try {
            Event::dispatch('checkout.order.save.before', $data);

            if (isset($data['customer']) && $data['customer']) {
                $data['customer_id'] = $data['customer']->id;
                $data['customer_type'] = get_class($data['customer']);
            } else {
                unset($data['customer']);
            }

            if (isset($data['channel']) && $data['channel']) {
                $data['channel_id'] = $data['channel']->id;
                $data['channel_type'] = get_class($data['channel']);
                $data['channel_name'] = $data['channel']->name;
            } else {
                unset($data['channel']);
            }

            $data['status'] = 'pending';

            $order = $this->model->create(array_merge($data, ['increment_id' => $this->generateIncrementId()]));

            $order->payment()->create($data['payment']);

            if (isset($data['shipping_address'])) {
                $order->addresses()->create($data['shipping_address']);
            }

            $order->addresses()->create($data['billing_address']);

            foreach ($data['items'] as $item) {
                Event::dispatch('checkout.order.orderitem.save.before', $data);

                $orderItem = $this->orderItemRepository->create(array_merge($item, ['order_id' => $order->id]));

                if (isset($item['children']) && $item['children']) {
                    foreach ($item['children'] as $child) {
                        $this->orderItemRepository->create(array_merge($child, ['order_id' => $order->id, 'parent_id' => $orderItem->id]));
                    }
                }

                $this->orderItemRepository->manageInventory($orderItem);

                $this->downloadableLinkPurchasedRepository->saveLinks($orderItem, 'available');

                Event::dispatch('checkout.order.orderitem.save.after', $data);
            }

            Event::dispatch('checkout.order.save.after', $order);
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }

        DB::commit();

        return $order;
    }

    /**
     * @param  int  $orderId
     * @return \Webkul\Sales\Contracts\Order
     */
    public function cancel($orderId)
    {
        $order = $this->findOrFail($orderId);

        if (! $order->canCancel()) {
            return false;
        }

        Event::dispatch('sales.order.cancel.before', $order);

        foreach ($order->items as $item) {
            if (! $item->qty_to_cancel) {
                continue;
            }

            $orderItems = [];

            if ($item->getTypeInstance()->isComposite()) {
                foreach ($item->children as $child) {
                    $orderItems[] = $child;
                }
            } else {
                $orderItems[] = $item;
            }

            foreach ($orderItems as $orderItem) {
                if ($orderItem->product) {
                    $this->orderItemRepository->returnQtyToProductInventory($orderItem);
                }

                if ($orderItem->qty_ordered) {
                    $orderItem->qty_canceled += $orderItem->qty_to_cancel;
                    $orderItem->save();

                    if ($orderItem->parent && $orderItem->parent->qty_ordered) {
                        $orderItem->parent->qty_canceled += $orderItem->parent->qty_to_cancel;
                        $orderItem->parent->save();
                    }
                } else {
                    $orderItem->parent->qty_canceled += $orderItem->parent->qty_to_cancel;
                    $orderItem->parent->save();
                }
            }

            $this->downloadableLinkPurchasedRepository->updateStatus($item, 'expired');
        }

        $this->updateOrderStatus($order);

        Event::dispatch('sales.order.cancel.after', $order);

        return true;
    }

    /**
     * @return int
     */
    public function generateIncrementId()
    {
        foreach ([  'Prefix'   => 'prefix',
                    'Length'   => 'length',
                    'Suffix'   => 'suffix', ] as
                    $varSuffix => $confKey)
        {
            $var = "invoiceNumber{$varSuffix}";
            $$var = core()->getConfigData('sales.orderSettings.order_number.order_number_'.$confKey) ?: false;
        }

        $lastOrder = $this->model->orderBy('id', 'desc')->limit(1)->first();
        $lastId = $lastOrder ? $lastOrder->id : 0;

        if ($invoiceNumberLength && ($invoiceNumberPrefix || $invoiceNumberSuffix)) {
            $invoiceNumber = ($invoiceNumberPrefix) . sprintf("%0{$invoiceNumberLength}d", 0) . ($lastId + 1) . ($invoiceNumberSuffix);
        } else {
            $invoiceNumber = $lastId + 1;
        }

        return $invoiceNumber;
    }

    /**
     * @param  \Webkul\Sales\Contracts\Order  $order
     * @return void
     */
    public function isInCompletedState($order)
    {
        $totalQtyOrdered = $totalQtyInvoiced = $totalQtyShipped = $totalQtyRefunded = $totalQtyCanceled = 0;

        foreach ($order->items()->get() as $item) {
            $totalQtyOrdered += $item->qty_ordered;
            $totalQtyInvoiced += $item->qty_invoiced;

            if (! $item->isStockable()) {
                $totalQtyShipped += $item->qty_ordered;
            } else {
                $totalQtyShipped += $item->qty_shipped;
            }

            $totalQtyRefunded += $item->qty_refunded;
            $totalQtyCanceled += $item->qty_canceled;
        }

        if ($totalQtyOrdered != ($totalQtyRefunded + $totalQtyCanceled)
            && $totalQtyOrdered == $totalQtyInvoiced + $totalQtyCanceled
            && $totalQtyOrdered == $totalQtyShipped + $totalQtyRefunded + $totalQtyCanceled) {
            return true;
        }

        return false;
    }

    /**
     * @param  \Webkul\Sales\Contracts\Order  $order
     * @return void
     */
    public function isInCanceledState($order)
    {
        $totalQtyOrdered = $totalQtyCanceled = 0;

        foreach ($order->items()->get() as $item) {
            $totalQtyOrdered += $item->qty_ordered;
            $totalQtyCanceled += $item->qty_canceled;
        }

        return $totalQtyOrdered === $totalQtyCanceled;
    }

    /**
     * @param mixed $order
     *
     * @return void
     */
    public function isInClosedState($order)
    {
        $totalQtyOrdered = $totalQtyRefunded = $totalQtyCanceled = 0;

        foreach ($order->items()->get() as $item) {
            $totalQtyOrdered += $item->qty_ordered;
            $totalQtyRefunded += $item->qty_refunded;
            $totalQtyCanceled += $item->qty_canceled;
        }

        return $totalQtyOrdered === $totalQtyRefunded + $totalQtyCanceled;
    }

    /**
     * @param  \Webkul\Sales\Contracts\Order  $order
     * @return void
     */
    public function updateOrderStatus($order)
    {
        $status = 'processing';

        if ($this->isInCompletedState($order)) {
            $status = 'completed';
        }

        if ($this->isInCanceledState($order)) {
            $status = 'canceled';
        } elseif ($this->isInClosedState($order)) {
            $status = 'closed';
        }

        $order->status = $status;
        $order->save();
    }

    /**
     * @param  \Webkul\Sales\Contracts\Order  $order
     * @return mixed
     */
    public function collectTotals($order)
    {
        //Order invoice total
        $order->sub_total_invoiced = $order->base_sub_total_invoiced = 0;
        $order->shipping_invoiced = $order->base_shipping_invoiced = 0;
        $order->tax_amount_invoiced = $order->base_tax_amount_invoiced = 0;
        $order->discount_invoiced = $order->base_discount_invoiced = 0;

        foreach ($order->invoices as $invoice) {
            $order->sub_total_invoiced += $invoice->sub_total;
            $order->base_sub_total_invoiced += $invoice->base_sub_total;

            $order->shipping_invoiced += $invoice->shipping_amount;
            $order->base_shipping_invoiced += $invoice->base_shipping_amount;

            $order->tax_amount_invoiced += $invoice->tax_amount;
            $order->base_tax_amount_invoiced += $invoice->base_tax_amount;

            $order->discount_invoiced += $invoice->discount_amount;
            $order->base_discount_invoiced += $invoice->base_discount_amount;
        }

        $order->grand_total_invoiced = $order->sub_total_invoiced + $order->shipping_invoiced + $order->tax_amount_invoiced - $order->discount_invoiced;
        $order->base_grand_total_invoiced = $order->base_sub_total_invoiced + $order->base_shipping_invoiced + $order->base_tax_amount_invoiced - $order->base_discount_invoiced;

        //Order refund total
        $order->sub_total_refunded = $order->base_sub_total_refunded = 0;
        $order->shipping_refunded = $order->base_shipping_refunded = 0;
        $order->tax_amount_refunded = $order->base_tax_amount_refunded = 0;
        $order->discount_refunded = $order->base_discount_refunded = 0;
        $order->grand_total_refunded = $order->base_grand_total_refunded = 0;

        foreach ($order->refunds as $refund) {
            $order->sub_total_refunded += $refund->sub_total;
            $order->base_sub_total_refunded += $refund->base_sub_total;

            $order->shipping_refunded += $refund->shipping_amount;
            $order->base_shipping_refunded += $refund->base_shipping_amount;

            $order->tax_amount_refunded += $refund->tax_amount;
            $order->base_tax_amount_refunded += $refund->base_tax_amount;

            $order->discount_refunded += $refund->discount_amount;
            $order->base_discount_refunded += $refund->base_discount_amount;

            $order->grand_total_refunded += $refund->adjustment_refund - $refund->adjustment_fee;
            $order->base_grand_total_refunded += $refund->base_adjustment_refund - $refund->base_adjustment_fee;
        }

        $order->grand_total_refunded += $order->sub_total_refunded + $order->shipping_refunded + $order->tax_amount_refunded - $order->discount_refunded;
        $order->base_grand_total_refunded += $order->base_sub_total_refunded + $order->base_shipping_refunded + $order->base_tax_amount_refunded - $order->base_discount_refunded;

        $order->save();

        return $order;
    }
}
