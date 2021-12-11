<?php

namespace App\Console\Commands;

use App\Jobs\PerformSearch;
use App\Models\Search;
use Illuminate\Console\Command;

class CheckSearches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:check-searches';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loop through searches and perfom those that need it.';

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
        $searchesWithUserAndFrequency = Search::with(['user', 'frequency'])
            ->whereRaw('next_search <= NOW()')
            ->get();

        $searchesWithUserAndFrequency->each(function ($searchWithUserAndFrequency) {
            PerformSearch::dispatch($searchWithUserAndFrequency);
        });

        if ($searchesWithUserAndFrequency->count() === 0) {
            $this->info('No searches needed');
        }

        return 0;
    }
}
