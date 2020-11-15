<?php

namespace Webkul\Checkout\Repositories;

use Webkul\Core\Eloquent\Repository;

class CartRepository extends Repository
{
    /**
     * Specify Model class name
     *
     * @return Mixed
     */

    function model()
    {
        return 'Webkul\Checkout\Contracts\Cart';
    }

    /**
     * @param  array  $data
     * @return \Webkul\Checkout\Contracts\Cart
     */
    public function create(array $data)
    {
        $cart = $this->model->create($data);

        return $cart;
    }

    /**
     * @param  array  $data
     * @param  int  $id
     * @param  string  $attribute
     * @return \Webkul\Checkout\Contracts\Cart
     */
    public function update(array $data, $id, $attribute = "id")
    {
        $cart = $this->find($id);

        $cart->update($data);

        return $cart;
    }

    /**
     * Method to detach associations. Use this only with guest cart only.
     * 
     * @param  int  $cartId
     * @return bool
     */
    public function deleteParent($cartId) {
        $cart = $this->model->find($cartId);

        return $this->model->destroy($cartId);
    }
}