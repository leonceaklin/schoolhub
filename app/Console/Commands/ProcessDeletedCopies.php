<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Copy;
use Illuminate\Support\Facades\Log;


class ProcessDeletedCopies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'copies:processdeleted';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletedes copies which are set to deleted for a long time forever';

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
        $copies = Copy::where("deleted_on", '<', \Carbon\Carbon::now()->subWeeks(4))->where("status", "deleted")->get();
        foreach($copies as $copy){
          Log::info("Copy deleted forever", ["id" => $copy->id, "uid" => $copy->uid, "owned_by" => $copy->owned_by]);
          $copy->delete();
        }
        return 0;
    }
}
