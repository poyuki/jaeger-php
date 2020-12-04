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

namespace Jaeger\Sampler;

use Exception;
use Jaeger\Constants;
use const Jaeger\Constants\SAMPLER_PARAM_TAG_KEY;
use const Jaeger\Constants\SAMPLER_TYPE_TAG_KEY;

class ProbabilisticSamplerInterface implements SamplerInterface
{

    // min 0, max 1
    private float $rate;

    private array $tags = [];


    public function __construct(float $rate = 0.0001)
    {
        $this->rate = $rate;
        $this->tags[SAMPLER_TYPE_TAG_KEY] = 'probabilistic';
        $this->tags[SAMPLER_PARAM_TAG_KEY] = $rate;
    }


    /**
     * @return bool
     * @throws Exception
     */
    public function isSampled(): bool
    {
        return random_int(1, 1 / $this->rate) === 1;
    }


    public function close(): void
    {
        //nothing to do
    }


    public function getTags(): array
    {
        return $this->tags;
    }
}
