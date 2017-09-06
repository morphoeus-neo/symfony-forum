<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Author;
use AppBundle\Entity\Post;
use AppBundle\Form\AuthorType;
use AppBundle\Form\PostType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $repository = $this->getDoctrine()
            ->getRepository("AppBundle:Theme");
        $postRepository = $this->getDoctrine()
            ->getRepository("AppBundle:Post");

        $list = $repository->getAllThemes()->getArrayResult();
        $postListByYear = $postRepository->getPostsGroupedByYear();

        //Gestion des nouveaux Posts


        //te renvoi un utilisateur loggué même si celui-ci est annonyme
        $user = $this->getUser();
        $roles = isset($user)?$user->getRoles():[];
        $formView = null;

        if (in_array("ROLE_AUTHOR", $roles)) {


            //Création du formulaire
            $post = new Post();
            $post->setCreatedAt(new \DateTime());
            $post->setAuthor($user);
            $form = $this->createForm(PostType::class, $post);

            //Hydratation de l'entité post
            $form->handleRequest($request);

            //Traitement du formulaire
            if ($form->isSubmitted() and $form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($post);
                $em->flush();

                //Redirection
                return $this->redirectToRoute("homepage");
            }
            $formView=$form->createView();
        }
        //Fin de traitement des nouveaux Posts
        return $this->render('default/index.html.twig',
            [
                "themeList" => $list,
                "postList" => $postListByYear,
                "postForm" => $formView
            ]);



    }

    /**
     * @Route("/theme/{id}", name="theme_details", requirements={"id":"\d+"})
     * @param $id
     * @return Response
     */
    public function themeAction($id)
    {

        $repository = $this->getDoctrine()
            ->getRepository("AppBundle:Theme");

        $theme = $repository->find($id);

        $allThemes = $repository->getAllThemes()->getArrayResult();

        if (!$theme) {
            throw new NotFoundHttpException("Thème introuvable");
        }


        return $this->render('default/theme.html.twig', [
            "theme" => $theme,
            "postList" => $theme->getPosts(),
            "all" => $allThemes
        ]);
    }

    /**
     * @Route("/inscription", name="author_registration")
     * @param Request $request
     * @return Response
     */
    public function registrationAction(Request $request)
    {

        $author = new Author();

        $form = $this->createForm(
            AuthorType::class,
            $author
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            //Encodage du mot de passe
            $encoderFactory = $this->get("security.encoder_factory");
            $encoder = $encoderFactory->getEncoder($author);
            $author->setPassword($encoder->encodePassword($author->getPlainPassword(), null));
            $author->setPlainPassword(null);

            //Enregistrement dans la base de données
            $em->persist($author);
            $em->flush();

        }

        return $this->render("default/author-registration.html.twig", [
            "registrationForm" => $form->createView()
        ]);
    }

    /**
     * @Route("/author-login", name="author_login")
     * @return Response
     */
    public function authorLoginAction()
    {

        $securityUtils = $this->get("security.authentication_utils");

        return $this->render(":default:generic-login.html.twig",
            [
                "title" => "Identification des auteurs",
                "action" => $this->generateUrl("author_login_check"),
                "userName" => $securityUtils->getLastUsername(),
                "error" => $securityUtils->getLastAuthenticationError()
            ]
        );
    }
}
