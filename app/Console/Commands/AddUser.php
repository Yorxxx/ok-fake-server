<?php

namespace App\Console\Commands;

use App\Account;
use App\Agent;
use App\Setting;
use App\Transaction;
use App\User;
use Illuminate\Console\Command;

/**
 * Class AddUser
 * @package App\Console\Commands
 * @codeCoverageIgnore
 */
class AddUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:add';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adds a user manually, with faked account, transactions and agents data';

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
        $name = $this->ask('Specify user name');
        $nif = $this->ask('Specify user NIF?');
        $password = $this->secret('Specify user password');
        $phonenumber = $this->ask('Specify your phonenumber (format +AA-123456789)');
        if ($this->confirm('This will create a user with fake account, contacts and transactions. Proceed?')) {
            try {
                $user = factory(User::class)->create([
                    'name'      => $name,
                    'document'  => $nif,
                    'password'  => bcrypt($password),
                    'phone'     => $phonenumber
                ]);

                $this->line('Feeding data');
                $number_agents = random_int(0, 25);
                $bar = $this->output->createProgressBar($number_agents+3);
                // Adding settings
                factory(Setting::class)->create([
                    'user_id'       => $user->id,
                    'language'      => 'ES'
                ]);
                $bar->advance();

                // Adding account
                $account = factory(Account::class)->create([
                    'user_id'       => $user->id
                ]);
                $bar->advance();

                // Adding agents and transactions
                for ($i = 0;$i<$number_agents; $i++) {
                    $agent = factory(Agent::class)->create([
                        'user_id'       => $user->id
                    ]);
                    factory(Transaction::class)->create([
                        'user_id'           =>  $user->id,
                        'account_source'    =>  $account->id,
                        'agent_destination' =>  $agent->id
                    ]);
                    $bar->advance();
                }
                $bar->finish();
                $this->info("");


                // Show user info
                $headers = ['User ID', 'Name', 'Document', 'DocType', 'Email', 'Phone'];
                $user_values = [$user->id, $user->name, $user->document, $user->doctype, $user->email, $user->phone];
                $this->table($headers, [$user_values]);

                // Show account info
                $account_headers = ['Account ID', 'Number', 'Amount', 'Currency'];
                $account_values = [$account->id, $account->number, $account->amount, $account->currency];
                $this->table($account_headers, [$account_values]);

                // Show agents info
                $agents_headers = ['Contact ID', 'Name', 'Phone', 'Email', 'Country'];
                $agent_values = array();
                $agents = Agent::where('user_id', $user->id)->get();
                foreach ($agents as $agent) {
                    $value = [$agent->id, $agent->name, $agent->phone, $agent->email, $agent->country];
                    array_push($agent_values, $value);
                }
                $this->table($agents_headers, $agent_values);

                // Show transactions info
                $transaction_headers = ['Transaction ID', 'Concept', 'Amount', 'State', 'Agent destination', 'Date Start'];
                $transaction_values = array();
                $transactions = Transaction::where('user_id', $user->id)->get();
                foreach ($transactions as $transaction) {
                    $value = [$transaction->id, $transaction->concept, $transaction->amount_source, $transaction->state, $transaction->agent_destination, $transaction->date_start];
                    array_push($transaction_values, $value);
                }
                $this->table($transaction_headers, $transaction_values);

            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
        }
    }
}
