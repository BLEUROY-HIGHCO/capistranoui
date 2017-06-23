<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation\Uploadable;
use Vich\UploaderBundle\Mapping\Annotation\UploadableField;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\ProjectRepository")
 * @ORM\Table()
 * @Uploadable
 */
class Project
{
    //<editor-fold desc="Members">
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=50)
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(type="string", length=50)
     */
    protected $folder;

    /**
     * @var string
     * @ORM\Column(type="string", length=50)
     */
    protected $githubOwner;

    /**
     * @var string
     * @ORM\Column(type="string", length=50)
     */
    protected $githubProject;

    /**
     * @var string
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $thumb;

    /**
     * @var string
     *
     * @UploadableField(mapping="project_image", fileNameProperty="thumb")
     */
    protected $thumbFile;

    /**
     * @var Environment[]|Collection
     * @ORM\OneToMany(targetEntity="Environment", mappedBy="project", cascade={"persist"})
     */
    protected $environments;

    //</editor-fold>

    public function __construct()
    {
        $this->environments = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name;
    }

    //<editor-fold desc="Getters">

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getFolder(): ?string
    {
        return $this->folder;
    }

    /**
     * @return string
     */
    public function getGithubUrl(): ?string
    {
        return sprintf('https://github.com/%s/%s', $this->githubOwner, $this->githubProject);
    }

    /**
     * @return string
     */
    public function getGithubOwner(): ?string
    {
        return $this->githubOwner;
    }

    /**
     * @return string
     */
    public function getGithubProject(): ?string
    {
        return $this->githubProject;
    }

    /**
     * @return string
     */
    public function getThumb(): ?string
    {
        return $this->thumb;
    }

    /**
     * @return string
     */
    public function getThumbFile(): ?string
    {
        return $this->thumbFile;
    }

    /**
     * @return mixed
     */
    public function getEnvironments()
    {
        return $this->environments;
    }
    //</editor-fold>

    //<editor-fold desc="Setters">
    /**
     * @param  string $name
     *
     * @return self
     */
    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param  string $folder
     *
     * @return self
     */
    public function setFolder($folder): self
    {
        $this->folder = $folder;

        return $this;
    }

    /**
     * @param  string $githubOwner
     *
     * @return self
     */
    public function setGithubOwner($githubOwner): self
    {
        $this->githubOwner = $githubOwner;

        return $this;
    }

    /**
     * @param  string $githubProject
     *
     * @return self
     */
    public function setGithubProject($githubProject): self
    {
        $this->githubProject = $githubProject;

        return $this;
    }

    /**
     * @param  string $thumb
     *
     * @return self
     */
    public function setThumb($thumb): self
    {
        $this->thumb = $thumb;

        return $this;
    }

    /**
     * @param string $thumbFile
     *
     * @return self
     */
    public function setThumbFile(string $thumbFile): self
    {
        $this->thumbFile = $thumbFile;

        return $this;
    }

    public function addEnvironment(Environment $environment): self
    {
        if (!$this->environments->contains($environment)) {
            $this->environments->add($environment);
            $environment->setProject($this);
        }

        return $this;
    }

    public function removeEnvironment(Environment $environment): self
    {
        $this->environments->removeElement($environment);

        return $this;
    }

    /**
     * @param Environment[]|Collection $environments
     *
     * @return Project
     */
    public function setEnvironments($environments): self
    {
        $this->environments = $environments;

        foreach ($this->environments as $environment) {
            $environment->setProject($this);
        }

        return $this;
    }

    //</editor-fold>

    public function getEnvironmentByName(string $name): ?Environment
    {
        foreach ($this->environments as $environment) {
            if ($environment->getName() === $name) {
                return $environment;
            }
        }

        return null;
    }

    /**
     * @param Project $project
     *
     * @return self
     */
    public function mergeUneditableProperties(Project $project): self
    {
        $this->folder = $project->getFolder();
        $this->githubOwner = $project->getGithubOwner();
        $this->githubProject = $project->getGithubProject();

        return $this;
    }
}
