<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTaskRequest;
use App\Http\Requests\TaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Ninja\Datatables\TaskDatatable;
use App\Ninja\Repositories\InvoiceRepository;
use App\Ninja\Repositories\TaskRepository;
use App\Services\TaskService;
use Auth;
use DropdownButton;
use Input;
use Redirect;
use Request;
use Session;
use URL;
use Utils;
use View;

/**
 * Class TaskController.
 */
class TaskController extends BaseController
{
    /**
     * @var TaskRepository
     */
    protected $taskRepo;

    /**
     * @var TaskService
     */
    protected $taskService;

    /**
     * @var
     */
    protected $entityType = ENTITY_TASK;

    /**
     * @var InvoiceRepository
     */
    protected $invoiceRepo;

    /**
     * TaskController constructor.
     *
     * @param TaskRepository    $taskRepo
     * @param InvoiceRepository $invoiceRepo
     * @param TaskService       $taskService
     */
    public function __construct(
        TaskRepository $taskRepo,
        InvoiceRepository $invoiceRepo,
        TaskService $taskService
    ) {
        // parent::__construct();

        $this->taskRepo = $taskRepo;
        $this->invoiceRepo = $invoiceRepo;
        $this->taskService = $taskService;
    }

    /**
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        return View::make('list_wrapper', [
            'entityType' => ENTITY_TASK,
            'datatable' => new TaskDatatable(),
            'title' => trans('texts.tasks'),
        ]);
    }

    /**
     * @param null $clientPublicId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDatatable($clientPublicId = null, $projectPublicId = null)
    {
        return $this->taskService->getDatatable($clientPublicId, $projectPublicId, Input::get('sSearch'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CreateTaskRequest $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(CreateTaskRequest $request)
    {
        return $this->save($request);
    }

    /**
     * @param $publicId
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function show($publicId)
    {
        Session::reflash();

        return Redirect::to("tasks/{$publicId}/edit");
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param TaskRequest $request
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create(TaskRequest $request)
    {
        $this->checkTimezone();

        $data = [
            'task' => null,
            'clientPublicId' => Input::old('client') ? Input::old('client') : ($request->client_id ?: 0),
            'projectPublicId' => Input::old('project_id') ? Input::old('project_id') : ($request->project_id ?: 0),
            'method' => 'POST',
            'url' => 'tasks',
            'title' => trans('texts.new_task'),
            'timezone' => Auth::user()->account->timezone ? Auth::user()->account->timezone->name : DEFAULT_TIMEZONE,
            'datetimeFormat' => Auth::user()->account->getMomentDateTimeFormat(),
        ];

        $data = array_merge($data, self::getViewModel());

        return View::make('tasks.edit', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param TaskRequest $request
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(TaskRequest $request)
    {
        $this->checkTimezone();
        $task = $request->entity();

        if (! $task) {
            return redirect('/');
        }

        $actions = [];
        if ($task->invoice) {
            $actions[] = ['url' => URL::to("invoices/{$task->invoice->public_id}/edit"), 'label' => trans('texts.view_invoice')];
        } else {
            $actions[] = ['url' => 'javascript:submitAction("invoice")', 'label' => trans('texts.invoice_task')];

            // check for any open invoices
            $invoices = $task->client_id ? $this->invoiceRepo->findOpenInvoices($task->client_id) : [];

            foreach ($invoices as $invoice) {
                $actions[] = ['url' => 'javascript:submitAction("add_to_invoice", '.$invoice->public_id.')', 'label' => trans('texts.add_to_invoice', ['invoice' => e($invoice->invoice_number)])];
            }
        }

        $actions[] = DropdownButton::DIVIDER;
        if (! $task->trashed()) {
            $actions[] = ['url' => 'javascript:submitAction("archive")', 'label' => trans('texts.archive_task')];
            $actions[] = ['url' => 'javascript:onDeleteClick()', 'label' => trans('texts.delete_task')];
        } else {
            $actions[] = ['url' => 'javascript:submitAction("restore")', 'label' => trans('texts.restore_task')];
        }

        $data = [
            'task' => $task,
            'entity' => $task,
            'clientPublicId' => $task->client ? $task->client->public_id : 0,
            'projectPublicId' => $task->project ? $task->project->public_id : 0,
            'method' => 'PUT',
            'url' => 'tasks/'.$task->public_id,
            'title' => trans('texts.edit_task'),
            'actions' => $actions,
            'timezone' => Auth::user()->account->timezone ? Auth::user()->account->timezone->name : DEFAULT_TIMEZONE,
            'datetimeFormat' => Auth::user()->account->getMomentDateTimeFormat(),
        ];

        $data = array_merge($data, self::getViewModel($task));

        return View::make('tasks.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateTaskRequest $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateTaskRequest $request)
    {
        $task = $request->entity();

        return $this->save($request, $task->public_id);
    }

    /**
     * @return array
     */
    private static function getViewModel($task = false)
    {
        return [
            'clients' => Client::scope()->withActiveOrSelected($task ? $task->client_id : false)->with('contacts')->orderBy('name')->get(),
            'account' => Auth::user()->account,
            'projects' => Project::scope()->withActiveOrSelected($task ? $task->project_id : false)->with('client.contacts')->orderBy('name')->get(),
        ];
    }

