<?php

namespace App\Jobs\Banking;

use App\Abstracts\Job;
use App\Models\Banking\Reconciliation;
use App\Models\Banking\Transaction;

class UpdateReconciliation extends Job
{
    protected $reconciliation;

    protected $request;

    /**
     * Create a new job instance.
     *
     * @param  $reconciliation
     * @param  $request
     */
    public function __construct($reconciliation, $request)
    {
        $this->reconciliation = $reconciliation;
        $this->request = $this->getRequestInstance($request);
    }

    /**
     * Execute the job.
     *
     * @return Reconciliation
     */
    public function handle()
    {
        $reconcile = $this->request->get('reconcile');
        $transactions = $this->request->get('transactions');

        $this->reconciliation->reconciled = $reconcile ? 1 : 0;
        $this->reconciliation->save();

        if ($transactions) {
            foreach ($transactions as $key => $value) {
                if (empty($value)) {
                    continue;
                }
 
                $t = explode('_', $key);

                $transaction = Transaction::find($t[1]);
                $transaction->reconciled = 1;
                $transaction->save();
            }
        }

        return $this->reconciliation;
    }
}
