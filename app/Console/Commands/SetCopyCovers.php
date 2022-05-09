<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Copy;

class SetCopyCovers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'copies:setcovers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Sets the cover of all copies to the corresponding item's cover";

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
        $copies = Copy::all();
        foreach($copies as $copy){
          if(isset($copy->_item)){
            $copy->cover = $copy->_item->cover;
            $copy->save();
          }
        }
        return 0;
    }
}
