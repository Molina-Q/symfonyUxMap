<?php

namespace App\Controller;

use Symfony\UX\Map\Map;
use Symfony\UX\Map\Point;
use Symfony\UX\Map\Marker;
use Symfony\UX\Map\InfoWindow;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MapController extends AbstractController
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
    ) {}

    #[Route('/map', name: 'app_map')]
    public function index(): Response
    {
        $map = (new Map())
            ->center(new Point(48.8566, 2.3522))
            ->Zoom(6);

        $data = $this->httpClient->request('GET', 'http://localhost:3000/api/poi');
        $decodedData = json_decode($data->getContent(), true);
        $POIS = $decodedData["data"];

        foreach ($POIS as $POI) {
            $map->addMarker(
                new Marker(
                    position: new Point($POI['latitude'], $POI['longitude']),
                    title: $POI["ville"],
                    infoWindow: new InfoWindow(
                        headerContent: '<h2>' . $POI["nom"] . '</h2>',
                        content: '<p>' . $POI["adresse"] . ', ' . $POI["cp"] . '</p>'
                    )
                )
            );
        }

        return $this->render('map/index.html.twig', [
            'map' => $map,
            'poi' => $POI,
        ]);
    }

    #[Route('/search', name: 'app_search_index', methods: ['GET'])]
    public function searchPage(): Response
    {
        return $this->render('map/search.html.twig');
    }

    #[Route('/search/results', name: 'app_search_results', methods: ['GET'])]
    public function searchResults(
        Request $request
    ): Response {
        $query = $request->query->get('results');
        // lat, lon, name, 
        // adress -> city, postcode, road
        $data = $this->httpClient->request('GET', 'https://nominatim.openstreetmap.org/search?q=' . $query . '&format=json&addressdetails=1');

        $decodedData = json_decode($data->getContent(), true);

        // dd($decodedData);

        return $this->render('map/searchResults.html.twig', [
            'results' => $decodedData,
        ]);
    }

    #[Route('/search/for', name: 'app_search', methods: ['POST'])]
    public function search(
        Request $request
    ): Response {
        $formData = $request->request->get("search");

        return $this->redirectToRoute('app_search_results', [
            'results' => $formData,
        ]);
    }

    #[Route('/poi/add', name: 'poi_add', methods: ['POST'])]
    public function addPOI(
        Request $request
    ): Response {
        $query = $request->request->all();

        $POI = [
            'nom' => $query['nom'],
            'ville' => $query['ville'],
            'adresse' => $query['adresse'],
            'cp' => $query['cp'],
            'latitude' => $query['latitude'],
            'longitude' => $query['longitude'],
        ];

        $encodedPOI = json_encode($POI);

        $data = $this->httpClient->request('POST', 'http://localhost:3000/api/poi/create', [
            'body' => $encodedPOI,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);

        $response = json_decode($data->getContent(), true);

        return $this->redirectToRoute('index_map');
    }
}
