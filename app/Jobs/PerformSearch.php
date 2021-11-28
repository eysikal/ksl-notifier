<?php

namespace App\Jobs;

use App\Models\Search;
use Goutte\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PerformSearch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const KSL_SEARCH_URL = 'https://classifieds.ksl.com/search/keyword/';
    private $searchWithUserAndFrequency;
    private $client;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Search $searchWithUserAndFrequency)
    {
        $this->searchWithUserAndFrequency = $searchWithUserAndFrequency;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // peform search
        $this->performSearch();

        // calc new next_search based on frequency

        // if new resuls, trrigger notification
    }

    private function performSearch()
    {
        $this->client = new Client();
        $crawler = $this->client->request('GET', self::KSL_SEARCH_URL . $this->searchWithUserAndFrequency->search_string);
        $results = collect(
            $crawler->filter('.item-info-title-link a')->extract(['href'])
        );

        $resultsDiff = $results->diff($this->previousResults());
        if ($resultsDiff->count() > 0) {
            // notify user
            


            // save new results
            $this->searchWithUserAndFrequency->results = json_encode($results);
            $this->searchWithUserAndFrequency->save();
        }

        // $newResults = array_diff($resultsList, $previousResultsList);
        // if (count($newResults) > 0 && !$firstRun) {
        //     echo "\n" . 'Found new results!' . "\n";
        //     $newResultsString = "\n\n";
        //     foreach ($newResults as $key => $result) {
        //         $newResultsString .= '#' . ($key + 1) . ' https://classifieds.ksl.com' . $result . "\n\n";
        //     }
        //     $this->sendNotification($newResultsString);
        // }
    }

    private function previousResults()
    {
        return collect(
            json_decode($this->searchWithUserAndFrequency->results)
        );
    }
}
