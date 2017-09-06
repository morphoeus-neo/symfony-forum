<?php
/**
 * Created by PhpStorm.
 * User: formation
 * Date: 06/09/2017
 * Time: 09:11
 */

namespace AppBundle\DataFixtures\ORM;


use AppBundle\Entity\Author;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


class AuthorFixture extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        //création nouvel Autheur

        $encoderFactory = $this->container->get("security.encoder_factory");
        $encoder = $encoderFactory->getEncoder(new Author());
        $password = $encoder->encodePassword("123", null);



        $author = new Author();
        $author
            ->setName("Hugo")
            ->setFirstName("Victor")
            ->setEmail("v.hugo@miserable@toto.fr")
            ->setPassword($password);
        $this->addReference("auteur_1", $author);
        $manager->persist($author);


        $author = new Author();
        $author
            ->setName("Albert")
            ->setFirstName("Heinstein")
            ->setEmail("He.Albert@toto.fr")
            ->setPassword($password);
        $this->addReference("auteur_2", $author);
        $manager->persist($author);


        $author = new Author();
        $author
            ->setName("Emilie")
            ->setFirstName("Fronton")
            ->setEmail("e.fronton@miserable.fr")
            ->setPassword($password);
        $this->addReference("auteur_3", $author);
        $manager->persist($author);


        $author = new Author();
        $author
            ->setName("Chrystel")
            ->setFirstName("Geraldine")
            ->setEmail("chris@miserable.fr")
            ->setPassword($password);
        $this->addReference("auteur_4", $author);
        $manager->persist($author);



        $manager->flush();


    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 2;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}