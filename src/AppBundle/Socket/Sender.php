<?php

namespace AppBundle\Socket;

class Sender
{
    protected $socket;

    protected $host;

    protected $port;

    protected $timeout;

    /**
     * Sender constructor.
     *
     * @param string $host
     * @param int    $port
     * @param int    $timeout
     */
    public function __construct(string $host, int $port, int $timeout)
    {
        $this->host    = $host;
        $this->port    = $port;
        $this->timeout = $timeout;
    }

    public function sendMessage($message)
    {
        if (!is_resource($this->socket)) {
            $this->connect();
        }
        $header = chr(0x80 | 0x02);
        // Mask 0x80 | payload length (0-125)
        if (strlen($message) < 126) {
            $header .= chr(0x80 | strlen($message));
        } elseif (strlen($message) < 0xFFFF) {
            $header .= chr(0x80 | 126).pack("n", strlen($message));
        } else {
            $header .= chr(0x80 | 127).pack("N", 0).pack("N", strlen($message));
        }
        // Add mask
        $mask   = pack("N", rand(1, 0x7FFFFFFF));
        $header .= $mask;

        // Mask application data.
        for ($i = 0; $i < strlen($message); $i++) {
            $message[$i] = chr(ord($message[$i]) ^ ord($mask[$i % 4]));
        }

        return fwrite($this->socket, $header.$message);
    }

    private function connect()
    {
        $this->socket = fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);
        if (!$this->socket) {
            throw new SocketException(sprintf('Unable to connect to websocket server: %s (%d)', $errstr, $errno));
        }

        stream_set_timeout($this->socket, $this->timeout);
        $headersSent = fwrite($this->socket, $this->getHeaders());
        if (!$headersSent) {
            throw new SocketException(sprintf('Unable to send upgrade header to websocket server: %s (%d)', $errstr, $errno));
        }

        $responseHeader = fread($this->socket, 1024);
        // status code 101 indicates that the WebSocket handshake has completed.
        if (!strpos($responseHeader, " 101 ") || !strpos($responseHeader, 'Sec-WebSocket-Accept: ')) {
            throw new SocketException(sprintf('erver did not accept to upgrade connection to websocket.: %s (%d)', $responseHeader, E_USER_ERROR));
        }
    }

    private function generateKey()
    {
        return substr(sprintf("%'A24s'", base64_encode(uniqid('', true))), 0, 22).'==';
    }

    private function getHeaders()
    {
        $headers = [
            'Host'                  => $this->host.':'.$this->port,
            'Upgrade'               => 'WebSocket',
            'Connection'            => 'Upgrade',
            'Sec-WebSocket-Key'     => $this->generateKey(),
            'Sec-WebSocket-Version' => '13',
        ];

        $headersString = '';

        foreach ($headers as $name => $value) {
            $headersString .= sprintf("%s: %s\r\n", $name, $value);
        }

        return sprintf("GET / HTTP/1.1\r\n%s\r\n", $headersString);
    }

    public function __destruct()
    {
        if (!is_resource($this->socket)) {
            fclose($this->socket);
        }
    }

}
