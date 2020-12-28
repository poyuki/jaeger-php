<?php

declare(strict_types=1);

namespace Jaeger;

class Scope implements \OpenTracing\Scope
{
    /**
     * Scope constructor.
     *
     * @param ScopeManager $scopeManager
     * @param \OpenTracing\Span $span
     * @param bool $finishSpanOnClose
     */
    public function __construct(private ScopeManager $scopeManager, private \OpenTracing\Span $span, private bool $finishSpanOnClose)
    { }

    public function close(): void
    {
        if ($this->finishSpanOnClose) {
            $this->span->finish();
        }

        $this->scopeManager->delActive($this);
    }

    public function getSpan(): \OpenTracing\Span
    {
        return $this->span;
    }
}
