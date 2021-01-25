<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route('/comment')]
class CommentController extends AbstractController
{
    #[Route('/new', name: 'comment_new', methods: ['GET', 'POST'])]
    public function new(Request $request, PostRepository $postRepository): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        $id = $request->request->get('post_id');
        $post = $postRepository->find($id);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setUser($this->getUser());
            $comment->setPost($post);
            $comment->setIsValid(0);
            $comment->setCreatedAt(new \DateTime());
            $comment->setUpdatedAt(new \DateTime());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('post_show', ['id' => $id]);
        }
    }

    /**
     * @IsGranted("ROLE_VALID")
     */
    #[Route('/{id}', name: 'comment_delete', methods: ['DELETE'])]
    public function delete(Request $request, CommentRepository $commentRepository, PostRepository $postRepository, $id): Response
    {
        $comment = $commentRepository->find($id);
        $userPosts = $postRepository->findBy(['user' => $this->getUser()->getId()]);

        if($comment) {
            if(in_array($comment->getPost(), $userPosts) || $this->isGranted('ROLE_ADMIN')) {
                if ($this->isCsrfTokenValid('delete'.$comment->getId(), $request->request->get('_token'))) {
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->remove($comment);
                    $entityManager->flush();
                }
            }
        }

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @IsGranted("ROLE_VALID")
     */
    #[Route('/{id}/validation', name: 'comment_validation', methods: ['POST'])]
    public function validation(Comment $comment, CommentRepository $commentRepository, PostRepository $postRepository, $id)
    {
        $comment = $commentRepository->find($id);
        $isValid = $comment->getIsValid() == 1;
        $userPosts = $postRepository->findBy(['user' => $this->getUser()->getId()]);

        if($comment) {
            if(in_array($comment->getPost(), $userPosts) || $this->isGranted('ROLE_ADMIN')) {

                if(!$isValid) {
                    $comment->setIsValid(1);
                }else {
                    $comment->setIsValid(0);
                }

                $this->getDoctrine()->getManager()->flush();
            }
        }
        return $this->redirectToRoute('admin_post_edit', ['id' => $comment->getPost()->getId()]);
    }

}
