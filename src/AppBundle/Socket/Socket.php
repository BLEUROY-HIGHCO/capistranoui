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
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Socket implements MessageComponentInterface
{
    const ROOT_LOG_DIR  = __DIR__.'/../../../var/logs/dev/capistrano/';
    const TYPE_NOTICE   = 'notice';
    const TYPE_ERROR    = 'error';
    const TYPE_DEPL0Y   = 'deploy';
    const TYPE_ROLLBACK = 'rollback';

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
    protected $capistranoPath;

    /**
     * @var string
     */
    protected $capistranoBin;

    /**
     * @var EngineInterface
     */
    protected $template;

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
     * @param Logger                 $logger
     * @param EngineInterface        $template
     * @param string                 $capistranoPath
     * @param string                 $capistranoBin
     */
    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator, Logger $logger, EngineInterface $template, string $capistranoPath, string $capistranoBin)
    {
        $this->environments   = new ArrayCollection();
        $this->entityManager  = $entityManager;
        $this->validator      = $validator;
        $this->capistranoPath = $capistranoPath;
        $this->capistranoBin  = $capistranoBin;
        $this->serializer     = new Serializer([new ObjectNormalizer()], [new JsonDecode()]);
        $this->logger         = $logger;
        $this->template       = $template;
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
        $this->logger->error($e->getMessage(), ['env' => 'err']);
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
            $this->logger->error($e->getMessage(), ['env' => 'err']);
        }
    }

    private function broadcast(ConnectionInterface $from, Message $message)
    {
        $this->broadcastMessage($message->getEnvId(), $message->getMessage());
    }

    private function subscribe(ConnectionInterface $from, Message $message)
    {
        $envId = $message->getEnvId();
        if (!isset($this->environments[$envId])) {
            $this->environments[$envId] = new ArrayCollection();
        }
        $this->environments[$envId]->add($from);
        $file = self::ROOT_LOG_DIR.$envId.'.log';
        if (file_exists($file)) {
            $this->sendFileToClient($from, file_get_contents($file));
        } else {
            $from->send($this->template->render('AppBundle:Socket:printLog.html.twig', ['line' => 'No file.', 'type' => 'notice']));
        }
    }

    private function sendFileToClient(ConnectionInterface $from, string $content)
    {
        foreach (explode("\n", $content) as $line) {
            $from->send($this->template->render('AppBundle:Socket:printLog.html.twig', ['line' => $line, 'type' => 'notice']));
        }
    }

//    private function deploy(ConnectionInterface $from, Message $message)
//    {
//        /** @var Environment $environment */
//        $environment = $this->entityManager->getRepository(Environment::class)->find($message->getEnvId());
//        if (null === $environment) {
//            throw new InvalidEnvironmentException();
//        }
//
//        $username = $message->getUsername();
//        /** @var User $user */
//        $user = $this->entityManager->getRepository(User::class)->findOneByUsername($username);
//        if (null === $user) {
//            throw new InvalidUserException();
//        }
//
//        $this->runProcess($environment, [$environment->getName(), 'deploy', sprintf('branch=%s', $message->getBranch()), sprintf('deployer=%s', $username)], self::TYPE_DEPL0Y, $user, $message->getBranch());
//    }
//
//    private function rollback(ConnectionInterface $from, Message $message)
//    {
//        /** @var Version $version */
//        $version = $this->entityManager->getRepository(Version::class)->find($message->getVersionId());
//        if (null === $version) {
//            throw new InvalidVersionException();
//        }
//
//        $username = $message->getUsername();
//        /** @var User $user */
//        $user = $this->entityManager->getRepository(User::class)->findOneByUsername($username);
//        if (null === $user) {
//            throw new InvalidUserException();
//        }
//
//        $this->runProcess($version->getEnvironment(), [$version->getEnvironment()->getName(), 'deploy:rollback', sprintf('deployer=%s', $username)], self::TYPE_ROLLBACK, $user, null, $version);
//    }
//
//    /**
//     * @param Environment  $environment
//     * @param array        $arguments
//     * @param string       $deployType
//     * @param User         $user
//     * @param string|null  $branch
//     * @param Version|null $version
//     */
//    private function runProcess(Environment $environment, array $arguments, string $deployType, User $user, string $branch = null, Version $version = null)
//    {
//        $process = (new ProcessBuilder())
//            ->setWorkingDirectory(sprintf('%s/%s', $this->capistranoPath, $environment->getProject()->getFolder()))
//            ->setPrefix($this->capistranoBin)
//            ->setArguments(array_merge(['exec', 'cap'], $arguments))
//            ->getProcess()
//            ->setTimeout(null);
//
//        $that          = $this;
//        $versionNumber = null;
//        $commit        = null;
//
//        $exitCode = $process->run(function ($type, $buffer) use ($that, $environment, $branch, &$versionNumber, &$commit) {
//            $logType = $that::TYPE_NOTICE;
//            if (Process::ERR === $type) {
//                $logType = $that::TYPE_ERROR;
//            }
//            $that->broadcastMessage($environment->getId(), $buffer, $logType);
//            if (preg_match('/releases\/([0-9]+)/', $buffer, $matches)) {
//                $versionNumber = $matches[1];
//            }
//
//            if (null !== $branch && preg_match(sprintf('/Branch %s \(at ([a-zA-Z0-9]+)\)/', $branch), $buffer, $matches)) {
//                $commit = $matches[1];
//            }
//        });
//
//        if ($exitCode === 0) {
//            $newVersion = new Version();
//            $environment->setCurrentVersion($newVersion);
//            $environment->addVersion($newVersion);
//            $newVersion->setDeployedBy($user);
//            $newVersion->setDeployedAt(new \DateTime());
//            $newVersion->setNumber($versionNumber);
//            if ($deployType === self::TYPE_ROLLBACK) {
//                $newVersion->setCommit($version->getCommit());
//                $newVersion->setBranch($version->getBranch());
//            } elseif ($deployType === self::TYPE_DEPL0Y) {
//                $newVersion->setCommit($commit);
//                $newVersion->setBranch($branch);
//            }
//
//            $this->entityManager->persist($newVersion);
//            $this->entityManager->flush();
//        }
//    }

    /**
     * @param int    $envId
     * @param string $message
     * @param string $type
     */
    private function broadcastMessage(int $envId, string $message, $type = self::TYPE_NOTICE)
    {
        if (!isset($this->environments[$envId])) {
            return;
        }

        foreach ($this->environments[$envId] as $client) {
            /** @var ConnectionInterface $client */
            $client->send($this->template->render('AppBundle:Socket:printLog.html.twig', ['line' => $message, 'type' => $type]));
        }

        $this->logger->info($message, ['env' => $envId]);
    }
}
