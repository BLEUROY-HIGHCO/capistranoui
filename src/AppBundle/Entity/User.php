<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table()
 */
class User extends BaseUser
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
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $firstName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $lastName;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Environment", mappedBy="users")
     */
    protected $environments;

    /**
     * @var Version[]|Collection
     * @ORM\OneToMany(targetEntity="Version", mappedBy="deployedBy")
     */
    protected $deployedVersions;

    /**
     * @var Version[]|Collection
     * @ORM\OneToMany(targetEntity="Version", mappedBy="rolledBackBy")
     */
    protected $rolledBackVersions;

    //</editor-fold>

    public function __construct()
    {
        parent::__construct();
        $this->environments = new ArrayCollection();
        $this->deployedVersions = new ArrayCollection();
        $this->rolledBackVersions = new ArrayCollection();
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
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }
    //</editor-fold>

    //<editor-fold desc="Setters">
    /**
     * @param string $firstName
     *
     * @return User
     */
    public function setFirstName(string $firstName): User
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @param string $lastName
     *
     * @return User
     */
    public function setLastName(string $lastName): User
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function addEnvironment(Environment $environment)
    {
        if (!$this->environments->contains($environment)) {
            $this->environments->add($environment);
            $environment->addUser($this);
        }

        return $this;
    }

    public function removeEnvironment(Environment $environment)
    {
        if ($this->environments->removeElement($environment)) {
            $environment->removeUser($this);
        }

        return $this;
    }
    
    public function addDeployedVersion(Version $deployedVersion)
    {
        if (!$this->deployedVersions->contains($deployedVersion)) {
            $this->deployedVersions->add($deployedVersion);
            $deployedVersion->setDeployedBy($this);
        }

        return $this;
    }

    public function removeDeployedVersion(Version $deployedVersion)
    {
        if ($this->deployedVersions->removeElement($deployedVersion)) {
            $deployedVersion->setDeployedBy(null);
        }

        return $this;
    }

    public function addRolledBackVersion(Version $rolledBackVersion)
    {
        if (!$this->rolledBackVersions->contains($rolledBackVersion)) {
            $this->rolledBackVersions->add($rolledBackVersion);
            $rolledBackVersion->setRolledBackBy($this);
        }

        return $this;
    }

    public function removeRolledBackVersion(Version $rolledBackVersion)
    {
        if ($this->rolledBackVersions->removeElement($rolledBackVersion)) {
            $rolledBackVersion->setRolledBackBy(null);
        }

        return $this;
    }
    
    //</editor-fold>

    /**
     * User as string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getUsername();
    }

}
