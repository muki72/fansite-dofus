<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class PortalsController extends AbstractController
{
    #[Route('/portals', name: 'app_portals')]
    public function index(HttpClientInterface $httpClient): Response
    {
        // blacklist
        $blacklistedServers = ['beta_1'];

        //list des servers
        $response = $httpClient->request('GET', 'https://api.dofus-portals.fr/internal/v1/servers');
        $content = $response->getContent();
        $servers = json_decode($content, true);

        // Filtre servers blacklist
        $filteredServers = array_filter($servers, function ($server) use ($blacklistedServers) {
            return !in_array($server['id'], $blacklistedServers);
        });

        // Passer les données filtrées au template Twig
        return $this->render('portals/index.html.twig', [
            'servers' => $filteredServers,
        ]);
    }

    #[Route('/portals/{serverName}', name: 'app_portals_server_details')]
    public function serverDetails(string $serverName, HttpClientInterface $httpClient): Response
    {
        // Récupérer les détails du serveur spécifique
        $response = $httpClient->request('GET', "https://api.dofus-portals.fr/internal/v1/servers/$serverName/portals");
        $content = $response->getContent();
        $serverDetails = json_decode($content, true);

        // Passer les détails du serveur au template Twig
        return $this->render('portals/server_details.html.twig', [
            'serverName' => $serverName,
            'serverDetails' => $serverDetails,
        ]);
    }
}
