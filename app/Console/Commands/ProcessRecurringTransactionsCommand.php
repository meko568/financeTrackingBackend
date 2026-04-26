<?php

namespace App\Console\Commands;

use App\Jobs\ProcessRecurringTransactions;
use Illuminate\Console\Command;

class ProcessRecurringTransactionsCommand extends Command
{
    protected $signature = 'transactions:process-recurring';

    protected $description = 'Process all due recurring transactions and create copies';

    public function handle(): int
    {
        $this->info('Dispatching recurring transactions job...');

        ProcessRecurringTransactions::dispatch();

        $this->info('Recurring transactions job dispatched successfully.');

        return Command::SUCCESS;
    }
}
