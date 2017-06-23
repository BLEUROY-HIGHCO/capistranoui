<?php

namespace AppBundle\Socket;

use AppBundle\Entity\Environment;
use AppBundle\Entity\User;
use AppBundle\Entity\Version;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Logger;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Socket implements MessageComponentInterface
{
    /**
     * @var ArrayCollection
     */
    protected $environments;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var string
     */
    protected $capistranoFolder;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * Socket constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface     $validator
     * @param string                 $capistranoFolder
     * @param Logger                 $logger
     */
    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator, string $capistranoFolder, Logger $logger)
    {
        $this->environments     = new ArrayCollection();
        $this->entityManager    = $entityManager;
        $this->validator        = $validator;
        $this->capistranoFolder = $capistranoFolder;
        $this->serializer       = new Serializer([new ObjectNormalizer()], [new JsonDecode()]);
        $this->logger           = $logger;
    }

    /**
     * When a new connection is opened it will be passed to this method
     *
     * @param  ConnectionInterface $conn The socket/connection that just connected to your application
     *
     * @throws \Exception
     */
    public function onOpen(ConnectionInterface $conn)
    {
        //Do nothing
        $this->logger->debug('onOpen', ['env' => 100]);
    }

    /**
     * This is called before or after a socket is closed (depends on how it's closed).  SendMessage to $conn will not result in an error if it has already been closed.
     *
     * @param  ConnectionInterface $conn The socket/connection that is closing/closed
     *
     * @throws \Exception
     */
    public function onClose(ConnectionInterface $conn)
    {
        $this->logger->debug('onClose', ['env' => 100]);
        /** @var ArrayCollection $environment */
        foreach ($this->environments as $environment) {
            $environment->removeElement($conn);
        }
    }

    /**
     * If there is an error with one of the sockets, or somewhere in the application where an Exception is thrown,
     * the Exception is sent back down the stack, handled by the Server and bubbled back up the application through this method
     *
     * @param  ConnectionInterface $conn
     * @param  \Exception          $e
     *
     * @throws \Exception
     */
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $this->logger->debug('error', ['env' => 100]);
        $conn->send($e->getMessage());
    }

    /**
     * Triggered when a client sends data through the socket
     *
     * @param  \Ratchet\ConnectionInterface $from The socket/connection that sent the message to your application
     * @param  string                       $msg  The message received
     *
     * @throws \Exception
     */
    public function onMessage(ConnectionInterface $from, $msg)
    {
        try {
            /** @var Message $message */
            $message = $this->serializer->deserialize($msg, Message::class, 'json');
            $errors  = $this->validator->validate($message);

            if (count($errors) > 0) {
                $from->send($errors);
            } else {
                call_user_func_array([$this, $message->getAction()], [$from, $message]);
            }
        } catch (\Exception $e) {
            $from->send($e->getMessage());
        }
    }

    private function subscribe(ConnectionInterface $from, Message $message)
    {
        $envId = $message->getEnvId();
        if (!isset($this->environments[$envId])) {
            $this->environments[$envId] = new ArrayCollection();
        }
        $this->environments[$envId]->add($from);
        //TODO : set file dynamically
        $file = __DIR__.'/../../../var/logs/dev/capistrano/'.$envId.'.log';
        if (file_exists($file)) {
            $from->send(file_get_contents($file));
        } else {
            $from->send('Pas de fichier');
        }
    }

    private function deploy(ConnectionInterface $from, Message $message)
    {
        $branch = $message->getBranch();

        /** @var Environment $environment */
        $environment = $this->entityManager->getRepository(Environment::class)->find($message->getEnvId());
        if (null === $environment) {
            throw new InvalidEnvironmentException();
        }

        /** @var User $user */
        $user = $this->entityManager->getRepository(User::class)->findByUsername($message->getUsername());
        if (null === $user) {
            throw new InvalidUserException();
        }

        $this->runProcess($environment, ['deploy', $environment->getName()]);
    }

    private function rollback(ConnectionInterface $from, Message $message)
    {
        /** @var Version $version */
        $version = $this->entityManager->getRepository(Version::class)->find($message->getVersionId());
        if (null === $version) {
            throw new InvalidVersionException();
        }

        /** @var User $user */
        $user = $this->entityManager->getRepository(User::class)->findByUsername($message->getUsername());
        if (null === $user) {
            throw new InvalidUserException();
        }

        $this->runProcess($version->getEnvironment(), ['deploy', $version->getEnvironment()->getName()]);
    }

    private function runProcess(Environment $environment, array $arguments)
    {
        //TODO : vérifier qui lance le deploy (avec les droits)
        $process = (new ProcessBuilder())
            ->setWorkingDirectory(sprintf('%s/%s', $this->capistranoFolder, $environment->getProject()->getFolder()))
            ->setPrefix('capistrano')
            ->setArguments($arguments)
            ->getProcess();

        $envId = $environment->getId();

        $that = $this;

        $process->run(function ($type, $buffer) use ($that, $envId) {
            if (Process::ERR === $type) {
                $message = sprintf('ERR > %s', $buffer);
            } else {
                $message = sprintf('OUT > %s', $buffer);
            }

            $that->broadcastMessage($envId, $message);
        }
        );
    }

    /**
     * @param int    $envId
     * @param string $message
     */
    private function broadcastMessage(int $envId, string $message)
    {
        if (!isset($this->environments[$envId])) {
            return;
        }

        foreach ($this->environments[$envId] as $client) {
            /** @var ConnectionInterface $client */
            $client->send($message);
        }

        $this->logger->debug($message, ['env' => $envId]);
    }
}
