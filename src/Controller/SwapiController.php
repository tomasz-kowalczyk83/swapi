<?php

namespace App\Controller;

use App\Swapi\SwapiClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SwapiController extends AbstractController
{
    /**
     * @Route("/swapi", name="swapi")
     */
    public function index(Request $request)
    {
        $swapi = new SwapiClient();
        $people = $swapi->getPeople($request->query->get('page'));

        return new JsonResponse($people);
    }

    public function download(Request $request)
    {
        $content = json_decode($request->getContent(), true);

        foreach($content as $row) {
            $data = array($row['name'], $row['height'], $row['mass']);
            $rows[] = implode(',', $data);
        }

        $content = implode("\n", $rows);

        $response = new Response($content);
        $response->headers->set('Content-Type', 'text/csv');

        return $response;
    }
}
