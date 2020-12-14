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

namespace Jaeger\Reporter;

use Jaeger\Jaeger;
use Jaeger\Transport\TransportInterface;

class RemoteReporter implements ReporterInterface
{

    public TransportInterface $tran;

    public function __construct(TransportInterface $tran)
    {
        $this->tran = $tran;
    }

    public function report(Jaeger $jaeger): void
    {
        $this->tran->append($jaeger);
    }

    public function close(): void
    {
        $this->tran->flush();
    }
}
