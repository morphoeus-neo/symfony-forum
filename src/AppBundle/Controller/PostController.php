<?php

namespace AppBundle\Controller;


use AppBundle\Form\PostType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use AppBundle\Entity\Post;

class PostController extends Controller
{

    /**
     * @param $slug
     * @Route("/post/{slug}",
     *          name="post_details"
     * )
     * @return Response
     */
    public function detailsAction($slug){

        $repository = $this->getDoctrine()
            ->getRepository("AppBundle:Post");

        /** @var $post Post */
        $post = $repository->findOneBySlug($slug);

        if(! $post){
            throw new NotFoundHttpException("post introuvable");
        }

        return $this->render("post/details.html.twig", [
            "post" => $post,
            "answerList" => $post->getAnswers()
        ]);
    }

    /**
     * @Route("/post-par-annee/{year}", name="post_by_year",
     *     requirements={"year":"\d{4}"})
     * @param $year
     * @return Response
     */
    public function postByYearAction($year){
        $postRepository = $this->getDoctrine()
            ->getRepository("AppBundle:Post");

        return $this->render("default/theme.html.twig", [
            "title" => "Liste des posts par année ({$year})",
            "postList" => $postRepository->getPostsByYear($year)
        ]);
    }

    /**
     * @Route("/post/modif/{id}", name="post_edit")
     * @param Request $request
     * @param Post $post
     * @return Response
     */
    public function editAction(Request $request, Post $post)
    {
        //on récupère l'utilisateur
        $user = $this->getUser();
        //on récupères les roles de l'utilisateur
        $roles = isset($user)?$user->getRoles():[];
        // on récupère l'Id de l'utilisateur
        $userId= isset($user)?$user->getId():null;
        //
        if (!in_array("ROLE_AUTHOR", $roles) || $userId != $post->getAuthor()->getId() )
        {
            throw new AccessDeniedException("Vous n'avez pas les droits pour Modifier ce post");

        }

        // Création du formulaire
        //je passes en argument une entitée déjà hydratéé
        $form = $this->createForm(PostType::class, $post);

        // hydratation de l'entitée
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid())
        {
            //création du formulaire
            $em = $this->getDoctrine()->getManager();
            $em->persist($post);
            $em->flush();

            //Redirection vers le post pour modification
            $this->redirectToRoute(
                "theme_details",
                ["id"=> $post->getTheme()->getId()]);

        }

        // Génération de la vue
        return $this->render("post/edit.html.twig", ["postForm"=>$form->createView()]);
    }

}