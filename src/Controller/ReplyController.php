<?php

namespace App\Controller;

use App\Entity\Reply;
use App\Entity\Post;
use App\Form\ReplyType;
use App\Repository\ReplyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use \DateTimeImmutable;


#[Route('/reply')]
final class ReplyController extends AbstractController
{
    #[Route('/new/{id}', name: 'app_reply_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, Post $post): Response
    {
        //securité pour verifier le role du visiteur
        $this->denyAccessUnlessGranted('ROLE_USER');
        $reply = new Reply();
        $reply->setPost($post);
        $form = $this->createForm(ReplyType::class, $reply);
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
                $reply->setImg($newFilename);
            }
            $reply->setUser($this->getUser());

            //ajoute la date de la creation du post
            $reply->setDate(new DateTimeImmutable('today'));

            //ajoute un VoteScore de 0 a la creation du post
            $reply->setVoteScore(0);
            $entityManager->persist($reply);
            $entityManager->flush();

            return $this->redirectToRoute('home', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reply/new.html.twig', [
            'reply' => $reply,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reply_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reply $reply, EntityManagerInterface $entityManager): Response
    {
        //securité pour verifier le role du visiteur
    $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $form = $this->createForm(ReplyType::class, $reply);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_reply_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reply/edit.html.twig', [
            'reply' => $reply,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reply_delete', methods: ['POST'])]
    public function delete(Request $request, Reply $reply, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $reply->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($reply);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reply_index', [], Response::HTTP_SEE_OTHER);
    }
}
