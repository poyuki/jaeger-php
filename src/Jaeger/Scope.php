<?php

namespace Jaeger;


class Scope implements \OpenTracing\Scope{

    /**
     * @var MockScopeManager
     */
    private $scopeManager = null;

    /**
     * @var span
     */
    private span $span;

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
    public function __construct(ScopeManager $scopeManager, \OpenTracing\Span $span, $finishSpanOnClose){
        $this->scopeManager = $scopeManager;
        $this->span = $span;
        $this->finishSpanOnClose = $finishSpanOnClose;
    }


    public function close(){
        if ($this->finishSpanOnClose) {
            $this->span->finish();
        }

        $this->scopeManager->delActive($this);
    }


    public function getSpan(){
        return $this->span;
    }
}
