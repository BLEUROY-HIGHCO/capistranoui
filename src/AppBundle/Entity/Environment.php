<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table()
 */
class Environment
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
     * @var Version
     * @ORM\ManyToOne(targetEntity="Version", cascade={"persist", "remove"})
     */
    protected $currentVersion;

    /**
     * @var int
     * @ORM\Column(type="smallint")
     */
    protected $keepReleases;

    /**
     * @var string
     * @ORM\Column(type="string", length=50)
     */
    protected $defaultBranch;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $branchSelectable;

    /**
     * @var Project
     * @ORM\ManyToOne(targetEntity="Project", inversedBy="environments", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    protected $project;

    /**
     * @var User[]|Collection
     * @ORM\ManyToMany(targetEntity="User", inversedBy="environments")
     * @ORM\JoinTable(name="users_environments")
     */
    protected $users;

    /**
     * @var Version[]|Collection
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Version", mappedBy="environment")
     */
    protected $versions;

    //</editor-fold>

    public function __construct()
    {
        $this->users = new ArrayCollection();
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
     * @return Version|null
     */
    public function getCurrentVersion(): ?Version
    {
        return $this->currentVersion;
    }

    /**
     * @return int
     */
    public function getKeepReleases(): ?int
    {
        return $this->keepReleases;
    }

    /**
     * @return string
     */
    public function getDefaultBranch(): ?string
    {
        return $this->defaultBranch;
    }

    /**
     * @return bool
     */
    public function isBranchSelectable(): ?bool
    {
        return $this->branchSelectable;
    }

    /**
     * @return Project|null
     */
    public function getProject(): ?Project
    {
        return $this->project;
    }

    /**
     * @return User[]|Collection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @return Version[]|Collection
     */
    public function getVersions()
    {
        return $this->versions;
    }
    //</editor-fold>

    //<editor-fold desc="Setters">
    /**
     * @param string $name
     *
     * @return Environment
     */
    public function setName(string $name): Environment
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param string $version
     *
     * @return Environment
     */
    public function setCurrentVersion(string $version): Environment
    {
        $this->currentVersion = $version;

        return $this;
    }

    /**
     * @param int $keepReleases
     *
     * @return Environment
     */
    public function setKeepReleases(int $keepReleases): Environment
    {
        $this->keepReleases = $keepReleases;

        return $this;
    }

    /**
     * @param string $defaultBranch
     *
     * @return Environment
     */
    public function setDefaultBranch(string $defaultBranch): Environment
    {
        $this->defaultBranch = $defaultBranch;

        return $this;
    }

    /**
     * @param bool $branchSelectable
     *
     * @return Environment
     */
    public function setBranchSelectable(bool $branchSelectable): Environment
    {
        $this->branchSelectable = $branchSelectable;

        return $this;
    }

    /**
     * @param Project $project
     *
     * @return Environment
     */
    public function setProject(Project $project): Environment
    {
        $this->project = $project;

        return $this;
    }

    public function addUser(User $user)
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->addEnvironment($this);
        }

        return $this;
    }

    public function removeUser(User $user)
    {
        if ($this->users->removeElement($user)) {
            $user->removeEnvironment($this);
        }

        return $this;
    }

    public function addVersion(Version $version)
    {
        if (!$this->versions->contains($version)) {
            $this->versions->add($version);
            $version->setEnvironment($this);
        }

        return $this;
    }

    public function removeVersion(Version $version)
    {
        if ($this->versions->removeElement($version)) {
            $version->setEnvironment(null);
        }

        return $this;
    }

    //</editor-fold>

    /**
     * @param Environment $environment
     *
     * @return self
     */
    public function mergeUneditableProperties(Environment $environment): self
    {
        $this->keepReleases = $environment->getKeepReleases();
        $this->branchSelectable = $environment->isBranchSelectable();
        $this->defaultBranch = $environment->getDefaultBranch();

        return $this;
    }

    /**
     * @return string
     */
    public function getGithubUrl()
    {
        return sprintf('%s/tree/%s', $this->project->getGithubUrl(), $this->defaultBranch);
    }

    /**
     * @return null|string
     */
    public function getBranchToDeploy()
    {
        return $this->currentVersion && $this->currentVersion->getBranch() ?  $this->currentVersion->getBranch() : $this->getDefaultBranch();
    }
}
