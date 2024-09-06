<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Map\Map;
use Symfony\UX\Map\Point;

class MapController extends AbstractController
{
    #[Route('/map', name: 'app_map')]
    public function index(): Response
    {
        $map = (new Map())
            ->center(new Point(48.8566, 2.3522))
            ->Zoom(12)
            ->fitBoundsToMarkers()
        ;
            
        return $this->render('map/index.html.twig', [
            'controller_name' => 'MapController',
        ]);
    }
}
