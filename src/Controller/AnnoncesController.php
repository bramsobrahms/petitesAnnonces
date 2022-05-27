<?php

namespace App\Controller;

use App\Entity\Annonces;
use App\Entity\Images;
use App\Form\Annonces1Type;
use App\Repository\AnnoncesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/annonces")
 */
class AnnoncesController extends AbstractController
{
    /**
     * @Route("/", name="app_annonces_index", methods={"GET"})
     */
    public function index(AnnoncesRepository $annoncesRepository): Response
    {
        return $this->render('annonces/index.html.twig', [
            'annonces' => $annoncesRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="app_annonces_new", methods={"GET", "POST"})
     */
    public function new(Request $request, AnnoncesRepository $annoncesRepository): Response
    {
        $annonce = new Annonces();
        $form = $this->createForm(Annonces1Type::class, $annonce);
        $form->handleRequest($request);
        

        if ($form->isSubmitted() && $form->isValid()) {
            //on recupère les imgaes transmises
            $images = $form->get('images')->getData();
            foreach($images as $image){
                //on génère un nouveau nom de fichier
                $fichier = md5(uniqid()).'.'.$image->guessExtension();
                //on copie le fichier dans le dossier upload
                $image->move($this->getParameter('images_directory'), $fichier);
                //on stock l'image dans la DB
                $img = new Images();
                $img->setName($fichier);
                $annonce->addImage($img);
            }

            $annoncesRepository->add($annonce, true);

            return $this->redirectToRoute('app_annonces_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('annonces/new.html.twig', [
            'annonce' => $annonce,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_annonces_show", methods={"GET"})
     */
    public function show(Annonces $annonce): Response
    {
        return $this->render('annonces/show.html.twig', [
            'annonce' => $annonce,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_annonces_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Annonces $annonce, AnnoncesRepository $annoncesRepository): Response
    {
        $form = $this->createForm(Annonces1Type::class, $annonce);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //on recupère les imgaes transmises
            $images = $form->get('images')->getData();
            foreach($images as $image){
                //on génère un nouveau nom de fichier
                $fichier = md5(uniqid()).'.'.$image->guessExtension();
                //on copie le fichier dans le dossier upload
                $image->move($this->getParameter('images_directory'), $fichier);
                //on stock l'image dans la DB
                $img = new Images();
                $img->setName($fichier);
                $annonce->addImage($img);
            }
            $annoncesRepository->add($annonce, true);

            return $this->redirectToRoute('app_annonces_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('annonces/edit.html.twig', [
            'annonce' => $annonce,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_annonces_delete", methods={"POST"})
     */
    public function delete(Request $request, Annonces $annonce, AnnoncesRepository $annoncesRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$annonce->getId(), $request->request->get('_token'))) {
            $annoncesRepository->remove($annonce, true);
        }

        return $this->redirectToRoute('app_annonces_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/deleteImage/{id}", name="annonces_delete_image", methods={"DELETE"})
     */
    public function deleteImage(Images $image, Request $request, EntityManagerInterface $emi)
    {
        $data = json_decode($request->getContent(), true);
        //on verifie si le token est valide
        if($this->isCsrfTokenValid('delete-image'.$image->getId(), $data['_token'])){
            //recupère le nom de l'image
            $nom = $image->getName();
            //supprime le fichier
            unlink($this->getParameter('images_directory').'/'.$nom);
            //on supprime de la base de donnée
            $emi->remove($image);
            $emi->flush();

            //on repond en json
            return new JsonResponse(['success' => 1]);
        }else {
            return new JsonResponse(['error' => 'Token Invalide'], 400);
        }
    }
}
