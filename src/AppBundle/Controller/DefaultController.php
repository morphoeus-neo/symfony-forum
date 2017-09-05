<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Post;
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
        //Création du formulaire "post"
        $post = new Post();
        $form =$this->createForm(PostType::class, $post);

        //hydratation de l'entité Post
        $form->handleRequest($request);
        if($form->isSubmitted() and $form->isValid()){
            $em=$this->getDoctrine()->getManager();
            $em->persist($post);
            $em->flush();

            //Redirection
            return $this->redirectToRoute("homepage");
        }

        return $this->render('default/index.html.twig',
            [
                "themeList" => $list,
                "postList"=>$postListByYear,
                "postForm"=> $form->createView()]);
    }

    /**
     * @Route("/theme/{id}", name="theme_details", requirements={"id":"\d+"})
     * @param $id
     * @return Response
     */
    public function themeAction($id){

        $repository = $this->getDoctrine()
            ->getRepository("AppBundle:Theme");

        $theme = $repository->find($id);

        $allThemes = $repository->getAllThemes()->getArrayResult();

        if(! $theme){
            throw new NotFoundHttpException("Thème introuvable");
        }


        return $this->render('default/theme.html.twig', [
            "theme" => $theme,
            "postList" => $theme->getPosts(),
            "all" => $allThemes
        ]);
    }
}
