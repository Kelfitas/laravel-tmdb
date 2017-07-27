<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Genre;

class ApiGetGenres extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:get:genres';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieves all genres';

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
        $this->api_key = config('app.tmdb_api_key');
        $this->api_url = config('app.tmdb_api_url');

        $res = $this->_get_results();
        $this->_parse_response($res);
    }

    private function _build_api_url(): string {
        return "{$this->api_url}/genre/movie/list"
            . "?api_key={$this->api_key}";
    }

    private function _get_results(): array {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $this->_build_api_url(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return ['error' => 'cURL Error #:' . $err];
        }

        return json_decode($response, true);
    }

    private function _parse_response(array $response) {
        if (!is_array($response) 
            || !isset($response['genres'])
            || empty($response['genres'])
        ) {
            return;
        }

        foreach($response['genres'] as $key => &$result) {
            if (!is_array($result)
                || (!isset($result['id'])
                    && !isset($result['name']))
            ) {
                continue;
            }

            $genre = Genre::where('genre_id', $result['id'])
                ->where('name', $result['name'])
                ->count();

            if ($genre > 0) {
                continue;
            }

            $genre = new Genre;
            $genre->genre_id = $result['id'];
            $genre->name = $result['name'];
            $genre->save();
        }
    }
}
