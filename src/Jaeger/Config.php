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

declare(strict_types=1);

namespace Jaeger;

use Exception;
use Jaeger\Reporter\RemoteReporter;
use Jaeger\Reporter\Reporter;
use Jaeger\Transport\TransportUdp;
use OpenTracing\NoopTracer;
use Jaeger\Sampler\Sampler;
use Jaeger\Sampler\ConstSampler;
use Jaeger\Propagator\JaegerPropagator;
use Jaeger\Propagator\ZipkinPropagator;
use const Jaeger\Constants\PROPAGATOR_JAEGER;
use const Jaeger\Constants\PROPAGATOR_ZIPKIN;

class Config
{
    private $transport;

    private $reporter;

    private $sampler;

    private $scopeManager;

    private bool $gen128bit = false;

    public static $tracer;

    public static $span;

    public static self $instance;

    public static bool $disabled = false;

    public static string $propagator = PROPAGATOR_JAEGER;

    private function __construct()
    {

    }

    private function __clone()
    {

    }

    public static function getInstance(): Config
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * init jaeger, return can use flush  buffers
     *
     * @param $serverName
     * @param string $agentHostPort
     * @return Jaeger|null
     * @throws Exception
     */
    public function initTracer($serverName, $agentHostPort = ''): ?Jaeger
    {

        if (self::$disabled) {
            return NoopTracer::create();
        }

        if ($serverName == '') {
            throw new Exception("serverName require");
        }

        if (isset(self::$tracer[$serverName]) && !empty(self::$tracer[$serverName])) {
            return self::$tracer[$serverName];
        }


        if ($this->transport === null) {
            $this->transport = new TransportUdp($agentHostPort);
        }

        if ($this->reporter === null) {
            $this->reporter = new RemoteReporter($this->transport);
        }

        if ($this->sampler === null) {
            $this->sampler = new ConstSampler(true);
        }

        if ($this->scopeManager === null) {
            $this->scopeManager = new ScopeManager();
        }

        $tracer = new Jaeger($serverName, $this->reporter, $this->sampler, $this->scopeManager);

        if ($this->gen128bit === true) {
            $tracer->gen128bit();
        }

        if (self::$propagator === PROPAGATOR_ZIPKIN) {
            $tracer->setPropagator(new ZipkinPropagator());
        } else {
            $tracer->setPropagator(new JaegerPropagator());
        }


        self::$tracer[$serverName] = $tracer;


        return $tracer;
    }

    /**
     * close tracer
     * @param $disabled
     * @return Config
     */
    public function setDisabled($disabled): Config
    {
        self::$disabled = $disabled;

        return $this;
    }

    public function setTransport(Transport\Transport $transport): Config
    {
        $this->transport = $transport;

        return $this;
    }


    public function setReporter(Reporter $reporter): Config
    {
        $this->reporter = $reporter;

        return $this;
    }


    public function setSampler(Sampler $sampler): Config
    {
        $this->sampler = $sampler;

        return $this;
    }


    public function gen128bit(): Config
    {
        $this->gen128bit = true;

        return $this;
    }


    public function flush(): bool
    {
        if (count(self::$tracer) > 0) {
            foreach (self::$tracer as $tracer) {
                $tracer->reportSpan();
            }
            $this->reporter->close();
        }

        return true;
    }
}
