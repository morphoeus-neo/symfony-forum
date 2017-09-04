<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Theme;
use AppBundle\Form\ThemeType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/admin")
 * Class AdminController
 * @package AppBundle\Controller
 */
class AdminController extends Controller
{

    /**
     * @Route("/themes", name="admin_themes")
     * @return Response
     */
    public function themeAction(Request $request){
        $repository = $this->getDoctrine()
            ->getRepository("AppBundle:Theme");

        $themeList = $repository->findAll();

        // génération du formulaire
        $theme = new Theme();
        $form = $this->createForm(ThemeType::class, $theme);

        //hydratation de l'entitée
        $form->handleRequest($request);

        // Traitement du formulaire
        if ($form->isSubmitted() and $form->isValid()){
            //persistance de l'entité
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($theme);
            $entityManager->flush();

            // Redirection de la page pour éviter d'envoyer les données postées a l'infini
            return $this->redirectToRoute("admin_themes");
        }

        return $this->render("admin/theme.html.twig",
            ["themeList" => $themeList,
             "themeForm" => $form->createView()
            ]);
    }

}