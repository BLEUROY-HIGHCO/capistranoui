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
     * @var string
     * @ORM\Column(type="string", length=50)
     */
    protected $branch;

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

    /**
     * @var Environment
     * @ORM\ManyToOne(targetEntity="Environment", inversedBy="versions")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $environment;


    //</editor-fold>

    //<editor-fold desc="Getters">
    /**
     * @return mixed
     */
    public function getId(): int
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
     * @return string
     */
    public function getBranch(): string
    {
        return $this->branch;
    }

    /**
     * @return \DateTime
     */
    public function getDeployedAt(): ?\DateTime
    {
        return $this->deployedAt;
    }

    /**
     * @return \DateTime|null
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

    /**
     * @return Environment
     */
    public function getEnvironment(): ?Environment
    {
        return $this->environment;
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
     * @param string $branch
     *
     * @return Version
     */
    public function setBranch(string $branch): Version
    {
        $this->branch = $branch;

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

    /**
     * @param Environment $environment
     *
     * @return Version
     */
    public function setEnvironment(Environment $environment): Version
    {
        $this->environment = $environment;

        return $this;
    }
    //</editor-fold>

    public function __toString()
    {
        return $this->number;
    }

    public function getCommitUrl()
    {
        return sprintf('https://github.com/%s/%s/commit/%s', $this->environment->getProject()->getGithubOwner(), $this->environment->getProject()->getGithubProject(), $this->commit);
    }
}
