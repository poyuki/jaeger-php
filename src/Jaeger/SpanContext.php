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

class SpanContext implements \OpenTracing\SpanContext
{
    // traceID represents globally unique ID of the trace.
    // Usually generated as a random number.
    public int $traceIdLow;

    public int $traceIdHigh;


    // spanID represents span ID that must be unique within its trace,
    // but does not have to be globally unique.
    public int $spanId;

    // parentID refers to the ID of the parent span.
    // Should be 0 if the current span is a root span.
    public int $parentId;

    // flags is a bitmap containing such bits as 'sampled' and 'debug'.
    public int $flags;

    // Distributed Context baggage. The is a snapshot in time.
    // key => val
    public ?array $baggage;

    // debugID can be set to some correlation ID when the context is being
    // extracted from a TextMap carrier.
    public int $debugId;


    public function __construct(int $spanId, int $parentId, int $flags, ?array $baggage = null, int $debugId = 0)
    {}


    public function getBaggageItem($key)
    {
        return $this->baggage[$key] ?? null;
    }


    public function withBaggageItem($key, $value): bool
    {
        $this->baggage[$key] = $value;

        return true;
    }

    public function getIterator()
    {
        // TODO: Implement getIterator() method.
    }


    /**
     * @return string
     */
    public function buildString(): string
    {
        if ($this->traceIdHigh) {
            return sprintf(
                "%x%016x:%x:%x:%x",
                $this->traceIdHigh,
                $this->traceIdLow,
                $this->spanId,
                $this->parentId,
                $this->flags
            );
        }

        return sprintf("%x:%x:%x:%x", $this->traceIdLow, $this->spanId, $this->parentId, $this->flags);
    }


    /**
     * @return string
     */
    public function spanIdToString(): string
    {
        return sprintf("%x", $this->spanId);
    }


    /**
     * @return string
     */
    public function parentIdToString(): string
    {
        return sprintf("%x", $this->parentId);
    }


    public function traceIdLowToString(): string
    {
        if ($this->traceIdHigh) {
            return sprintf("%x%016x", $this->traceIdHigh, $this->traceIdLow);
        }

        return sprintf("%x", $this->traceIdLow);
    }


    /**
     * @return string
     */
    public function flagsToString(): string
    {
        return sprintf("%x", $this->flags);
    }


    /**
     * 是否取样
     *
     * @return mixed
     */
    public function isSampled()
    {
        return $this->flags;
    }


    /**
     * @param string $hex
     * @return int
     */
    public function hexToSignedInt(string $hex): int
    {
        $hexStrLen = strlen($hex);
        $dec = 0;
        for ($i = 0; $i < $hexStrLen; $i++) {
            $hexByteStr = $hex[$i];
            if (ctype_xdigit($hexByteStr)) {
                $decByte = hexdec($hex[$i]);
                $dec = ($dec << 4) | $decByte;
            }
        }

        return $dec;
    }


    /**
     * @param string $traceId
     */
    public function traceIdToString(string $traceId): void
    {
        $len = strlen($traceId);
        if ($len > 16) {
            $this->traceIdHigh = $this->hexToSignedInt(substr($traceId, 0, 16));
            $this->traceIdLow = $this->hexToSignedInt(substr($traceId, 16));
        } else {
            $this->traceIdLow = $this->hexToSignedInt($traceId);
        }
    }


    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->isTraceIdValid() && $this->spanId;
    }


    /**
     * @return bool
     */
    public function isTraceIdValid(): bool
    {
        return $this->traceIdLow || $this->traceIdHigh;
    }
}
