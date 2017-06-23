<?php

namespace AppBundle\Log\Handler;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class FlushableStreamHandler extends AbstractProcessingHandler
{
    /**
     * @var
     */
    protected $streams;

    /**
     * @var int
     */
    protected $baseUrl;

    /**
     * @var
     */
    protected $errorMessage;

    /**
     * @var bool
     */
    protected $dirCreated;

    /**
     * FlushableStreamHandler constructor.
     *
     * @param int      $baseUrl
     * @param bool|int $level
     * @param bool     $bubble
     */
    public function __construct($baseUrl, $level = Logger::DEBUG, $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->baseUrl = $baseUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        foreach ($this->streams as $stream) {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }
    }

    /**
     * Writes the record down to the log of the implementing handler
     *
     * @param  array $record
     *
     * @return void
     */
    protected function write(array $record)
    {
        $envId = null;
        if (isset($record['context'], $record['context']['env'])) {
            $envId = $record['context']['env'];
        } else {
            throw new \InvalidArgumentException('A record must contain a envId.');
        }
        if (!is_resource($this->streams[$envId])) {
            $path = sprintf(sprintf('%s/%s.log', $this->baseUrl, $envId));
            $this->createDir();
            $this->errorMessage = null;
            set_error_handler([$this, 'customErrorHandler']);
            $this->streams[$envId] = fopen($path, 'wb');
            restore_error_handler();
            if (!is_resource($this->streams[$envId])) {
                unset($this->streams[$envId]);
                throw new \UnexpectedValueException(sprintf('The stream or file "%s" could not be opened: '.$this->errorMessage, $path));
            }
        }

        fwrite($this->streams[$envId], (string) $record['formatted']);
    }

    private function createDir()
    {
        // Do not try to create dir if it has already been tried.
        if ($this->dirCreated) {
            return;
        }

        if (null !== $this->baseUrl && !is_dir($this->baseUrl)) {
            $this->errorMessage = null;
            set_error_handler([$this, 'customErrorHandler']);
            $status = mkdir($this->baseUrl, 0777, true);
            restore_error_handler();
            if (false === $status) {
                throw new \UnexpectedValueException(sprintf('There is no existing directory at "%s" and its not buildable: '.$this->errorMessage, $this->baseUrl));
            }
        }
        $this->dirCreated = true;
    }

    private function customErrorHandler($code, $msg)
    {
        $this->errorMessage = preg_replace('{^(fopen|mkdir)\(.*?\): }', '', $msg);
    }
}
