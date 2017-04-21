<?php

namespace App\Console\Commands;

use App\UpdateTransactionsTask;
use Illuminate\Console\Command;

class UpdateTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transaction:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the transactions state manually';


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $updater = new UpdateTransactionsTask;
        $updater->update();
    }
}
