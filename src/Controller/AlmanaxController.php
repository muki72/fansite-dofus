<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AlmanaxController extends AbstractController
{
    #[Route('/almanax', name: 'app_almanax')]
    public function index(): Response
    {
        $from = date("Y-m-d");
        $to = date('Y-m-d', strtotime($from . ' + 29 days'));;
        $almanaxData = json_decode(file_get_contents("https://api.dofusdu.de/dofus3/v1/fr/almanax?range[from]=$from&range[to]=$to&range[size]=-1&timezone=Europe/Paris"));
        
        return $this->render('almanax/index.html.twig', [
            'almanaxData' => $almanaxData,

        ]);
    }
}
