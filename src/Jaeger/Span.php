<?php

declare(strict_types=1);

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

class Span implements \OpenTracing\Span
{
    public int $startTime;

    public int $finishTime;

    public int $duration = 0;

    public array $logs = [];

    public array $tags = [];


    public function __construct(
        private string $operationName,
        public \OpenTracing\SpanContext $spanContext,
        public array $references = [],
        ?int $startTime = null
    ) {
        $this->startTime = $startTime ?? $this->microtimeToInt();
    }

    /**
     * @return string
     */
    public function getOperationName(): string
    {
        return $this->operationName;
    }


    /**
     * @return \OpenTracing\SpanContext
     */
    public function getContext(): \OpenTracing\SpanContext
    {
        return $this->spanContext;
    }

    /**
     * @param float|int|\DateTimeInterface|null $finishTime if passing float or int
     * it should represent the timestamp (including as many decimal places as you need)
     */
    public function finish($finishTime = null): void
    {
        $this->finishTime = $finishTime ?? $this->microtimeToInt();
        $this->duration = $this->finishTime - $this->startTime;
    }

    /**
     * @param string $newOperationName
     */
    public function overwriteOperationName(string $newOperationName): void
    {
        $this->operationName = $newOperationName;
    }


    public function setTag(string $key, $value): void
    {
        $this->tags[$key] = $value;
    }


    /**
     * Adds a log record to the span
     *
     * @param array $fields [key => val]
     * @param int|float|\DateTimeInterface $timestamp
     */
    public function log(array $fields = [], $timestamp = null): void
    {
        $this->logs[] = [
            'timestamp' => $timestamp ?: $this->microtimeToInt(),
            'fields' => $fields,
        ];
    }

    /**
     * Adds a baggage item to the SpanContext which is immutable so it is required to use SpanContext::withBaggageItem
     * to get a new one.
     *
     * @param string $key
     * @param string $value
     * @return void
     */
    public function addBaggageItem(string $key, string $value): void
    {
        $this->log([
            'event' => 'baggage',
            'key' => $key,
            'value' => $value,
        ]);
        $this->spanContext->withBaggageItem($key, $value);
    }

    /**
     * @param string $key
     * @return string|null
     */
    public function getBaggageItem(string $key): ?string
    {
        return $this->spanContext->getBaggageItem($key);
    }


    private function microtimeToInt(): int
    {
        return (int)(microtime(true) * 1000000);
    }
}
