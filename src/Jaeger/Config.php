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
use Jaeger\Propagator\JaegerPropagator;
use Jaeger\Propagator\ZipkinPropagator;
use Jaeger\Reporter\RemoteReporter;
use Jaeger\Reporter\ReporterInterface;
use Jaeger\Sampler\ConstSampler;
use Jaeger\Sampler\SamplerInterface;
use Jaeger\Transport\TransportInterface;
use Jaeger\Transport\TransportUdp;
use OpenTracing\NoopTracer;
use const Jaeger\Constants\PROPAGATOR_JAEGER;
use const Jaeger\Constants\PROPAGATOR_ZIPKIN;

class Config
{
    private TransportInterface $transport;

    private ReporterInterface $reporter;

    private SamplerInterface $sampler;

    private \OpenTracing\ScopeManager $scopeManager;

    private bool $gen128bit = false;

    public static array $tracers;

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
    public function initTracer(string $serverName, string $agentHostPort = ''): ?Jaeger
    {

        if (self::$disabled) {
            return NoopTracer::create();
        }

        if ($serverName === '') {
            throw new \RuntimeException("serverName require");
        }

        if (isset(self::$tracers[$serverName]) && !empty(self::$tracers[$serverName])) {
            return self::$tracers[$serverName];
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


        self::$tracers[$serverName] = $tracer;


        return $tracer;
    }

    /**
     * close tracer
     *
     * @param $disabled
     * @return Config
     */
    public function setDisabled($disabled): Config
    {
        self::$disabled = $disabled;

        return $this;
    }

    public function setTransport(TransportInterface $transport): Config
    {
        $this->transport = $transport;

        return $this;
    }


    public function setReporter(ReporterInterface $reporter): Config
    {
        $this->reporter = $reporter;

        return $this;
    }


    public function setSampler(SamplerInterface $sampler): Config
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
        if (count(self::$tracers) > 0) {
            foreach (self::$tracers as $tracer) {
                $tracer->reportSpan();
            }
            $this->reporter->close();
        }

        return true;
    }
}
