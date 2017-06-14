<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table()
 */
class Version
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
    protected $number;

    /**
     * @var string
     * @ORM\Column(type="string", length=50)
     */
    protected $commit;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $deployedAt;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $rolledBackAt;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User", inversedBy="deployedVersions", fetch="EAGER")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $deployedBy;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User", inversedBy="rolledBackVersions", fetch="EAGER")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $rolledBackBy;


    //</editor-fold>

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
    public function getNumber(): string
    {
        return $this->number;
    }

    /**
     * @return string
     */
    public function getCommit(): string
    {
        return $this->commit;
    }

    /**
     * @return \DateTime
     */
    public function getDeployedAt(): ?\DateTime
    {
        return $this->deployedAt;
    }

    /**
     * @return \DateTime
     */
    public function getRolledBackAt(): ?\DateTime
    {
        return $this->rolledBackAt;
    }

    /**
     * @return User
     */
    public function getDeployedBy(): ?User
    {
        return $this->deployedBy;
    }

    /**
     * @return User
     */
    public function getRolledBackBy(): ?User
    {
        return $this->rolledBackBy;
    }
    //</editor-fold>

    //<editor-fold desc="Setters">
    /**
     * @param string $number
     *
     * @return Version
     */
    public function setNumber(string $number): Version
    {
        $this->number = $number;

        return $this;
    }

    /**
     * @param string $commit
     *
     * @return Version
     */
    public function setCommit(string $commit): Version
    {
        $this->commit = $commit;

        return $this;
    }

    /**
     * @param \DateTime $deployedAt
     *
     * @return Version
     */
    public function setDeployedAt(\DateTime $deployedAt): Version
    {
        $this->deployedAt = $deployedAt;

        return $this;
    }

    /**
     * @param \DateTime $rolledBackAt
     *
     * @return Version
     */
    public function setRolledBackAt(\DateTime $rolledBackAt): Version
    {
        $this->rolledBackAt = $rolledBackAt;

        return $this;
    }

    /**
     * @param User $deployedBy
     *
     * @return Version
     */
    public function setDeployedBy(User $deployedBy): Version
    {
        $this->deployedBy = $deployedBy;

        return $this;
    }

    /**
     * @param User $rolledBackBy
     *
     * @return Version
     */
    public function setRolledBackBy(User $rolledBackBy): Version
    {
        $this->rolledBackBy = $rolledBackBy;

        return $this;
    }
    //</editor-fold>
}
