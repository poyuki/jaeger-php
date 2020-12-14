<?php
/*
 * Copyright (c) 2019, The Jaeger Authors
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except
 * in compliance with the License. You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under the License
 * is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express
 * or implied. See the License for the specific language governing permissions and limitations under
 * the License.
 */

namespace Jaeger;

use Jaeger\Thrift\AgentClient;

/**
 * send thrift to jaeger-agent
 * Class UdpClient
 * @package Jaeger
 */
class UdpClient
{

    private string $host;

    private string $post;

    private $socket;

    private AgentClient $agentClient;

    public function __construct(string $hostPost, AgentClient $agentClient)
    {
        [$this->host, $this->post] = explode(":", $hostPost);
        $this->agentClient = $agentClient;
        $this->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    }


    /**
     * @return bool
     */
    public function isOpen(): bool
    {
        return $this->socket !== null;
    }


    /**
     * send thrift
     * @param $batch
     * @return bool|null
     */
    public function emitBatch(array $batch): ?bool
    {
        $buildThrift = $this->agentClient->buildThrift($batch);
        if (isset($buildThrift['len']) && $buildThrift['len'] && $this->isOpen()) {
            $len = $buildThrift['len'];
            $enitThrift = $buildThrift['thriftStr'];
            $res = socket_sendto($this->socket, $enitThrift, $len, 0, $this->host, $this->post);
            if ($res === false) {
                throw new \RuntimeException("emit fails");
            }

            return true;
        }

        return false;
    }


    public function close(): void
    {
        socket_close($this->socket);
        $this->socket = null;
    }
}