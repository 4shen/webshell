<?php

namespace Webkul\Velocity\Http\Controllers\Admin;

use Illuminate\Http\Response;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Velocity\Repositories\ContentRepository;

class ContentController extends Controller
{
    /**
     * ProductRepository object
     *
     * @var \Webkul\Product\Repositories\ProductRepository
    */
    protected $productRepository;

    /**
     * ContentRepository object
     *
     * @var \Webkul\Velocity\Repositories\ContentRepository
    */
    protected $contentRepository;

    /**
     * Create a new controller instance.
     *
     * @param  \Webkul\Product\Repositories\ProductRepository  $productRepository
     * @param  \Webkul\Velocity\Repositories\ContentRepository  $contentRepository
     * @return void
     */
    public function __construct(
        ProductRepository $productRepository,
        ContentRepository $contentRepository
    ) {
        $this->productRepository = $productRepository;

        $this->contentRepository = $contentRepository;

        $this->_config = request('_config');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view($this->_config['view']);
    }

    /**
     * Search for catalog
     *
     * @return \Illuminate\View\View
    */
    public function search()
    {
        $results = [];

        $params = request()->input();

        if (isset($params['query']) && $params['query']) {
            foreach ($this->productRepository->searchProductByAttribute(request()->input('query')) as $row) {
                $results[] = [
                    'id'   => $row->product_id,
                    'name' => $row->name,
                ];
            }
        }
        return response()->json($results);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view($this->_config['view']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        $params = request()->all();

        if (isset($params['products'])) {
            $params['products'] = json_encode($params['products']);
        }

        $this->contentRepository->create($params);

        session()->flash('success', trans('admin::app.response.create-success', ['name' => 'Content Page']));

        return redirect()->route($this->_config['redirect']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $content = $this->contentRepository->findOrFail($id);

        return view($this->_config['view'], compact('content'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Webkul\Product\Http\Requests\ProductForm  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        $params = request()->all();

        if (isset($params['locale']) && isset($params[$params['locale']]['products'])) {
            $params[$params['locale']]['products'] = json_encode($params[$params['locale']]['products']);
        }

        $content = $this->contentRepository->update($params, $id);

        session()->flash('success', trans('admin::app.response.update-success', ['name' => 'Content']));

        return redirect()->route($this->_config['redirect']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $content = $this->contentRepository->findOrFail($id);

        try {
            $this->contentRepository->delete($id);

            session()->flash('success', trans('admin::app.response.delete-success', ['name' => 'Content']));

            return response()->json(['message' => true], 200);
        } catch (\Exception $e) {
            session()->flash('error', trans('admin::app.response.delete-failed', ['name' => 'Content']));
        }

        return response()->json(['message' => false], 400);
    }

    /**
     * Mass Delete the products
     *
     * @return \Illuminate\Http\Response
     */
    public function massDestroy()
    {
        $contentIds = explode(',', request()->input('indexes'));

        foreach ($contentIds as $contentId) {

            $content = $this->contentRepository->find($contentId);

            if (isset($content)) {
                $this->contentRepository->delete($contentId);
            }
        }

        session()->flash('success', trans('velocity::app.admin.contents.mass-delete-success'));

        return redirect()->route($this->_config['redirect']);
    }
}