<?php

/*
 * This file is part of Cachet.
 *
 * (c) Alt Three Services Limited
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CachetHQ\Cachet\Bus\Handlers\Commands\Incident;

use CachetHQ\Cachet\Bus\Commands\Component\UpdateComponentCommand;
use CachetHQ\Cachet\Bus\Commands\Incident\CreateIncidentCommand;
use CachetHQ\Cachet\Bus\Events\Incident\IncidentWasCreatedEvent;
use CachetHQ\Cachet\Bus\Exceptions\Incident\InvalidIncidentTimestampException;
use CachetHQ\Cachet\Bus\Handlers\Traits\StoresMeta;
use CachetHQ\Cachet\Models\Component;
use CachetHQ\Cachet\Models\Incident;
use CachetHQ\Cachet\Models\IncidentTemplate;
use CachetHQ\Cachet\Models\Meta;
use CachetHQ\Cachet\Services\Dates\DateFactory;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Guard;
use Twig\Environment as Twig_Environment;
use Twig\Loader\ArrayLoader as Twig_Loader_Array;

/**
 * This is the create incident command handler.
 *
 * @author James Brooks <james@alt-three.com>
 */
class CreateIncidentCommandHandler
{
    use StoresMeta;

    /**
     * The authentication guard instance.
     *
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $auth;

    /**
     * The date factory instance.
     *
     * @var \CachetHQ\Cachet\Services\Dates\DateFactory
     */
    protected $dates;

    /**
     * Create a new create incident command handler instance.
     *
     * @param \Illuminate\Contracts\Auth\Guard            $auth
     * @param \CachetHQ\Cachet\Services\Dates\DateFactory $dates
     *
     * @return void
     */
    public function __construct(Guard $auth, DateFactory $dates)
    {
        $this->auth = $auth;
        $this->dates = $dates;
    }

    /**
     * Handle the create incident command.
     *
     * @param \CachetHQ\Cachet\Bus\Commands\Incident\CreateIncidentCommand $command
     *
     * @return \CachetHQ\Cachet\Models\Incident
     */
    public function handle(CreateIncidentCommand $command)
    {
        $data = [
            'user_id'  => $this->auth->user()->id,
            'name'     => $command->name,
            'status'   => $command->status,
            'visible'  => $command->visible,
            'stickied' => $command->stickied,
        ];

        if ($template = IncidentTemplate::where('slug', '=', $command->template)->first()) {
            $data['message'] = $this->parseTemplate($template, $command);
        } else {
            $data['message'] = $command->message;
        }

        // Link with the component.
        if ($command->component_id) {
            $data['component_id'] = $command->component_id;
        }

        // The incident occurred at a different time.
        if ($occurredAt = $command->occurred_at) {
            if ($date = $this->dates->create('Y-m-d H:i', $occurredAt)) {
                $data['occurred_at'] = $date;
            } else {
                throw new InvalidIncidentTimestampException("Unable to pass timestamp {$occurredAt}");
            }
        } else {
            $data['occurred_at'] = Carbon::now();
        }

        // Create the incident
        $incident = Incident::create($data);

        // Store any meta?
        if ($meta = $command->meta) {
            $this->storeMeta($command->meta, 'incidents', $incident->id);
        }

        // Update the component.
        if ($component = Component::find($command->component_id)) {
            execute(new UpdateComponentCommand(
                Component::find($command->component_id),
                null,
                null,
                $command->component_status,
                null,
                null,
                null,
                null,
                null,
                false
            ));
        }

        event(new IncidentWasCreatedEvent($this->auth->user(), $incident, (bool) $command->notify));

        return $incident;
    }

    /**
     * Compiles an incident template into an incident message.
     *
     * @param \CachetHQ\Cachet\Models\IncidentTemplate                     $template
     * @param \CachetHQ\Cachet\Bus\Commands\Incident\CreateIncidentCommand $command
     *
     * @return string
     */
    protected function parseTemplate(IncidentTemplate $template, CreateIncidentCommand $command)
    {
        $env = new Twig_Environment(new Twig_Loader_Array([]));
        $template = $env->createTemplate($template->template);

        $vars = array_merge($command->template_vars, [
            'incident' => [
                'name'             => $command->name,
                'status'           => $command->status,
                'message'          => $command->message,
                'visible'          => $command->visible,
                'notify'           => $command->notify,
                'stickied'         => $command->stickied,
                'occurred_at'      => $command->occurred_at,
                'component'        => Component::find($command->component_id) ?: null,
                'component_status' => $command->component_status,
            ],
        ]);

        return $template->render($vars);
    }
}