    /**
     * @param null $publicId
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    private function save($request, $publicId = null)
    {
        $action = Input::get('action');

        if (in_array($action, ['archive', 'delete', 'restore'])) {
            return self::bulk();
        }

        $task = $this->taskRepo->save($publicId, $request->input());

        if (in_array($action, ['invoice', 'add_to_invoice'])) {
            return self::bulk();
        }

        if (request()->wantsJson()) {
            $task->time_log = json_decode($task->time_log);
            return $task->load(['client.contacts', 'project'])->toJson();
        } else {
            if ($publicId) {
                Session::flash('message', trans('texts.updated_task'));
            } else {
                Session::flash('message', trans('texts.created_task'));
            }

            return Redirect::to("tasks/{$task->public_id}/edit");
        }
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulk()
    {
        $action = Input::get('action');
        $ids = Input::get('public_id') ?: (Input::get('id') ?: Input::get('ids'));
        $referer = Request::server('HTTP_REFERER');

        if (in_array($action, ['resume', 'stop'])) {
            $this->taskRepo->save($ids, ['action' => $action]);
            Session::flash('message', trans($action == 'stop' ? 'texts.stopped_task' : 'texts.resumed_task'));
            return $this->returnBulk($this->entityType, $action, $ids);
        } elseif (strpos($action, 'update_status') === 0) {
            list($action, $statusPublicId) = explode(':', $action);
            Task::scope($ids)->update([
                'task_status_id' => TaskStatus::getPrivateId($statusPublicId),
                'task_status_sort_order' => 9999,
            ]);
            Session::flash('message', trans('texts.updated_task_status'));
            return $this->returnBulk($this->entityType, $action, $ids);
        } elseif ($action == 'invoice' || $action == 'add_to_invoice') {
            $tasks = Task::scope($ids)->with('account', 'client', 'project')->orderBy('project_id', 'id')->get();
            $clientPublicId = false;
            $data = [];

            $lastProjectId = false;
            foreach ($tasks as $task) {
                if ($task->client) {
                    if ($task->client->trashed()) {
                        return redirect($referer)->withError(trans('texts.client_must_be_active'));
                    }

                    if (! $clientPublicId) {
                        $clientPublicId = $task->client->public_id;
                    } elseif ($clientPublicId != $task->client->public_id) {
                        return redirect($referer)->withError(trans('texts.task_error_multiple_clients'));
                    }
                }

                if ($task->is_running) {
                    return redirect($referer)->withError(trans('texts.task_error_running'));
                } elseif ($task->invoice_id) {
                    return redirect($referer)->withError(trans('texts.task_error_invoiced'));
                }

                $account = Auth::user()->account;
                $showProject = $lastProjectId != $task->project_id;
                $data[] = [
                    'publicId' => $task->public_id,
                    'description' => $task->present()->invoiceDescription($account, $showProject),
                    'duration' => $task->getHours(),
                    'cost' => $task->getRate(),
                ];
                $lastProjectId = $task->project_id;
            }

            if ($action == 'invoice') {
                return Redirect::to("invoices/create/{$clientPublicId}")->with('tasks', $data);
            } else {
                $invoiceId = Input::get('invoice_id');

                return Redirect::to("invoices/{$invoiceId}/edit")->with('tasks', $data);
            }
        } else {
            $count = $this->taskService->bulk($ids, $action);
            if (request()->wantsJson()) {
                return response()->json($count);
            } else {
                $message = Utils::pluralize($action.'d_task', $count);
                Session::flash('message', $message);

                return $this->returnBulk($this->entityType, $action, $ids);
            }
        }
    }

    private function checkTimezone()
    {
        if (! Auth::user()->account->timezone) {
            $link = link_to('/settings/localization?focus=timezone_id', trans('texts.click_here'), ['target' => '_blank']);
            Session::now('warning', trans('texts.timezone_unset', ['link' => $link]));
        }
    }
}
