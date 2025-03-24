<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\CategoryRepository;
use App\Repository\GuideRepository;

final class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(CategoryRepository $categoryRepository, GuideRepository $guideRepository): Response
    {

        //securité pour verifier le role du visiteur
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('dashboard/index.html.twig', [
            'controller_name' => 'DashboardController',
            'categories' => $categoryRepository->findAll(),
            'guides' => $guideRepository->findAll(),
        ]);
    }
}
