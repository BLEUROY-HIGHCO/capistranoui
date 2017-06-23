<?php

declare(strict_types=1);

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    /**
     * Loaded bundles.
     *
     * @var array
     */
    protected $loadedBundles = [];

    /**
     * Registrer bundles.
     *
     * @return array
     */
    public function registerBundles(): array
    {
        $this->loadSymfonyBundles();
        $this->loadThirdPartyBundles();
        $this->loadAppBundles();
        $this->loadEnvBundles();

        return $this->loadedBundles;
    }

    public function getRootDir()
    {
        return __DIR__;
    }

    public function getCacheDir()
    {
        return dirname(__DIR__).'/var/cache/'.$this->getEnvironment();
    }

    public function getLogDir()
    {
        return dirname(__DIR__).'/var/logs';
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir().'/config/config_'.$this->getEnvironment().'.yml');
    }

    /**
     * Load default symfony bundles.
     *
     * Load Doctrine also.
     */
    protected function loadSymfonyBundles()
    {
        $bundles             = [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
        ];
        $this->loadedBundles = array_merge($this->loadedBundles, $bundles);
    }

    /**
     * Load third parties.
     */
    protected function loadThirdPartyBundles()
    {
        $bundles             = [
            new FOS\UserBundle\FOSUserBundle(),
            new Vich\UploaderBundle\VichUploaderBundle(),
            new EightPoints\Bundle\GuzzleBundle\GuzzleBundle(),
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
        ];
        $this->loadedBundles = array_merge($this->loadedBundles, $bundles);
    }

    /**
     * Load application bundles.
     */
    protected function loadAppBundles()
    {
        $bundles             = [
            new AppBundle\AppBundle(),
        ];
        $this->loadedBundles = array_merge($this->loadedBundles, $bundles);
    }

    /**
     * Load bundles switch env.
     */
    protected function loadEnvBundles()
    {
        $bundles = [];
        if (in_array($this->getEnvironment(), ['dev', 'test'], true)) {
            $bundles = [
                new Symfony\Bundle\DebugBundle\DebugBundle(),
                new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle(),
                new Sensio\Bundle\DistributionBundle\SensioDistributionBundle(),
                new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle(),
            ];
        }
        $this->loadedBundles = array_merge($this->loadedBundles, $bundles);
    }
}
