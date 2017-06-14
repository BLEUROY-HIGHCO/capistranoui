<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation\Uploadable;
use Vich\UploaderBundle\Mapping\Annotation\UploadableField;

/**
 * @ORM\Entity
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
     * @ORM\Column(type="string", length=255)
     */
    protected $githubUrl;

    /**
     * @var string
     * @ORM\Column(type="string", length=50)
     */
    protected $thumb;

    /**
     * @var string
     *
     * @UploadableField(mapping="project_image", fileNameProperty="thumb")
     */
    protected $thumbFile;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="Environment", mappedBy="project")
     */
    protected $environments;

    //</editor-fold>

    public function __construct()
    {
        $this->environments = new ArrayCollection();
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
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getFolder(): string
    {
        return $this->folder;
    }

    /**
     * @return string
     */
    public function getGithubUrl(): string
    {
        return $this->githubUrl;
    }

    /**
     * @return string
     */
    public function getThumb(): string
    {
        return $this->thumb;
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
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param  string $folder
     *
     * @return self
     */
    public function setFolder($folder)
    {
        $this->folder = $folder;

        return $this;
    }

    /**
     * @param  string $githubUrl
     *
     * @return self
     */
    public function setGithubUrl($githubUrl)
    {
        $this->githubUrl = $githubUrl;

        return $this;
    }

    /**
     * @param  string $thumb
     *
     * @return self
     */
    public function setThumb($thumb)
    {
        $this->thumb = $thumb;

        return $this;
    }

    public function addEnvironment(Environment $environment)
    {
        if (!$this->environments->contains($environment)) {
            $this->environments->add($environment);
        }

        return $this;
    }

    public function removeEnvironment(Environment $environment)
    {
        $this->environments->removeElement($environment);

        return $this;
    }
    //</editor-fold>
}
