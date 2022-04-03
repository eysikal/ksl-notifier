<?php

namespace App\Jobs;

use App\Models\Search;
use App\Notifications\NewResultsFound;
use Goutte\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class PerformSearch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const KSL_SEARCH_URL = 'https://classifieds.ksl.com/search/keyword/%s/perPage/96';
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
    public function handle(): void
    {
        $this->performSearch();
    }

    private function performSearch(): void
    {
        $searchUrl = sprintf(
            self::KSL_SEARCH_URL,
            urlencode($this->searchWithUserAndFrequency->search_string)
        );

        $this->client = new Client();
        $crawler = $this->client->request('GET', $searchUrl);
        $results = collect(
            $crawler->filter('.item-info-title-link a')->extract(['href'])
        );

        $resultsDiff = $results->diff($this->previousResults());
        if ($resultsDiff->count() > 0) {
            $this->notifyUser($searchUrl);
            $this->searchWithUserAndFrequency->results = json_encode($results);
        }
        
        $this->searchWithUserAndFrequency->next_search = $this->calcNextSearch();
        $this->searchWithUserAndFrequency->save();
    }

    private function previousResults(): Collection
    {
        return collect(
            json_decode($this->searchWithUserAndFrequency->results)
        );
    }

    private function calcNextSearch(): string
    {
        $frequency = $this->searchWithUserAndFrequency->frequency;
        $randomMinutes = rand($frequency->min, $frequency->max);

        return Carbon::now()->addMinutes($randomMinutes);
    }

    private function notifyUser(string $searchUrl): void
    {
        $this->searchWithUserAndFrequency->user->notify(
            new NewResultsFound(
                $this->searchWithUserAndFrequency->search_string,
                $searchUrl
            )
        );
    }
}
