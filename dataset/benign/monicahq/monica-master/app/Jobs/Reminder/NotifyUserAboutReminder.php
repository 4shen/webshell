<?php

namespace App\Jobs\Reminder;

use Illuminate\Bus\Queueable;
use App\Helpers\AccountHelper;
use App\Notifications\UserNotified;
use App\Notifications\UserReminded;
use App\Interfaces\MailNotification;
use App\Models\Contact\ReminderOutbox;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Notification;

class NotifyUserAboutReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var ReminderOutbox
     */
    protected $reminderOutbox;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(ReminderOutbox $reminderOutbox)
    {
        $this->reminderOutbox = $reminderOutbox;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // prepare the notification to be sent
        $message = $this->getMessage();

        if (! is_null($message)) {
            // send the notification to this user
            $account = $this->reminderOutbox->user->account;
            $hasLimitations = AccountHelper::hasLimitations($account);
            if (! $hasLimitations) {
                Notification::send($this->reminderOutbox->user, $message);
            }

            // create the Reminder Sent object
            $this->reminderOutbox->logSent($message);

            // schedule the next reminder for this user
            /** @var \App\Models\Contact\Reminder */
            $reminder = $this->reminderOutbox->reminder;
            if ($reminder->frequency_type == 'one_time') {
                $reminder->inactive = true;
                $reminder->save();
            } else {
                $reminder->schedule($this->reminderOutbox->user);
            }
        }

        // delete the reminder outbox
        $this->reminderOutbox->delete();
    }

    /**
     * Get message to send.
     *
     * @return MailNotification|null
     */
    private function getMessage(): ?MailNotification
    {
        switch ($this->reminderOutbox->nature) {
            case 'reminder':
                return new UserReminded($this->reminderOutbox->reminder);
            case 'notification':
                return new UserNotified($this->reminderOutbox->reminder, $this->reminderOutbox->notification_number_days_before);
            default:
                return null;
        }
    }
}
