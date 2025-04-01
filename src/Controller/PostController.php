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
   
    #[Route('/new', name: 'app_post_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        //securité pour verifier le role du visiteur
        $this->denyAccessUnlessGranted('ROLE_USER');
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


    #[Route('/{id}/edit', name: 'app_post_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        //securité pour verifier le role du visiteur
        $this->denyAccessUnlessGranted('ROLE_USER');
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
        //securité pour verifier le role du visiteur
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        if ($this->isCsrfTokenValid('delete' . $post->getId(), $request->getPayload()->getString('_token'))) {
             $entityManager->remove($post);
             $entityManager->flush();
        }
        

        return $this->redirectToRoute('home', [], Response::HTTP_SEE_OTHER);
    }




    #[Route('/{id}/vote', name: 'app_post_score', methods: ['POST'])]
    public function updateScore(Request $request, Post $post, EntityManagerInterface $entityManager, Security $security): JsonResponse
    {

        $user = $security->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
        $data = json_decode($request->getContent(), true);
        $voteValue = $data['vote'] ?? null; // Récupe du vote (1 ou 0 ou -1)

        if (!in_array($voteValue, [1, 0, -1])) {
            return new JsonResponse(['error' => 'Invalid vote'], Response::HTTP_BAD_REQUEST);
        }



        $voteRepository = $entityManager->getRepository(Vote::class);
        $existingVote = $voteRepository->findOneBy(['user' => $user, 'post' => $post]);

        if ($existingVote) {
            // Annule vote si user clique sur le meme bouton

            if ($existingVote->getValue() === $voteValue) {
                $entityManager->remove($existingVote);
                $post->setVoteScore($post->getVoteScore() - $voteValue);
            } else {
                // Mettre à jour le vote

                $post->setVoteScore($post->getVoteScore() - $existingVote->getValue() + $voteValue);
                $existingVote->setValue($voteValue);
            }
        } else {

            $vote = new Vote();
            $vote->setUser($user);
            $vote->setPost($post);
            $vote->setValue($voteValue);
            $entityManager->persist($vote);
            $post->setVoteScore($post->getVoteScore() + $voteValue);
        }
        $entityManager->persist($post);


        $entityManager->flush();

        return new JsonResponse(['newScore' => $post->getVoteScore()]);
    }
}
