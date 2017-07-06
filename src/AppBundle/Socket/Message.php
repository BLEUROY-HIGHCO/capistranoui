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
     * @Assert\Choice({"subscribe", "broadcast"})
     */
    protected $action;

    /**
     * @var string
     * @Assert\NotBlank(groups={"broadcast"})
     */
    protected $message;

    /**
     * @var string
     * @Assert\NotBlank(groups={"subscribe", "broadcast"})
     */
    protected $envId;

    /**
     * @var string
     * @Assert\NotBlank(groups={"broadcast"})
     */
    protected $logType;

    /**
     * @var string
     * @Assert\NotBlank(groups={"broadcast"})
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
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getEnvId(): ?string
    {
        return $this->envId;
    }

    /**
     * @return string
     */
    public function getLogType(): ?string
    {
        return $this->logType;
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
     * @param string $envId
     *
     * @return Message
     */
    public function setEnvId(string $envId): Message
    {
        $this->envId = $envId;

        return $this;
    }

    /**
     * @param string $message
     *
     * @return Message
     */
    public function setMessage(?string $message): Message
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @param string $logType
     *
     * @return Message
     */
    public function setLogType(string $logType): Message
    {
        $this->logType = $logType;

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
        $groups   = ['Message'];
        $groups[] = $this->action;

        return $groups;
    }
}
