<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use App\Repository\ContactRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


/**
 * @IsGranted("ROLE_ADMIN")
 */
#[Route('/contact')]
class ContactController extends AbstractController
{
    #[Route('/new', name: 'contact_new', methods: ['POST'])]
    public function new(Request $request): Response
    {
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($contact);
            $entityManager->flush();

            return $this->redirectToRoute('home');
        }
    }


    #[Route('/{id}/edit', name: 'contact_edit', methods: ['POST'])]
    public function edit(Request $request, Contact $contact): Response
    {
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('contact_index');
        }
    }

    #[Route('/{id}', name: 'contact_delete', methods: ['DELETE'])]
    public function delete(Request $request, ContactRepository $contactRepository, $id): Response
    {
        $contact = $contactRepository->find($id);

        if($contact) {
            if ($this->isCsrfTokenValid('delete'.$contact->getId(), $request->request->get('_token'))) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->remove($contact);
                $entityManager->flush();
            }
        }

        return $this->redirectToRoute('admin_contact_index');
    }
}
