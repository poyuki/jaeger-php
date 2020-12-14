<?php

declare(strict_types=1);

namespace Jaeger;

use JetBrains\PhpStorm\Pure;

class ScopeManager implements \OpenTracing\ScopeManager
{

    /**
     * @var array|Scope[]
     */
    private array $scopes = [];


    /**
     * append scope
     *
     * @param \OpenTracing\Span $span
     * @param bool $finishSpanOnClose
     * @return Scope
     */
    public function activate(\OpenTracing\Span $span, $finishSpanOnClose): Scope
    {
        $scope = new Scope($this, $span, $finishSpanOnClose);
        $this->scopes[] = $scope;

        return $scope;
    }


    /**
     * @return Scope|null
     */
    #[Pure] public function getActive(): ?Scope
    {
        if (count($this->scopes) === 0) {
            return null;
        }

        return $this->scopes[count($this->scopes) - 1];
    }


    /**
     * del scope
     *
     * @param Scope $scope
     * @return bool
     */
    public function delActive(Scope $scope): bool
    {
        if (count($this->scopes) === 0) {
            return false;
        }

        foreach ($this->scopes as $i => $iValue) {
            if ($scope === $iValue) {
                array_splice($this->scopes, $i, 1);
            }
        }

        return true;
    }
}
