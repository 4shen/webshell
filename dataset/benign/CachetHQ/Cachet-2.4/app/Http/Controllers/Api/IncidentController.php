<?php

/*
 * This file is part of Cachet.
 *
 * (c) Alt Three Services Limited
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CachetHQ\Cachet\Http\Controllers\Api;

use CachetHQ\Cachet\Bus\Commands\Incident\CreateIncidentCommand;
use CachetHQ\Cachet\Bus\Commands\Incident\RemoveIncidentCommand;
use CachetHQ\Cachet\Bus\Commands\Incident\UpdateIncidentCommand;
use CachetHQ\Cachet\Models\Incident;
use GrahamCampbell\Binput\Facades\Binput;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class IncidentController extends AbstractApiController
{
    /**
     * Get all incidents.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $incidentVisibility = app(Guard::class)->check() ? 0 : 1;

        $incidents = Incident::where('visible', '>=', $incidentVisibility);

        $incidents->search(Binput::except(['sort', 'order', 'per_page']));

        if ($sortBy = Binput::get('sort')) {
            $direction = Binput::has('order') && Binput::get('order') == 'desc';

            $incidents->sort($sortBy, $direction);
        }

        $incidents = $incidents->paginate(Binput::get('per_page', 20));

        return $this->paginator($incidents, Request::instance());
    }

    /**
     * Get a single incident.
     *
     * @param \CachetHQ\Cachet\Models\Incident $incident
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Incident $incident)
    {
        return $this->item($incident);
    }

    /**
     * Create a new incident.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store()
    {
        try {
            $incident = execute(new CreateIncidentCommand(
                Binput::get('name'),
                Binput::get('status'),
                Binput::get('message', null, false, false),
                (bool) Binput::get('visible', true),
                Binput::get('component_id'),
                Binput::get('component_status'),
                (bool) Binput::get('notify', true),
                (bool) Binput::get('stickied', false),
                Binput::get('occurred_at'),
                Binput::get('template'),
                Binput::get('vars', []),
                Binput::get('meta', [])
            ));
        } catch (QueryException $e) {
            throw new BadRequestHttpException();
        }

        return $this->item($incident);
    }

    /**
     * Update an existing incident.
     *
     * @param \CachetHQ\Cachet\Models\Incident $incident
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Incident $incident)
    {
        try {
            $incident = execute(new UpdateIncidentCommand(
                $incident,
                Binput::get('name'),
                Binput::get('status'),
                Binput::get('message'),
                (bool) Binput::get('visible', true),
                Binput::get('component_id'),
                Binput::get('component_status'),
                (bool) Binput::get('notify', true),
                (bool) Binput::get('stickied', false),
                Binput::get('occurred_at'),
                Binput::get('template'),
                Binput::get('vars', [])
            ));
        } catch (QueryException $e) {
            throw new BadRequestHttpException();
        }

        return $this->item($incident);
    }

    /**
     * Delete an existing incident.
     *
     * @param \CachetHQ\Cachet\Models\Incident $incident
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Incident $incident)
    {
        execute(new RemoveIncidentCommand($incident));

        return $this->noContent();
    }
}
