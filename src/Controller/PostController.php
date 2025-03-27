<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use \DateTimeImmutable;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\Vote;

#[Route('/post')]
final class PostController extends AbstractController
{
    // #[Route(name: 'app_post_index', methods: ['GET'])]
    // public function index(PostRepository $postRepository): Response
    // {
    //     return $this->render('post/index.html.twig', [
    //         'posts' => $postRepository->findAll(),
    //     ]);
    // }

    #[Route('/new', name: 'app_post_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
                $imageFile->move(
                    $this->getParameter('images_directory'),
                    $newFilename
                );
                $post->setImg($newFilename);
            }
            //ajoute l'utilisateur qui creer le post 
            $post->setUser($this->getUser());

            //ajoute la date de la creation du post
            $post->setDate(new DateTimeImmutable('today'));

            //ajoute un VoteScore de 0 a la creation du post
            $post->setVoteScore(0);
            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('home', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('post/new.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_post_show', methods: ['GET'])]
    public function show(Post $post): Response
    {
        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_post_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('home', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('post/edit.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_post_delete', methods: ['POST'])]
    public function delete(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $post->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($post);
            $entityManager->flush();
        }

        return $this->redirectToRoute('home', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/score', name: 'app_post_score', methods: ['POST'])]
    public function updateScore(Request $request, Post $post, EntityManagerInterface $entityManager, Security $security): JsonResponse
    {
        //  recupere l'utilisateur connecté
        $user = $security->getUser(); 
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non connecté'], Response::HTTP_FORBIDDEN);
        }

        //  récupére la valeur envoyé en AJAX (upvote ou downvote)
        $data = json_decode($request->getContent(), true);
        //  +1 pour upvote -1 pour downvote
        $voteValue = $data['vote'] ?? 0; 

        // Vérifie si l'utilisateur a déjà voté
        $existingVote = $entityManager->getRepository(Vote::class)->findOneBy([
            'user' => $user,
            'post' => $post,
        ]);

        if ($existingVote) {
            // Si l'utilisateur clique sur le même vote, il annule son vote
            if ($existingVote->getValue() === $voteValue) {
                $entityManager->remove($existingVote);
                $voteValue = 0; // Annulation du vote
            } else {
                $existingVote->setValue($voteValue);
            }
        } else {
            // Si aucun vote existant, créer un nouveau vote
            $vote = new Vote();
            $vote->setUser($user);
            $vote->setPost($post);
            $vote->setValue($voteValue);
            $entityManager->persist($vote);
        }

        // Recalculer le score total des votes pour ce post
        $totalVotes = $entityManager->getRepository(Vote::class)->getPostScore($post);
        $post->setVoteScore($totalVotes);
        $entityManager->flush();

        return new JsonResponse(['newScore' => $totalVotes]);
    }
}
