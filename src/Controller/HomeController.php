<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use App\Entity\Category;
use App\Entity\Guide;
use App\Form\GuideType;
use App\Repository\CategoryRepository;
use App\Repository\GuideRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Entity\User;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(GuideRepository $guideRepository, PostRepository $postRepository, CategoryRepository $categoryRepository, UserRepository $userRepository): Response
    {
        $date = date("Y-m-d");
        $posts = $postRepository->findAll();
        
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'categories' => $categoryRepository->findAll(),
            'posts' => $posts,
            'almanax' => json_decode(file_get_contents("https://api.dofusdu.de/dofus3/v1/fr/almanax/$date"))
        ]);
    }
}
