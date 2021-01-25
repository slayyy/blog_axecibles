<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Form\CommentType;
use App\Form\PostType;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route('/post')]
class PostController extends AbstractController
{
    #[Route('/', name: 'post_index', methods: ['GET'])]
    public function index(PostRepository $postRepository): Response
    {
        return $this->render('post/index.html.twig', [
            'posts' => $postRepository->findBy([], ['id' => 'DESC']),
        ]);
    }

    #[Route('/new', name: 'post_new', methods: ['POST'])]
    public function new(Request $request): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setUser($this->getUser());
            $post->setCreatedAt(new \DateTime());
            $post->setUpdatedAt(new \DateTime());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('admin_index');
        }
    }

    #[Route('/{id}', name: 'post_show', methods: ['GET'])]
    public function show(Post $post): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);

        return $this->render('post/show.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @IsGranted("ROLE_VALID")
     */
    #[Route('/{id}/edit', name: 'post_edit', methods: ['POST'])]
    public function edit(Request $request, Post $post, PostRepository $postRepository, $id): Response
    {
        $userPosts = $postRepository->findBy(["user" => $this->getUser()->getId()]);
        $repo = $postRepository->find($id);

        if($repo) {
            if(in_array($post, $userPosts) || $this->isGranted('ROLE_ADMIN')) {
                $form = $this->createForm(PostType::class, $post);
                $form->handleRequest($request);
        
                if ($form->isSubmitted() && $form->isValid()) {
                    $post->setUpdatedAt(new \DateTime());
                    $this->getDoctrine()->getManager()->flush();
                }
            }
        }

        return $this->redirectToRoute('admin_index');
    }

    #[Route('/{id}', name: 'post_delete', methods: ['DELETE'])]
    public function delete(Request $request, Post $post, PostRepository $postRepository, $id): Response
    {
        $userPosts = $postRepository->findBy(['user' => $this->getUser()->getId()]);

        if($postRepository->find($id)) {
            if(in_array($post, $userPosts) || $this->isGranted('ROLE_ADMIN')) {
                if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->remove($post);
                    $entityManager->flush();
                }
            }
        }

        return $this->redirectToRoute('admin_index');
    }
}
