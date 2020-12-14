<?php

namespace Jaeger;


class Scope implements \OpenTracing\Scope{

    /**
     * @var ScopeManager
     */
    private ScopeManager $scopeManager;

    /**
     * @var \OpenTracing\Span
     */
    private \OpenTracing\Span $span;

    /**
     * @var bool
     */
    private bool $finishSpanOnClose;


    /**
     * Scope constructor.
     * @param ScopeManager $scopeManager
     * @param \OpenTracing\Span $span
     * @param bool $finishSpanOnClose
     */
    public function __construct(ScopeManager $scopeManager, \OpenTracing\Span $span, bool $finishSpanOnClose){
        $this->scopeManager = $scopeManager;
        $this->span = $span;
        $this->finishSpanOnClose = $finishSpanOnClose;
    }


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
