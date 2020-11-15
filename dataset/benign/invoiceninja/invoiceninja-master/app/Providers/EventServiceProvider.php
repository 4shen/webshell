<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [

        // Clients
        'App\Events\ClientWasCreated' => [
            'App\Listeners\ActivityListener@createdClient',
            'App\Listeners\SubscriptionListener@createdClient',
        ],
        'App\Events\ClientWasArchived' => [
            'App\Listeners\ActivityListener@archivedClient',
        ],
        'App\Events\ClientWasUpdated' => [
            'App\Listeners\SubscriptionListener@updatedClient',
        ],
        'App\Events\ClientWasDeleted' => [
            'App\Listeners\ActivityListener@deletedClient',
            'App\Listeners\SubscriptionListener@deletedClient',
            'App\Listeners\HistoryListener@deletedClient',
        ],
        'App\Events\ClientWasRestored' => [
            'App\Listeners\ActivityListener@restoredClient',
        ],

        // Invoices
        'App\Events\InvoiceWasCreated' => [
            'App\Listeners\ActivityListener@createdInvoice',
            'App\Listeners\InvoiceListener@createdInvoice',
        ],
        'App\Events\InvoiceWasUpdated' => [
            'App\Listeners\ActivityListener@updatedInvoice',
            'App\Listeners\InvoiceListener@updatedInvoice',
        ],
        'App\Events\InvoiceItemsWereCreated' => [
            'App\Listeners\SubscriptionListener@createdInvoice',
        ],
        'App\Events\InvoiceItemsWereUpdated' => [
            'App\Listeners\SubscriptionListener@updatedInvoice',
        ],
        'App\Events\InvoiceWasArchived' => [
            'App\Listeners\ActivityListener@archivedInvoice',
        ],
        'App\Events\InvoiceWasDeleted' => [
            'App\Listeners\ActivityListener@deletedInvoice',
            'App\Listeners\TaskListener@deletedInvoice',
            'App\Listeners\ExpenseListener@deletedInvoice',
            'App\Listeners\HistoryListener@deletedInvoice',
            'App\Listeners\SubscriptionListener@deletedInvoice',
        ],
        'App\Events\InvoiceWasRestored' => [
            'App\Listeners\ActivityListener@restoredInvoice',
        ],
        'App\Events\InvoiceWasEmailed' => [
            'App\Listeners\InvoiceListener@emailedInvoice',
            'App\Listeners\NotificationListener@emailedInvoice',
        ],
        'App\Events\InvoiceInvitationWasEmailed' => [
            'App\Listeners\ActivityListener@emailedInvoice',
        ],
        'App\Events\InvoiceInvitationWasViewed' => [
            'App\Listeners\ActivityListener@viewedInvoice',
            'App\Listeners\NotificationListener@viewedInvoice',
            'App\Listeners\InvoiceListener@viewedInvoice',
        ],

        // Quotes
        'App\Events\QuoteWasCreated' => [
            'App\Listeners\ActivityListener@createdQuote',
        ],
        'App\Events\QuoteWasUpdated' => [
            'App\Listeners\ActivityListener@updatedQuote',
        ],
        'App\Events\QuoteItemsWereCreated' => [
            'App\Listeners\SubscriptionListener@createdQuote',
        ],
        'App\Events\QuoteItemsWereUpdated' => [
            'App\Listeners\SubscriptionListener@updatedQuote',
        ],
        'App\Events\QuoteWasArchived' => [
            'App\Listeners\ActivityListener@archivedQuote',
        ],
        'App\Events\QuoteWasDeleted' => [
            'App\Listeners\ActivityListener@deletedQuote',
            'App\Listeners\HistoryListener@deletedQuote',
            'App\Listeners\SubscriptionListener@deletedQuote',
        ],
        'App\Events\QuoteWasRestored' => [
            'App\Listeners\ActivityListener@restoredQuote',
        ],
        'App\Events\QuoteWasEmailed' => [
            'App\Listeners\QuoteListener@emailedQuote',
            'App\Listeners\NotificationListener@emailedQuote',
        ],
        'App\Events\QuoteInvitationWasEmailed' => [
            'App\Listeners\ActivityListener@emailedQuote',
        ],
        'App\Events\QuoteInvitationWasViewed' => [
            'App\Listeners\ActivityListener@viewedQuote',
            'App\Listeners\NotificationListener@viewedQuote',
            'App\Listeners\QuoteListener@viewedQuote',
        ],
        'App\Events\QuoteInvitationWasApproved' => [
            'App\Listeners\ActivityListener@approvedQuote',
            'App\Listeners\NotificationListener@approvedQuote',
            'App\Listeners\SubscriptionListener@approvedQuote',
        ],

        // Payments
        'App\Events\PaymentWasCreated' => [
            'App\Listeners\ActivityListener@createdPayment',
            'App\Listeners\SubscriptionListener@createdPayment',
            'App\Listeners\InvoiceListener@createdPayment',
            'App\Listeners\NotificationListener@createdPayment',
            'App\Listeners\AnalyticsListener@trackRevenue',
        ],
        'App\Events\PaymentWasArchived' => [
            'App\Listeners\ActivityListener@archivedPayment',
        ],
        'App\Events\PaymentWasDeleted' => [
            'App\Listeners\ActivityListener@deletedPayment',
            'App\Listeners\InvoiceListener@deletedPayment',
            'App\Listeners\CreditListener@deletedPayment',
            'App\Listeners\SubscriptionListener@deletedPayment',
        ],
        'App\Events\PaymentWasRefunded' => [
            'App\Listeners\ActivityListener@refundedPayment',
            'App\Listeners\InvoiceListener@refundedPayment',
        ],
        'App\Events\PaymentWasVoided' => [
            'App\Listeners\ActivityListener@voidedPayment',
            'App\Listeners\InvoiceListener@voidedPayment',
        ],
        'App\Events\PaymentFailed' => [
            'App\Listeners\ActivityListener@failedPayment',
            'App\Listeners\InvoiceListener@failedPayment',
        ],
        'App\Events\PaymentWasRestored' => [
            'App\Listeners\ActivityListener@restoredPayment',
            'App\Listeners\InvoiceListener@restoredPayment',
        ],

        // Credits
        'App\Events\CreditWasCreated' => [
            'App\Listeners\ActivityListener@createdCredit',
        ],
        'App\Events\CreditWasArchived' => [
            'App\Listeners\ActivityListener@archivedCredit',
        ],
        'App\Events\CreditWasDeleted' => [
            'App\Listeners\ActivityListener@deletedCredit',
        ],
        'App\Events\CreditWasRestored' => [
            'App\Listeners\ActivityListener@restoredCredit',
        ],

        // User events
        'App\Events\UserSignedUp' => [
            'App\Listeners\HandleUserSignedUp',
        ],
        'App\Events\UserLoggedIn' => [
            'App\Listeners\HandleUserLoggedIn',
        ],
        'App\Events\UserSettingsChanged' => [
            'App\Listeners\HandleUserSettingsChanged',
        ],

        // Task events
        'App\Events\TaskWasCreated' => [
            'App\Listeners\ActivityListener@createdTask',
            'App\Listeners\SubscriptionListener@createdTask',
        ],
        'App\Events\TaskWasUpdated' => [
            'App\Listeners\ActivityListener@updatedTask',
            'App\Listeners\SubscriptionListener@updatedTask',
        ],
        'App\Events\TaskWasRestored' => [
            'App\Listeners\ActivityListener@restoredTask',
        ],
        'App\Events\TaskWasArchived' => [
            'App\Listeners\ActivityListener@archivedTask',
        ],
        'App\Events\TaskWasDeleted' => [
            'App\Listeners\ActivityListener@deletedTask',
            'App\Listeners\SubscriptionListener@deletedTask',
            'App\Listeners\HistoryListener@deletedTask',
        ],

        // Vendor events
        'App\Events\VendorWasCreated' => [
            'App\Listeners\SubscriptionListener@createdVendor',
        ],
        'App\Events\VendorWasUpdated' => [
            'App\Listeners\SubscriptionListener@updatedVendor',
        ],
        'App\Events\VendorWasDeleted' => [
            'App\Listeners\SubscriptionListener@deletedVendor',
        ],

        // Expense events
        'App\Events\ExpenseWasCreated' => [
            'App\Listeners\ActivityListener@createdExpense',
            'App\Listeners\SubscriptionListener@createdExpense',
        ],
        'App\Events\ExpenseWasUpdated' => [
            'App\Listeners\ActivityListener@updatedExpense',
            'App\Listeners\SubscriptionListener@updatedExpense',
        ],
        'App\Events\ExpenseWasRestored' => [
            'App\Listeners\ActivityListener@restoredExpense',
        ],
        'App\Events\ExpenseWasArchived' => [
            'App\Listeners\ActivityListener@archivedExpense',
        ],
        'App\Events\ExpenseWasDeleted' => [
            'App\Listeners\ActivityListener@deletedExpense',
            'App\Listeners\SubscriptionListener@deletedExpense',
            'App\Listeners\HistoryListener@deletedExpense',
        ],

        // Project events
        'App\Events\ProjectWasDeleted' => [
            'App\Listeners\HistoryListener@deletedProject',
        ],

        // Proposal events
        'App\Events\ProposalWasDeleted' => [
            'App\Listeners\HistoryListener@deletedProposal',
        ],

        'Illuminate\Queue\Events\JobExceptionOccurred' => [
            'App\Listeners\InvoiceListener@jobFailed'
        ],

        //DNS Add A record to Cloudflare
        'App\Events\SubdomainWasUpdated' => [
            'App\Listeners\DNSListener@addDNSRecord'
        ],

        //DNS Remove A record from Cloudflare
        'App\Events\SubdomainWasRemoved' => [
            'App\Listeners\DNSListener@removeDNSRecord'
        ]

        /*
        // Update events
        \Codedge\Updater\Events\UpdateAvailable::class => [
            \Codedge\Updater\Listeners\SendUpdateAvailableNotification::class,
        ],
        */
    ];

    /**
     * Register any other events for your application.
     *
     * @param \Illuminate\Contracts\Events\Dispatcher $events
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
