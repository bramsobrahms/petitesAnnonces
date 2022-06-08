<?php

namespace App\Controller;

use App\Form\ContactType;
use App\Form\SearchAnnoncesType;
use App\Repository\AnnoncesRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="app_home")
     */
    public function index(AnnoncesRepository $annoncesRepo, Request $request): Response
    {
        $annonces = $annoncesRepo->findBy(['active' => true], ['created_at' =>'desc'], 5);

        $form = $this->createForm(SearchAnnoncesType::class);
        $search = $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            //search annonce by keyWord
            $annonces = $annoncesRepo->search(
                $search->get('mots')->getData(),
                $search->get('categories')->getData()
            );
        }

        return $this->render('main/index.html.twig', [
            'annonces' => $annonces,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @route("/contact", name="contact")
     */
    public function contact(Request $request, MailerInterface $mailer)
    {
        $form = $this->createForm(ContactType::class);

        $contact = $form->handleRequest($request);

        if($form -> isSubmitted() && $form -> isValid()){
            $email = (new TemplatedEmail())
                ->from($contact->get('email')->getData())
                ->to('Vous@domaine.fr')
                ->subject('Contac depuis le site PetitesAnnonce')
                ->htmlTemplate('emails/contact.html.twig')
                ->context([
                    'mail' => $contact->get('email')->getData(),
                    'sujet' => $contact->get('suject')->getData(),
                    'message' => $contact->get('message')->getData(),
                ])
            ;

            $mailer->send($email);
            $this->addFlash('message', 'Mail de contact envoyé !');
            return $this->redirectToRoute('contact');
        }
        return $this->render('main/contact.html.twig', [
            'form' => $form->createView()
        ]);      
    }

}
