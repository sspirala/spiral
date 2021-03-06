<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Views\Engines\Stempler;

use Spiral\Files\FilesInterface;
use Spiral\Views\EnvironmentInterface;
use Spiral\Views\LoaderInterface;

/**
 * Very simple Stempler cache. Almost identical to twig cache except generateKey method.
 */
class StemplerCache
{
    /**
     * @var FilesInterface
     */
    protected $files = null;

    /**
     * @var EnvironmentInterface
     */
    protected $environment = null;

    /**
     * TwigCache constructor.
     *
     * @param FilesInterface       $files
     * @param EnvironmentInterface $environment
     * @param LoaderInterface      $loader
     */
    public function __construct(
        FilesInterface $files,
        EnvironmentInterface $environment,
        LoaderInterface $loader
    ) {
        $this->files = $files;
        $this->environment = $environment;
        $this->loader = $loader;
    }

    /**
     * Generate cache key for given path.
     *
     * @param string $path
     * @return string
     */
    public function generateKey($path)
    {
        $namespace = $this->loader->viewNamespace($path);
        $name = $this->loader->viewName($path);

        $hash = hash('md5', $namespace . ':' . $name . '.' . $this->environment->getID());

        return $this->environment->cacheDirectory() . '/' . $hash . '.php';
    }

    /**
     * Get local cache filename (to be included in view).
     *
     * @param string $key
     * @return string
     */
    public function cachedFilename($key)
    {
        return $this->files->localUri($key);
    }

    /**
     * Store data into cache.
     *
     * @param string $key
     * @param string $content
     */
    public function write($key, $content)
    {
        $this->files->write($key, $content, FilesInterface::RUNTIME, true);
    }

    /**
     * Last update time.
     *
     * @param string $key
     * @return int
     */
    public function getTimestamp($key)
    {
        if (!$this->environment->cachable()) {
            //Always expired
            return 0;
        }

        if ($this->files->exists($key)) {
            return $this->files->time($key);
        }

        return 0;
    }
}