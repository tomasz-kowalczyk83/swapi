<?php

namespace App\Swapi;


class SwapiClient
{
    private $client;
    private $perpage = 9;

    private $people;

    public function __construct()
    {
        $this->client = new \GuzzleHttp\Client(['base_uri' => 'https://swapi.co/api/']);
    }

    public function getPeople(int $page = 1)
    {
        $response = $this->client->request('GET', sprintf("people/?page=%d", $page));

        $body = $response->getBody()->getContents();

        $data = \json_decode($body, true);

        $this->people = $data['results'];

        return $this->paginate($page, $this->people, $data['count']);
    }

    private function paginate($page, $data, $total)
    {
        $start = ($page - 1) * $this->perpage;
        $end = ($this->perpage * $page) - 1;
        $totalPages = ceil((int)$total / $this->perpage);

        return [
            'offset' => $start,
            'page' => $page,
            'perpage' => $this->perpage,
            'prev' => $page > 1 ? $page -1 : false,
            'next' => $page < $totalPages ? $page + 1 : false,
            'total' => $totalPages,
            //'results' => array_slice($data, $start, 9)
            'results' => array_slice($data, 0, 9)
        ];
    }
}
