<?php

/*
 * This file is part of Cachet.
 *
 * (c) Alt Three Services Limited
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CachetHQ\Cachet\Notifications\Schedule;

use CachetHQ\Cachet\Models\Schedule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\NexmoMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use McCool\LaravelAutoPresenter\Facades\AutoPresenter;

/**
 * This is the new schedule notification class.
 *
 * @author James Brooks <james@alt-three.com>
 */
class NewScheduleNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The schedule.
     *
     * @var \CachetHQ\Cachet\Models\Schedule
     */
    protected $schedule;

    /**
     * Create a new notification instance.
     *
     * @param \CachetHQ\Cachet\Models\Schedule $schedule
     *
     * @return void
     */
    public function __construct(Schedule $schedule)
    {
        $this->schedule = AutoPresenter::decorate($schedule);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     *
     * @return string[]
     */
    public function via($notifiable)
    {
        return ['mail', 'nexmo', 'slack'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $content = trans('notifications.schedule.new.mail.content', [
            'name' => $this->schedule->name,
            'date' => $this->schedule->scheduled_at_formatted,
        ]);

        return (new MailMessage())
            ->subject(trans('notifications.schedule.new.mail.subject'))
            ->markdown('notifications.schedule.new', [
                'content'                => $content,
                'unsubscribeText'        => trans('cachet.subscriber.unsubscribe'),
                'unsubscribeUrl'         => cachet_route('subscribe.unsubscribe', $notifiable->verify_code),
                'manageSubscriptionText' => trans('cachet.subscriber.manage_subscription'),
                'manageSubscriptionUrl'  => cachet_route('subscribe.manage', $notifiable->verify_code),
            ]);
    }

    /**
     * Get the Nexmo / SMS representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return \Illuminate\Notifications\Messages\NexmoMessage
     */
    public function toNexmo($notifiable)
    {
        $content = trans('notifications.schedule.new.sms.content', [
            'name' => $this->schedule->name,
            'date' => $this->schedule->scheduled_at_formatted,
        ]);

        return (new NexmoMessage())->content($content);
    }

    /**
     * Get the Slack representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return \Illuminate\Notifications\Messages\SlackMessage
     */
    public function toSlack($notifiable)
    {
        $content = trans('notifications.schedule.new.slack.content', [
            'name' => $this->schedule->name,
            'date' => $this->schedule->scheduled_at_formatted,
        ]);

        return (new SlackMessage())
                    ->content(trans('notifications.schedule.new.slack.title'))
                    ->attachment(function ($attachment) use ($content) {
                        $attachment->title($content)
                                   ->timestamp($this->schedule->getWrappedObject()->scheduled_at)
                                   ->fields(array_filter([
                                       'ID'     => "#{$this->schedule->id}",
                                       'Status' => $this->schedule->human_status,
                                   ]));
                    });
    }
}
