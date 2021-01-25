<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Repository\PostRepository;
use App\Entity\Post;
use App\Entity\User;
use App\Form\PostType;
use App\Repository\ContactRepository;
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
    /**
     * @IsGranted("ROLE_VALID")
     */
    #[Route('/', name: 'admin_index')]
    public function index(PostRepository $postRepository): Response
    {
        $post = null;

        if($this->isGranted('ROLE_ADMIN')) {
            $post = $postRepository->findBy([], ['id' => 'DESC']);
        }else {
            $post = $postRepository->findBy(['user' => $this->getUser()->getId()]);
        }

        return $this->render('admin/index.html.twig', [
            'posts' => $post
        ]);
    }

    /**
     * @IsGranted("ROLE_VALID")
     */
    #[Route('/posts/new', name: 'admin_post_new', methods: ['GET'])]
    public function newPost(Request $request): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);

        return $this->render('admin/post/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @IsGranted("ROLE_VALID")
     */
    #[Route('/posts/{id}/edit', name: 'admin_post_edit', methods: ['GET'])]
    public function editPost(Post $post, PostRepository $postRepository): Response
    {
        $repo = $postRepository->findBy(["user" => $this->getUser()->getId()]);
        $canEdit = in_array($post, $repo) && $this->isGranted('ROLE_VALID');

        if($canEdit || $this->isGranted('ROLE_ADMIN')) {
            $form = $this->createForm(PostType::class, $post);
            
            return $this->render('admin/post/edit.html.twig', [
                'post' => $post,
                'form' => $form->createView(),
            ]);
        }else {
            return $this->redirectToRoute('admin_index');
        }
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     */
    #[Route('/contact', name: 'admin_contact_index', methods: ['GET'])]
    public function contactIndex(ContactRepository $contactRepository): Response
    {
        return $this->render('admin/contact/index.html.twig', [
            'contacts' => $contactRepository->findAll(),
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     */
    #[Route('/contact/{id}', name: 'admin_contact_show', methods: ['GET'])]
    public function contactShow(Contact $contact): Response
    {
        return $this->render('admin/contact/show.html.twig', [
            'contact' => $contact,
        ]);
    }


    /**
     * @IsGranted("ROLE_ADMIN")
     */
    #[Route('/user', name: 'admin_user_index', methods: ['GET'])]
    public function userIndex(UserRepository $userRepository): Response
    {
        return $this->render('admin/user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     */
    #[Route('/{id}/validation', name: 'user_validation', methods: ['POST'])]
    public function userValidation(User $user)
    {
        $isValid = in_array('ROLE_VALID', $user->getRoles());
        
        if($isValid) {
            $user->setRoles(['ROLE_USER']);
        }else {
            $user->setRoles(['ROLE_VALID']);
        }

        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('admin_user_index');
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     */
    #[Route('/{id}', name: 'user_delete', methods: ['DELETE'])]
    public function userDelete(Request $request, UserRepository $userRepository, $id): Response
    {
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
