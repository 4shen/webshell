<?php

namespace Webkul\Velocity\DataGrids;

use Illuminate\Support\Facades\DB;
use Webkul\Ui\DataGrid\DataGrid;

class ContentDataGrid extends DataGrid
{
    protected $index = 'content_id'; //the column that needs to be treated as index column

    protected $sortOrder = 'desc'; //asc or desc

    public function prepareQueryBuilder()
    {
        $queryBuilder = DB::table('velocity_contents as con')
            ->select('con.id as content_id', 'con_trans.title', 'con.position', 'con.content_type', 'con.status')
            ->leftJoin('velocity_contents_translations as con_trans', function($leftJoin) {
                $leftJoin->on('con.id', '=', 'con_trans.content_id')
                         ->where('con_trans.locale', app()->getLocale());
            })
            ->groupBy('con.id');

        $this->addFilter('content_id', 'con.id');

        $this->setQueryBuilder($queryBuilder);
    }

    public function addColumns()
    {
        $this->addColumn([
            'index'      => 'content_id',
            'label'      => trans('velocity::app.admin.contents.datagrid.id'),
            'type'       => 'number',
            'searchable' => true,
            'sortable'   => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'title',
            'label'      => trans('velocity::app.admin.contents.datagrid.title'),
            'type'       => 'string',
            'searchable' => true,
            'sortable'   => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'position',
            'label'      => trans('velocity::app.admin.contents.datagrid.position'),
            'type'       => 'number',
            'searchable' => true,
            'sortable'   => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'status',
            'label'      => trans('velocity::app.admin.contents.datagrid.status'),
            'type'       => 'boolean',
            'sortable'   => true,
            'searchable' => false,
            'filterable' => true,
            'wrapper'    => function($value) {
                if ($value->status == 1) {
                    return 'Active';
                } else {
                    return 'Inactive';
                }
            },
        ]);

        $this->addColumn([
            'index'      => 'content_type',
            'label'      => trans('velocity::app.admin.contents.datagrid.content-type'),
            'type'       => 'string',
            'sortable'   => true,
            'searchable' => true,
            'filterable' => true,
            'wrapper'    => function($value) {
                if ($value->content_type == 'category') {
                    return 'Category Slug';
                } elseif ($value->content_type == 'link') {
                    return 'Link';
                } elseif ($value->content_type == 'product') {
                    return 'Product';
                } elseif ($value->content_type == 'static') {
                    return 'Static';
                }
            },
        ]);
    }

    public function prepareActions() {
        $this->addAction([
            'type'   => 'Edit',
            'method' => 'GET',
            'route'  => 'velocity.admin.content.edit',
            'icon'   => 'icon pencil-lg-icon',
        ]);

        $this->addAction([
            'type'         => 'Delete',
            'method'       => 'POST',
            'route'        => 'velocity.admin.content.delete',
            'confirm_text' => trans('ui::app.datagrid.massaction.delete', ['resource' => 'content']),
            'icon'         => 'icon trash-icon',
        ]);
    }

    public function prepareMassActions()
    {
        $this->addMassAction([
            'type'   => 'delete',
            'action' => route('velocity.admin.content.mass-delete'),
            'label'  => trans('admin::app.datagrid.delete'),
            'method' => 'DELETE',
        ]);
    }
}
