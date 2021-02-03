<?php

namespace App\Controller;

use App\Repository\PostRepository;
use App\Entity\Post;
use App\Entity\User;
use App\Form\PostType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'admin_index')]
    public function index(PostRepository $postRepository): Response
    {
        $this->denyAccessUnlessGranted('USER');

        return $this->render('admin/index.html.twig', [
            'posts' => $postRepository->findBy([], ['id' => 'DESC'])
        ]);
    }

    #[Route('/posts/new', name: 'admin_post_new', methods: ['GET'])]
    public function newPost(): Response
    {
        $this->denyAccessUnlessGranted('USER');
        
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);

        return $this->render('admin/post/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/posts/{id}/edit', name: 'admin_post_edit', methods: ['GET'])]
    public function editPost(Post $post): Response
    {
        $this->denyAccessUnlessGranted('POST', $post);

        $form = $this->createForm(PostType::class, $post);
        
        return $this->render('admin/post/edit.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/user', name: 'admin_user_index', methods: ['GET'])]
    public function userIndex(UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted('ADMIN');
        
        return $this->render('admin/user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/{id}/validation', name: 'user_validation', methods: ['POST'])]
    public function userValidation(User $user)
    {
        $this->denyAccessUnlessGranted('ADMIN');

        $isValid = in_array('ROLE_VALID', $user->getRoles());
        
        if($isValid) {
            $user->setRoles(['ROLE_USER']);
        }else {
            $user->setRoles(['ROLE_VALID']);
        }

        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('admin_user_index');
    }

    #[Route('/{id}', name: 'user_delete', methods: ['DELETE'])]
    public function userDelete(Request $request, UserRepository $userRepository, $id): Response
    {
        $this->denyAccessUnlessGranted('ADMIN');

        $user = $userRepository->find($id);

        if($user) {
            if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->remove($user);
                $entityManager->flush();
            }
        }

        return $this->redirectToRoute('admin_user_index');
    }
    
    #[Route('/change_role', name: 'change_role', methods: ['POST'])]
    public function changeRole(): Response
    {
        $this->denyAccessUnlessGranted('AUTH');

        $user = $this->getUser();
        $isAdmin = $this->isGranted('ROLE_ADMIN');
        $route = '';
        
        if($isAdmin) {
            $user->setRoles(['ROLE_USER']);
            $route = 'home';
        }else {
            $user->setRoles(['ROLE_ADMIN']);
            $route = 'admin_index';
        }

        $this->getDoctrine()->getManager()->flush();

        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);
        $this->get('session')->set('_security_main', serialize($token));

        return $this->redirectToRoute($route);
    }
}
