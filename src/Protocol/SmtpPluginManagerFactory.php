<?php

namespace Laminas\Mail\Protocol;

use interop\container\containerinterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\ServiceManager\ServiceManager;

/**
 * @link ServiceManager
 *
 * @psalm-import-type ServiceManagerConfiguration from ServiceManager
 */
class SmtpPluginManagerFactory implements FactoryInterface
{
    /**
     * laminas-servicemanager v2 support for invocation options.
     *
     * @var array
     * @psalm-var ServiceManagerConfiguration
     */
    protected $creationOptions;

    /**
     * {@inheritDoc}
     *
     * @psalm-param ServiceManagerConfiguration $options
     * @return SmtpPluginManager
     */
    public function __invoke(containerinterface $container, $name, ?array $options = null)
    {
        return new SmtpPluginManager($container, $options ?: []);
    }

    /**
     * {@inheritDoc}
     *
     * @return SmtpPluginManager
     */
    public function createService(ServiceLocatorInterface $container, $name = null, $requestedName = null)
    {
        return $this($container, $requestedName ?: SmtpPluginManager::class, $this->creationOptions);
    }

    /**
     * laminas-servicemanager v2 support for invocation options.
     *
     * @param array $options
     * @psalm-param ServiceManagerConfiguration $options
     * @return void
     */
    public function setCreationOptions(array $options)
    {
        $this->creationOptions = $options;
    }
}
