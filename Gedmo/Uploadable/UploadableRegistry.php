<?php

namespace EMC\FileinputBundle\Gedmo\Uploadable;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class UploadableRegistry implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var array
     */
    private $providers;

    function __construct(array $providers)
    {
        $this->providers = $providers;
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        if ($name === null || !isset($this->providers[$name])) {
            throw new \InvalidArgumentException(sprintf('The upload provider "%s" is not registered with the service container.',
                $name));
        }

        $provider = $this->container->get($this->providers[$name]);

        if (!$provider->getUploadableListener() instanceof UploadableListenerInterface) {
            throw new \InvalidArgumentException(sprintf(
                'The upload provider name specified for the service "%s" does not implements %s',
                $this->providers[$name],
                UploadableListenerInterface::class
            ));
        }

        return $provider;
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        return isset($this->providers[$name]);
    }
}
