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
     * @var string
     * @ORM\Column(type="string", length=50)
     */
    protected $version;

    /**
     * @var int
     * @ORM\Column(type="smallint")
     */
    protected $keepReleases;

    /**
     * @var Project
     * @ORM\ManyToOne(targetEntity="Project", inversedBy="environments", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $project;

    /**
     * @var User[]|Collection
     * @ORM\ManyToMany(targetEntity="User", inversedBy="environments")
     * @ORM\JoinTable(name="users_environments")
     */
    protected $users;

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
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @return int
     */
    public function getKeepReleases(): int
    {
        return $this->keepReleases;
    }

    /**
     * @return Project
     */
    public function getProject(): Project
    {
        return $this->project;
    }

    /**
     * @return User[]
     */
    public function getUsers()
    {
        return $this->users;
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
    public function setVersion(string $version): Environment
    {
        $this->version = $version;

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
    //</editor-fold>
}
