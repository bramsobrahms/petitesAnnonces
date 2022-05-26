<?php

namespace App\Controller\Admin;

use App\Entity\Annonces;
use App\Repository\AnnoncesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

    /**
     * @Route("/admin/annonces", name="admin_annonces_")
     */
class AnnoncesController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(AnnoncesRepository $annonceRepo): Response
    {
        return $this->render('admin/annonces/index.html.twig', [
            'annonces' => $annonceRepo->findAll(),
        ]);
    }

    /**
     * @Route("/activer/{id}", name="active")
     */
    public function activer(Annonces $annonce, EntityManagerInterface $em)
    {
        $annonce->setActive(($annonce->isActive() ?false:true));
        $em->persist($annonce);
        $em->flush();

        return new Response("true");
    }

    /**
     * @Route("/supprimer/{id}", name="delete")
     */
    public function delete(Annonces $annonce, EntityManagerInterface $em)
    {
        $em->remove($annonce);
        $em->flush();

        $this->addFlash('message', 'Annonce supprimée avec succès');
        return $this->redirectToRoute('admin_annonces_home');
    }

}
