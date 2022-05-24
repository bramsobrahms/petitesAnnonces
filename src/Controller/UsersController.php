<?php

namespace App\Controller;

use App\Entity\Annonces;
use App\Form\AnnoncesType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UsersController extends AbstractController
{
    /**
     * @Route("/users", name="app_users")
     */
    public function index(): Response
    {
        return $this->render('users/index.html.twig', [
            'controller_name' => 'UsersController',
        ]);
    }

    /**
     * @Route("/users/annonces/ajout", name="users_annonces_ajout")
     */
    public function addAnnonce(HttpFoundationRequest $request, EntityManagerInterface $em): Response
    {
        $annonce = new Annonces;

        $form = $this->createForm(AnnoncesType::class, $annonce);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $annonce->setUsers($this->getUser());
            $annonce->setActive(false);

            $em->persist($annonce);
            $em->flush();

            return $this->redirectToRoute('app_users');
        }

        return $this->render('users/annonces/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
