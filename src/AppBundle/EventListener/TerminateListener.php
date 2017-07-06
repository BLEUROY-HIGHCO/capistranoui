<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\Environment;
use AppBundle\Entity\User;
use AppBundle\Entity\Version;
use AppBundle\Socket\Message;
use AppBundle\Socket\Sender;
use AppBundle\Socket\Socket;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\SerializerInterface;

class TerminateListener
{

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var Sender
     */
    private $sender;

    /**
     * @var string
     */
    private $capistranoPath;

    /**
     * @var string
     */
    private $capistranoBin;

    public function __construct(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage, SerializerInterface $serializer, Sender $sender, string $capistranoPath, string $capistranoBin)
    {
        $this->entityManager  = $entityManager;
        $this->tokenStorage   = $tokenStorage;
        $this->serializer     = $serializer;
        $this->sender         = $sender;
        $this->capistranoPath = $capistranoPath;
        $this->capistranoBin  = $capistranoBin;
    }

    public function onKernelTerminate(PostResponseEvent $event)
    {
        if ($event->isMasterRequest() && $event->getResponse()->getStatusCode() === 200) {
            $request = $event->getRequest();
            switch ($request->attributes->get('_route')) {
                case 'environment_deploy':
                    /** @var Environment $environment */
                    $environment = $request->get('environment');
                    $branch      = $environment->isBranchSelectable() ? $request->request->get('form')['branch'] : $environment->getDefaultBranch();
                    /** @var User $user */
                    $user = $this->tokenStorage->getToken()->getUser();
                    $this->runProcess($environment, [$environment->getName(), 'deploy', sprintf('branch=%s', $branch), sprintf('deployer=%s', $user->getUsername())], Socket::TYPE_DEPL0Y, $user, $branch);
                    break;
                case 'environment_rollback':
                    /** @var Version $version */
                    $version = $request->get('version');
                    /** @var User $user */
                    $user = $this->tokenStorage->getToken()->getUser();
                    $this->runProcess($version->getEnvironment(), [$version->getEnvironment()->getName(), 'deploy:rollback', sprintf('deployer=%s', $user->getUsername())], Socket::TYPE_ROLLBACK, $user, null, $version);
                    break;
            }
        }
    }

    /**
     * @param Environment  $environment
     * @param array        $arguments
     * @param string       $deployType
     * @param User         $user
     * @param string|null  $branch
     * @param Version|null $version
     */
    private function runProcess(Environment $environment, array $arguments, string $deployType, User $user, string $branch = null, Version $version = null)
    {
        $process = (new ProcessBuilder())
            ->setWorkingDirectory(sprintf('%s/%s', $this->capistranoPath, $environment->getProject()->getFolder()))
            ->setPrefix($this->capistranoBin)
            ->setArguments(array_merge(['exec', 'cap'], $arguments))
            ->getProcess()
            ->setTimeout(null);

        file_put_contents('/tmp/capistranoui.log.3', $process->getWorkingDirectory() . ' TEST ' . $process->getCommandLine());

        $versionNumber = null;
        $commit        = null;

        $process->start();

        $err = '';

        foreach ($process as $type => $buffer) {
            $logType = Socket::TYPE_NOTICE;
            if (Process::ERR === $type) {
                $logType = Socket::TYPE_ERROR;
                $err .= $buffer;
            }
            $this->broadcastMessage($environment->getId(), $buffer, $logType);
            if (preg_match('/releases\/([0-9]+)/', $buffer, $matches)) {
                $versionNumber = $matches[1];
            }

            if (null !== $branch && preg_match(sprintf('/Branch %s \(at ([a-zA-Z0-9]+)\)/', $branch), $buffer, $matches)) {
                $commit = $matches[1];
            }
        }

        file_put_contents('/tmp/capistranoui.log', $err.' ERR '. $process->getExitCode());

        if ($process->getExitCode() === 0) {
            $newVersion = new Version();
            $environment->setCurrentVersion($newVersion);
            $environment->addVersion($newVersion);
            $newVersion->setDeployedBy($user);
            $newVersion->setDeployedAt(new \DateTime());
            $newVersion->setNumber($versionNumber);
            if ($deployType === Socket::TYPE_ROLLBACK) {
                $newVersion->setCommit($version->getCommit());
                $newVersion->setBranch($version->getBranch());
            } elseif ($deployType === Socket::TYPE_DEPL0Y) {
                $newVersion->setCommit($commit);
                $newVersion->setBranch($branch);
            }

            $this->entityManager->persist($newVersion);
            $this->entityManager->flush();
        }
    }

    private function broadcastMessage(string $envId, string $buffer, string $logType)
    {
        $message = new Message();
        $message->setEnvId($envId)->setAction('broadcast')->setMessage($buffer)->setLogType($logType);
        $this->sender->sendMessage($this->serializer->serialize($message, 'json'));
    }
}
