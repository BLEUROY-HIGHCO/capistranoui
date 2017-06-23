<?php

namespace AppBundle\Socket;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\GroupSequenceProviderInterface;

/**
 * Socket message
 *
 * @Assert\GroupSequenceProvider
 */
class Message implements GroupSequenceProviderInterface
{
    //<editor-fold desc="Members">
    /**
     * @var string
     * @Assert\NotBlank(groups={"Message"})
     * @Assert\Choice({"subscribe", "deploy", "rollback"})
     */
    protected $action;

    /**
     * @var string
     * @Assert\NotBlank(groups={"deploy", "rollback"})
     */
    protected $username;

    /**
     * @var int
     * @Assert\NotBlank(groups={"deploy", "subscribe"})
     */
    protected $envId;

    /**
     * @var string
     * @Assert\NotBlank(groups={"deploy"})
     */
    protected $branch;

    /**
     * @var int
     * @Assert\NotBlank(groups={"rollback"})
     */
    protected $versionId;

    /**
     * @var string
     * @Assert\NotBlank(groups={"deploy", "rollback"})
     */
    protected $token;
    //</editor-fold>

    //<editor-fold desc="Getters">
    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @return string
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @return int
     */
    public function getEnvId(): ?int
    {
        return $this->envId;
    }

    /**
     * @return string
     */
    public function getBranch(): ?string
    {
        return $this->branch;
    }

    /**
     * @return int
     */
    public function getVersionId(): ?int
    {
        return $this->versionId;
    }

    /**
     * @return string
     */
    public function getToken(): ?string
    {
        return $this->token;
    }
    //</editor-fold>

    //<editor-fold desc="Setters">
    /**
     * @param string $action
     *
     * @return Message
     */
    public function setAction(string $action): Message
    {
        $this->action = $action;

        return $this;
    }

    /**
     * @param string $username
     *
     * @return Message
     */
    public function setUsername(?string $username): Message
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @param int $envId
     *
     * @return Message
     */
    public function setEnvId(?int $envId): Message
    {
        $this->envId = $envId;

        return $this;
    }

    /**
     * @param string $branch
     *
     * @return Message
     */
    public function setBranch(?string $branch): Message
    {
        $this->branch = $branch;

        return $this;
    }

    /**
     * @param int $versionId
     *
     * @return Message
     */
    public function setVersionId(?int $versionId): Message
    {
        $this->versionId = $versionId;

        return $this;
    }

    /**
     * @param string $token
     *
     * @return Message
     */
    public function setToken(?string $token): Message
    {
        $this->token = $token;

        return $this;
    }
    //</editor-fold>

    /**
     * Returns which validation groups should be used for a certain state
     * of the object.
     *
     * @return array An array of validation groups
     */
    public function getGroupSequence()
    {
        $groups = ['Message'];
        $groups[] = $this->action;

        return $groups;
    }
}
