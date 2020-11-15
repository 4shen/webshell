<?php

namespace Webkul\Core\Http\Controllers;

use Illuminate\Support\Facades\Event;
use Webkul\Core\Repositories\LocaleRepository;

class LocaleController extends Controller
{
    /**
     * Contains route related configuration
     *
     * @var array
     */
    protected $_config;

    /**
     * LocaleRepository object
     *
     * @var \Webkul\Core\Repositories\LocaleRepository
     */
    protected $localeRepository;

    /**
     * Create a new controller instance.
     *
     * @param  \Webkul\Core\Repositories\LocaleRepository  $localeRepository
     * @return void
     */
    public function __construct(LocaleRepository $localeRepository)
    {
        $this->localeRepository = $localeRepository;

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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
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
        $this->validate(request(), [
            'code'      => ['required', 'unique:locales,code', new \Webkul\Core\Contracts\Validations\Code],
            'name'      => 'required',
            'direction' => 'in:ltr,rtl',
        ]);

        Event::dispatch('core.locale.create.before');

        $locale = $this->localeRepository->create(request()->all());

        Event::dispatch('core.locale.create.after', $locale);

        session()->flash('success', trans('admin::app.settings.locales.create-success'));

        return redirect()->route($this->_config['redirect']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $locale = $this->localeRepository->findOrFail($id);

        return view($this->_config['view'], compact('locale'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        $this->validate(request(), [
            'code'      => ['required', 'unique:locales,code,' . $id, new \Webkul\Core\Contracts\Validations\Code],
            'name'      => 'required',
            'direction' => 'in:ltr,rtl',
        ]);

        Event::dispatch('core.locale.update.before', $id);

        $locale = $this->localeRepository->update(request()->all(), $id);

        Event::dispatch('core.locale.update.after', $locale);

        session()->flash('success', trans('admin::app.settings.locales.update-success'));

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
        $locale = $this->localeRepository->findOrFail($id);

        if ($this->localeRepository->count() == 1) {
            session()->flash('warning', trans('admin::app.settings.locales.last-delete-error'));
        } else {
            try {
                Event::dispatch('core.locale.delete.before', $id);

                $this->localeRepository->delete($id);

                Event::dispatch('core.locale.delete.after', $id);

                session()->flash('success', trans('admin::app.settings.locales.delete-success'));

                return response()->json(['message' => true], 200);
            } catch(\Exception $e) {
                session()->flash('error', trans('admin::app.response.delete-failed', ['name' => 'Locale']));
            }
        }

        return response()->json(['message' => false], 400);
    }
}