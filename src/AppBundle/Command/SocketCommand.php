<?php

namespace AppBundle\Command;

use AppBundle\Socket\Socket;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SocketCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('socket:start')
             ->setHelp("Starts the capistrano socket handler")
             ->setDescription('Starts the capistrano socket handler');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'Capistrano socket',
            '============',
        ]);

        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    $this->getContainer()->get(Socket::class)
                )
            ),
            $this->getContainer()->getParameter('socket_port')
        );

        $server->run();
    }
}
