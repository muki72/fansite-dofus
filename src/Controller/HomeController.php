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
    #[Route('/{sort}', name: 'home', requirements: ['sort' => 'date|votes'])]
    public function index(PostRepository $postRepository, UserRepository $userRepository, CategoryRepository $categoryRepository, $sort = 'date'): Response
    {
        $date = date("Y-m-d");
        // vérifier les paramètres de tri
        if (!in_array($sort, ['date', 'votes'])) {
            $sort = 'date';
        }
        $posts = ($sort === 'votes')
            ? $postRepository->findBy([], ['voteScore' => 'DESC'])
            : $postRepository->findBy([], ['date' => 'DESC']);

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'categories' => $categoryRepository->findAll(),
            'users' => $userRepository->findAll(),
            'posts' => $posts,
            'almanax' => json_decode(file_get_contents("https://api.dofusdu.de/dofus3/v1/fr/almanax/$date")),
            'currentSort' => $sort
        ]);
    }

    #[Route('/rgpd', name: 'home_rgpd')]
    public function mentionsLegales(): Response
    {
        return $this->render('home/rgpd.html.twig');
    }
}
