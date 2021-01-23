<?php

namespace App\Controller;

use App\Repository\PostRepository;
use App\Entity\Post;
use App\Form\PostType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'admin_index')]
    public function index(PostRepository $postRepository): Response
    {
        return $this->render('admin/index.html.twig', [
            'posts' => $postRepository->findAll()
        ]);
    }

    #[Route('/posts/new', name: 'admin_post_new', methods: ['GET'])]
    public function new(Request $request): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);

        return $this->render('admin/post/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/posts/{id}/edit', name: 'admin_post_edit', methods: ['GET'])]
    public function edit(Post $post): Response
    {
        $form = $this->createForm(PostType::class, $post);
        
        return $this->render('admin/post/edit.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/change_role', name: 'change_role', methods: ['POST'])]
    public function changeRole(): Response
    {
        $user = $this->getUser();
        $isAdmin = $this->isGranted('ROLE_ADMIN');
        $role = [];
        
        if($isAdmin) {
            $role = ['ROLE_USER'];
        }else {
            $role = ['ROLE_ADMIN'];
        }

        $user->setRoles($role);
        $this->getDoctrine()->getManager()->flush();

        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);
        $this->get('session')->set('_security_main', serialize($token));

        return $this->redirectToRoute('admin_index');
    }
}
