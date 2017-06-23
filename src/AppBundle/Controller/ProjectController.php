<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Environment;
use AppBundle\Entity\Project;
use AppBundle\Form\ProjectType;
use AppBundle\Service\CapistranoFinder;
use AppBundle\Service\GithubApi;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("project")
 */
class ProjectController extends Controller
{
    /**
     * @Route("/", name="project_index")
     * @Template()
     */
    public function indexAction()
    {
        return [
        ];
    }

    /**
     * @Route("/select-folder", name="project_select_folder")
     * @Template()
     * @return array
     */
    public function selectFolderAction()
    {
        return [
            'folders' => $this->get(CapistranoFinder::class)->listAvailableProject(),
        ];
    }

    /**
     * @Route("/show/{id}", requirements={"id": "\d+"}, name="project_show")
     * @Template()
     * @param Project $project
     *
     * @return array
     */
    public function showAction(Project $project)
    {
        return [
            'project' => $project,
        ];
    }

    /**
     * @Route("/list", name="project_list")
     *
     * @Template()
     */
    public function listAction()
    {
        $projects = $this->getDoctrine()->getRepository('AppBundle:Project')->findAll();
        $request  = $this->get('request_stack')->getMasterRequest();
        $project  = $request->attributes->get('project');
        if (null === $project && $environment = $request->attributes->get('environment')) {
            $project = $environment->getProject();
        }

        return [
            'projects'       => $projects,
            'currentProject' => $project,
        ];
    }

    /**
     * @Route("/new", name="project_new", methods={"GET"})
     * @Template()
     * @param Request $request
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function newAction(Request $request)
    {
        $folder = $request->query->get('folder');

        if (!$folder) {
            throw $this->createNotFoundException();
        }

        if ($project = $this->getDoctrine()->getRepository('AppBundle:Project')->findOneByFolder($folder)) {
            /** @var Project $project */
            return $this->redirectToRoute('project_edit', [
                'id' => $project->getId(),
            ]);
        }

        return [
            'form' => $this->getNewForm($folder)->createView(),
        ];
    }

    /**
     * @Route("/new", name="project_create", methods={"POST"})
     * @Template("AppBundle:Project:new.html.twig")
     * @param Request $request
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createAction(Request $request)
    {
        $form = $this->getForm(new Project());

        if ($this->save($form, $request)) {
            return $this->redirectToRoute('project_show', ['id' => $form->getData()->getId()]);
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/edit/{id}", requirements={"id": "\d+"}, name="project_edit", methods={"GET"})
     * @Template()
     * @param Project $project
     *
     * @return array
     */
    public function editAction(Project $project)
    {
        $project = $this->get(CapistranoFinder::class)->refreshProjectFromConfig($project);
        $this->getDoctrine()->getManager()->flush();

        return [
            'form' => $this->getEditForm($project)->createView(),
        ];
    }

    /**
     * @Route("/edit/{id}", requirements={"id": "\d+"}, name="project_update", methods={"POST"})
     * @Template("AppBundle:Project:edit.html.twig")
     * @param Project $project
     * @param Request $request
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateAction(Project $project, Request $request)
    {
        $form = $this->getEditForm($project);

        if ($this->save($form, $request)) {
            return $this->redirectToRoute('project_show', ['id' => $form->getData()->getId()]);
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @param string $folder
     *
     * @return FormInterface
     */
    private function getNewForm(string $folder)
    {
        $project = $this->get(CapistranoFinder::class)->createProjectFromConfig($folder);

        return $this->getForm($project);
    }

    /**
     * @param Project $project
     *
     * @return FormInterface
     */
    private function getEditForm(Project $project)
    {
        return $this->getForm($project);
    }

    /**
     * @param Project $project
     *
     * @return FormInterface
     */
    private function getForm(Project $project)
    {
        return $this->createForm(ProjectType::class, $project)
                    ->add('submit', SubmitType::class);
    }

    private function save(FormInterface $form, Request $request)
    {
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $project = $form->getData();
            $project = $this->get(CapistranoFinder::class)->refreshProjectFromConfig($project);

            $em = $this->getDoctrine()->getManager();

            $em->persist($project);
            $em->flush();

            return true;
        }

        return false;
    }
}
