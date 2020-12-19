<?php

namespace WebId\Radis\Console\Commands;

use Illuminate\Console\Command;
use WebId\Radis\Hello;

class DeployCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'radis:deploy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deploy a Review App';

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
     * @return int
     */
    public function handle()
    {
        $hello = new Hello();
        dump($hello());

        return 0;
    }
}
