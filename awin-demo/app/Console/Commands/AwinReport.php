<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AwinReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'awin:report {merchantId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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

        if ($merchantId = $this->argument('merchantId')) {
            $this->info("Generating report for: $merchantId");

            try {

                $merchant = new \App\Merchant($merchantId);
                $transactions = $merchant->getAll();

                printf("%s;%s;%s\n",'date','currency','amount');
                foreach ($transactions as $transaction) {
                    printf("%s;%s;%.2f\n",$transaction->getDate(),'£',$transaction->getAmount());
                }
            }
            catch (\Exception $e) {
                $this->error($e->getMessage());
            }
        }
    }
}
