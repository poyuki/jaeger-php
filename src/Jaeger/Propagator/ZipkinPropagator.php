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

namespace Jaeger\Propagator;

use Jaeger\SpanContext;
use Jaeger\Constants;
use const Jaeger\Constants\X_B3_PARENT_SPANID;
use const Jaeger\Constants\X_B3_SAMPLED;
use const Jaeger\Constants\X_B3_SPANID;
use const Jaeger\Constants\X_B3_TRACEID;

class ZipkinPropagator implements Propagator
{

    public function inject(SpanContext $spanContext, string $format, array &$carrier): void
    {
        $carrier[X_B3_TRACEID] = $spanContext->traceIdLowToString();
        $carrier[X_B3_PARENT_SPANID] = $spanContext->parentIdToString();
        $carrier[X_B3_SPANID] = $spanContext->spanIdToString();
        $carrier[X_B3_SAMPLED] = $spanContext->flagsToString();
    }


    public function extract(string $format, array $carrier): ?SpanContext
    {
        $spanContext = null;

        foreach ($carrier as $k => $val) {
            if (in_array($k, [X_B3_TRACEID, X_B3_PARENT_SPANID, X_B3_SPANID, X_B3_SAMPLED], true)) {
                if ($spanContext === null) {
                    $spanContext = new SpanContext(0, 0, 0, null, 0);
                }

                continue;
            }
        }

        if (isset($carrier[X_B3_TRACEID]) && $carrier[X_B3_TRACEID]) {
            $spanContext->traceIdToString($carrier[X_B3_TRACEID]);
        }

        if (isset($carrier[X_B3_PARENT_SPANID]) && $carrier[X_B3_PARENT_SPANID]) {
            $spanContext->parentId = $spanContext->hexToSignedInt($carrier[X_B3_PARENT_SPANID]);
        }

        if (isset($carrier[X_B3_SPANID]) && $carrier[X_B3_SPANID]) {
            $spanContext->spanId = $spanContext->hexToSignedInt($carrier[X_B3_SPANID]);
        }

        if (isset($carrier[X_B3_SAMPLED]) && $carrier[X_B3_SAMPLED]) {
            $spanContext->flags = $carrier[X_B3_SAMPLED];
        }


        return $spanContext;
    }
}
