<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use App\Entity\Category;
use App\Entity\Guide;
use App\Form\GuideType;
use App\Repository\GuideRepository;
use App\Entity\User;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(GuideRepository $guideRepository): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'guides' => $guideRepository->findAll(),
        ]);
    }
}
