<?php

namespace Infrastructure\FrameworkCore\Adapters\Authentication;

use Infrastructure\FrameworkCore\Contracts\Authentication\AuthenticatorInterface;

abstract class AbstractAuthenticator implements AuthenticatorInterface
{
    /**
     * Get the authentication guard name.
     */
    abstract protected function getGuardName(): string;

    /**
     * Get the user provider name.
     */
    protected function getProviderName(): ?string
    {
        return null;
    }
}
