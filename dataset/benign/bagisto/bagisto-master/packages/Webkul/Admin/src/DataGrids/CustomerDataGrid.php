<?php

namespace Webkul\Admin\DataGrids;

use Illuminate\Support\Facades\DB;
use Webkul\Ui\DataGrid\DataGrid;

class CustomerDataGrid extends DataGrid
{
    protected $index = 'customer_id';

    protected $sortOrder = 'desc';

    protected $itemsPerPage = 10;

    public function prepareQueryBuilder()
    {
        $queryBuilder = DB::table('customers')
            ->leftJoin('customer_groups', 'customers.customer_group_id', '=', 'customer_groups.id')
            ->addSelect('customers.id as customer_id', 'customers.email', 'customer_groups.name', 'customers.phone', 'customers.gender', 'status')
            ->addSelect(DB::raw('CONCAT(' . DB::getTablePrefix() . 'customers.first_name, " ", ' . DB::getTablePrefix() . 'customers.last_name) as full_name'));

        $this->addFilter('customer_id', 'customers.id');
        $this->addFilter('full_name', DB::raw('CONCAT(' . DB::getTablePrefix() . 'customers.first_name, " ", ' . DB::getTablePrefix() . 'customers.last_name)'));
        $this->addFilter('phone', 'customers.phone');
        $this->addFilter('gender', 'customers.gender');

        $this->setQueryBuilder($queryBuilder);
    }

    public function addColumns()
    {
        $this->addColumn([
            'index'      => 'customer_id',
            'label'      => trans('admin::app.datagrid.id'),
            'type'       => 'number',
            'searchable' => false,
            'sortable'   => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'full_name',
            'label'      => trans('admin::app.datagrid.name'),
            'type'       => 'string',
            'searchable' => true,
            'sortable'   => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'email',
            'label'      => trans('admin::app.datagrid.email'),
            'type'       => 'string',
            'searchable' => true,
            'sortable'   => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'name',
            'label'      => trans('admin::app.datagrid.group'),
            'type'       => 'string',
            'searchable' => false,
            'sortable'   => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'phone',
            'label'      => trans('admin::app.datagrid.phone'),
            'type'       => 'number',
            'searchable' => true,
            'sortable'   => true,
            'filterable' => false,
            'closure'    => true,
            'wrapper'    => function ($row) {
                if (! $row->phone) {
                    return '-';
                } else {
                    return $row->phone;
                }
            },
        ]);

        $this->addColumn([
            'index'      => 'gender',
            'label'      => trans('admin::app.datagrid.gender'),
            'type'       => 'string',
            'searchable' => false,
            'sortable'   => true,
            'filterable' => false,
            'closure'    => true,
            'wrapper'    => function ($row) {
                if (! $row->gender) {
                    return '-';
                } else {
                    return $row->gender;
                }
            },
        ]);

        $this->addColumn([
            'index'      => 'status',
            'label'      => trans('admin::app.datagrid.status'),
            'type'       => 'boolean',
            'searchable' => false,
            'sortable'   => true,
            'filterable' => true,
            'closure'    => true,
            'wrapper'    => function ($row) {
                if ($row->status == 1) {
                    return '<span class="badge badge-md badge-success">'. trans('admin::app.customers.customers.active') .'</span>';
                } else {
                    return '<span class="badge badge-md badge-danger">'. trans('admin::app.customers.customers.inactive') .'</span>';
                }
            },
        ]);
    }

    public function prepareActions()
    {
        $this->addAction([
            'method' => 'GET',
            'route'  => 'admin.customer.edit',
            'icon'   => 'icon pencil-lg-icon',
            'title'  => trans('admin::app.customers.customers.edit-help-title'),
        ]);

        $this->addAction([
            'type'   => 'Edit',
            'method' => 'GET',
            'route'  => 'admin.customer.addresses.index',
            'icon'   => 'icon list-icon',
            'title'  => trans('admin::app.customers.customers.addresses'),
        ]);

        $this->addAction([
            'method' => 'POST',
            'route'  => 'admin.customer.delete',
            'icon'   => 'icon trash-icon',
            'title'  => trans('admin::app.customers.customers.delete-help-title'),
        ]);

        $this->addAction([
            'method' => 'GET',
            'route'  => 'admin.customer.note.create',
            'icon'   => 'icon note-icon',
            'title'  => trans('admin::app.customers.note.help-title'),
        ]);
    }

    /**
     * Customer Mass Action To Delete And Change Their
     */
    public function prepareMassActions()
    {
        $this->addMassAction([
            'type'   => 'delete',
            'label'  => trans('admin::app.datagrid.delete'),
            'action' => route('admin.customer.mass-delete'),
            'method' => 'PUT',
        ]);

        $this->addMassAction([
            'type'    => 'update',
            'label'   => trans('admin::app.datagrid.update-status'),
            'action'  => route('admin.customer.mass-update'),
            'method'  => 'PUT',
            'options' => [
                'Active'   => 1,
                'Inactive' => 0,
            ],
        ]);
    }
}