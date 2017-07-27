<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Movie;
use App\Genre;
use App\MovieGenre;

class ApiGetMovies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:get:movies';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieves movies for current date';

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
        $this->api_remaining_header = config('app.tmdb_api_remaining_header');
        $this->api_limit = max(1, config('app.tmdb_api_limit'));
        
        $this->date = date('Y-m-d');
        $this->page = 1;

        while(true) {
            if ($this->_can_request()) {
                $res = $this->_get_results();
                if ($this->_is_response_error($res)) {
                    break;
                }

                $this->_parse_response($res);
                if ($this->_get_remaining_pages() === 0) {
                    break;
                }

                $this->page++;
            }
            usleep(1000000 / $this->api_limit);
        }
    }

    private function _build_api_url(): string {
        return "{$this->api_url}/discover/movie"
            . "?api_key={$this->api_key}"
            . "&primary_release_date.gte={$this->date}"
            . "&primary_release_date.lte={$this->date}"
            . "&page={$this->page}";
    }

    private function &_get_results(): array {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $this->_build_api_url(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HEADER => true,
        ]);

        $response = curl_exec($curl);
        $header_len = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $headers = substr($response, 0, $header_len);
        $body = substr($response, $header_len);
        $err = curl_error($curl);

        curl_close($curl);

        $this->last_response = [
            'headers' => http_parse_headers($headers),
            'body' => [],
            'error' => null
        ];

        if ($err) {
            $this->last_response['error'] = 'cURL Error #:' . $err;
        } else {
            $this->last_response['body'] = json_decode($body, true);
        }

        return $this->last_response['body'];
    }

    private function _get_remaining_requests(): int {
        $headers = &$this->last_response['headers'];

        return (int)$headers[$this->api_remaining_header];
    }

    private function _can_request(): bool {
        if (!isset($this->last_response)) { // first request
            return true;
        }

        if ($this->_get_remaining_requests() === 0) {
            return false;
        }

        return true;
    }

    private function _get_remaining_pages(): int {
        if (!isset($this->last_response) 
            || !is_array($this->last_response)
            || !isset($this->last_response['body'])
            || !is_array($this->last_response['body'])
            || !isset($this->last_response['body']['total_pages'])
        ) {
            return 0;
        }

        $req_body = &$this->last_response['body'];
        $req_body['total_pages'] = (int)$req_body['total_pages'];

        if ($req_body['total_pages'] > $this->page) {
            return $req_body['total_pages'] - $this->page;
        }
        
        return 0;
    }

    private function _is_response_error(array $response) {
        return isset($response['error']) && !empty($response['error']);
    }

    private function _parse_response(array $response) {
        if (!is_array($response) 
            || !isset($response['results'])
            || empty($response['results'])
        ) {
            return;
        }

        foreach($response['results'] as $key => &$result) {
            if (!is_array($result)
                || (!isset($result['original_title'])
                    && !isset($result['genre_ids']))
            ) {
                continue;
            }

            $movieExists = Movie::where('id', $result['id'])->exists();

            if ($movieExists) {
                continue;
            }

            $movie = new Movie;
            $movie->id = $result['id'];
            $movie->original_title = $result['original_title'];
            $movie->save();

            if (empty($result['genre_ids'])) {
                continue;
            }

            foreach($result['genre_ids'] as &$genre_id) {
                if (!$genre_id) {
                    continue;
                }

                $movie_genre = new MovieGenre;
                $movie_genre->movie_id = $result['id'];
                $movie_genre->genre_id = $genre_id;
                $movie_genre->save();
            }
        }
    }
}
