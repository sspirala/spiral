<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\View\ConfigWriter;

use Spiral\Components\Files\FileManager;
use Spiral\Components\Tokenizer\Tokenizer;
use Spiral\Support\Generators\Config\ConfigWriter;
use Spiral\Support\Generators\Config\ConfigWriterException;

class ViewConfig extends ConfigWriter
{
    /**
     * View namespaces used to describe location of module view files, any count of namespaces can be
     * created.
     *
     * @var array
     */
    protected $viewNamespaces = array();

    /**
     * View processors has to be registered and their location in chain.
     *
     * @var array
     */
    protected $processors = array();

    /**
     * Base directory for registered namespaces.
     *
     * @var string
     */
    protected $baseDirectory = '';

    /**
     * Config class used to update application configuration files with new sections, data and presets,
     * it can resolve new config data by merging already exists presets with requested setting by one
     * of specified merge methods.
     *
     * ViewConfig is specialized configurator allows modules mount view namespaces and view processors.
     *
     * @param FileManager $file          FileManager component.
     * @param Tokenizer   $tokenizer     Tokenizer component.
     * @param string      $name          Config filename, should not include extensions, may include
     *                                   directory name.
     * @param int         $method        How system should merge existed and requested config contents.
     * @param string      $baseDirectory Base directory for registered namespaces.
     */
    public function __construct(
        FileManager $file,
        Tokenizer $tokenizer,
        $name = 'views',
        $method = self::MERGE_CUSTOM,
        $baseDirectory = ''
    )
    {
        $this->file = $file;
        $this->tokenizer = $tokenizer;
        $this->name = $name;
        $this->method = $method;
        $this->baseDirectory = $baseDirectory;
    }

    /**
     * Set view namespaces base directory.
     *
     * @param string $baseDirectory
     * @return static
     */
    public function baseDirectory($baseDirectory)
    {
        $this->baseDirectory = $baseDirectory;

        return $this;
    }

    /**
     * Register view namespace linked to module directory. If following module supports view layouts,
     * it's recommended to specify multiple view namespaces to support layout extensions.
     *
     * Examples:
     * $viewConfig->viewNamespace('keeper', 'views');
     * $viewConfig->viewNamespace('keeper.origin', 'views');
     *
     * Second namespace will allow application to replace some layout file but still inherit it from
     * original view.
     *
     * @param string $namespace View namespace.
     * @param string $directory Directory name relative to modules directory.
     * @return ViewConfig
     */
    public function addNamespace($namespace, $directory = 'views')
    {
        $this->viewNamespaces[$namespace] = $directory;

        return $this;
    }

    /**
     * Read configuration file from some specified directory (application or module config folder).
     *
     * @param string $directory Director where config should be located.
     * @throws ConfigWriterException
     */
    public function readConfig($directory)
    {
        //No need to read module view config
    }

    /**
     * Methods will be applied to merge existed and custom configuration data in merge method is
     * specified as Config::mergeCustom. This method usually used to perform logical merge.
     *
     * @param mixed $internal Requested configuration data.
     * @param mixed $existed  Existed configuration data.
     * @return mixed
     */
    protected function customMerge($internal, $existed)
    {
        $result = $existed;

        foreach ($this->viewNamespaces as $namespace => $directory)
        {
            $directory = $this->file->normalizePath($this->baseDirectory . '/' . $directory, true);

            $result['namespaces'][$namespace][] = $directory;
            foreach ($result['namespaces'][$namespace] as &$namespaceDirectory)
            {
                $namespaceDirectory = $this->file->normalizePath($namespaceDirectory, true);
                unset($namespaceDirectory);
            }

            $result['namespaces'][$namespace] = array_unique($result['namespaces'][$namespace]);
        }

        return $result;
    }
}