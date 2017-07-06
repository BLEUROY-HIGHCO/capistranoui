<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Environment;
use AppBundle\Entity\Version;
use AppBundle\Github\ApiClient;
use AppBundle\Security\Voter\DeployVoter;
use AppBundle\Socket\Message;
use AppBundle\Socket\Sender;
use AppBundle\Socket\Socket;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("environment")
 */
class EnvironmentController extends Controller
{
    const TOKEN_ID = 'jliSYMZuGytT19FU';

    /**
     * @Route("/show/{id}", requirements={"id": "\d+"}, name="environment_show")
     * @Template()
     * @param Environment $environment
     *
     * @return array
     */
    public function showAction(Environment $environment)
    {
        return [
            'environment' => $environment,
            'form'        => $this->getForm($environment)->createView(),
        ];
    }

    /**
     * @param Environment $environment
     *
     * @return FormInterface
     */
    private function getForm(Environment $environment)
    {
        return $this->createFormBuilder()
                    ->add('branch', ChoiceType::class, [
                        'multiple' => false,
                        'choices'  => $this->get(ApiClient::class)->getBranchesFromRepository($environment->getProject()),
                        'data'     => $environment->getBranchToDeploy(),
                        'attr'     => [
                            'readonly' => !$environment->isBranchSelectable(),
                        ],
                    ])
                    ->add('submit', SubmitType::class)->getForm();
    }

    /**
     * @Route("/currentVersion/{id}", requirements={"id": "\d+"}, name="environment_current_version")
     * @Template()
     * @param Environment $environment
     *
     * @return array
     */
    public function currentVersionAction(Environment $environment)
    {
        return [
            'version' => $environment->getCurrentVersion(),
        ];
    }

    /**
     * @Route(
     *     "/githubLastCommit/{id}/{branch}",
     *     requirements={"id": "\d+"},
     *     name="project_github_last_commit",
     *     options={"expose": true}
     * )
     * @Template()
     * @param Environment $environment
     *
     * @param string      $branch
     *
     * @return array
     */
    public function githubLastCommitAction(Environment $environment, ?string $branch = null)
    {
        if (null === $branch) {
            $branch = $environment->getBranchToDeploy();
        }

        return [
            'commit' => $this->get(ApiClient::class)->getBranchLastCommit($environment->getProject(), $branch),
        ];
    }

    /**
     * @Route(
     *     "/githubBranchCommits/{id}/{branch}",
     *     requirements={"id": "\d+"},
     *     name="project_github_branch_commits",
     *     options={"expose": true}
     * )
     * @Template()
     * @param Environment $environment
     * @param string      $branch
     *
     * @return array
     */
    public function githubBranchCommitsAction(Environment $environment, ?string $branch = null)
    {
        if (null === $branch) {
            $branch = $environment->getBranchToDeploy();
        }

        return [
            'environment' => $environment,
            'commits'     => $this->get(ApiClient::class)->getLastBranchCommits($environment, $branch),
        ];
    }

    /**
     * @Route(
     *     "/deploy/{id}",
     *     requirements={"id": "\d+"},
     *     methods={"POST"},
     *     name="environment_deploy",
     *     options={"expose": true}
     * )
     *
     * @param Request     $request
     * @param Environment $environment
     *
     * @return Response|\Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function deployAction(Request $request, Environment $environment)
    {
        if ($this->get('security.authorization_checker')->isGranted(DeployVoter::DEPLOY, $environment)) {
            $form = $this->getForm($environment);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                return new Response();
            } else {
                throw new BadRequestHttpException('Invalid form');
            }
        }

        throw $this->createAccessDeniedException();
    }

    /**
     * @Route(
     *     "/rollback/{id}/{token}",
     *     requirements={"id": "\d+"},
     *     methods={"GET"},
     *     name="environment_rollback",
     *     options={"expose": true}
     * )
     *
     * @param Version             $version
     *
     * @param string              $token
     *
     * @return Response|\Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function rollbackAction(Version $version, string $token)
    {
        $environment = $version->getEnvironment();
        if ($this->isCsrfTokenValid(self::TOKEN_ID, $token) && $this->get('security.authorization_checker')->isGranted(DeployVoter::DEPLOY, $environment)) {
            return new Response();
        } else {
            throw new BadRequestHttpException('Invalid token');
        }
    }
}
