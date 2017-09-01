<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Version;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends Controller
{
    /**
     * Display dashboard.
     *
     * @Route("/", name="dashboard")
     *
     * @return Response
     */
    public function indexAction()
    {
        /** @var EntityManager $em */
        $em       = $this->getDoctrine()->getManager();
        $versions = $em->getRepository(Version::class)
                       ->createQueryBuilder('v')
                       ->addOrderBy('v.deployedAt', 'DESC')
                       ->setMaxResults(40)
                       ->getQuery()
                       ->getResult();

        return $this->render('AppBundle:Dashboard:index.html.twig', ['versions' => $versions]);
    }
}
