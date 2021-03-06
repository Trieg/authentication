<?php
namespace vakata\authentication;

/**
 * A class for authentication management.
 */
class Manager implements AuthenticationInterface
{
    protected $providers = [];

    public function __construct(array $providers = [])
    {
        foreach ($providers as $provider) {
            if ($provider instanceof AuthenticationInterface) {
                $this->providers[] = $provider;
            }
        }
    }
    public function addProvider(AuthenticationInterface $provider)
    {
        $this->providers[] = $provider;
        return $this;
    }
    public function getProviders(): array
    {
        return $this->providers;
    }
    /**
     * Do any of the providers support this input
     * @param  array    $data the auth input
     * @return boolean
     */
    public function supports(array $data = []) : bool
    {
        foreach ($this->providers as $method) {
            if ($method->supports($data)) {
                return true;
            }
        }
        return false;
    }
    /**
     * Authenticate using the supplied input. Returns a JWT token or throws an AuthenticationException.
     * @param  array        $data
     * @return Credentials
     */
    public function authenticate(array $data = []) : Credentials
    {
        $supported = [];
        foreach ($this->providers as $method) {
            if ($method->supports($data)) {
                $supported[] = $method;
            }
        }
        if (!count($supported)) {
            throw new AuthenticationExceptionNotSupported();
        }
        $exceptions = [];
        foreach ($supported as $method) {
            try {
                return $method->authenticate($data);
            } catch (AuthenticationException $e) {
                $exceptions[] = $e;
            }
        }
        throw $exceptions[0] ?? new AuthenticationException('No supported authentication methods');
    }
}
