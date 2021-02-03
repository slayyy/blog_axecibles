<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/contact')]
class ContactController extends AbstractController
{
    #[Route('/new', name: 'contact_new', methods: ['POST'])]
    public function new(Request $request, \Swift_Mailer $mailer): Response
    {
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        $data = $form->getData();
        $name = $data->getName();

        if ($form->isSubmitted() && $form->isValid()) {
            $mail = (new \Swift_Message("${name} - Contact Form"))
                        ->setFrom($data->getEmail())
                        ->setTo('decobert.a78@gmail.com')
                        ->setBody(
                            $this->renderView(
                                'emails/contact.html.twig',
                                ['message' => $data->getMessage()]
                            ),
                            'text/html'
                        );

            $mailer->send($mail);
            return $this->redirectToRoute('home');
        }
    }
}
