<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Modules\Entities;

use Spiral\Core\Component;
use Spiral\Core\Configurator;
use Spiral\Core\DirectoriesInterface;
use Spiral\Debug\Traits\LoggerTrait;
use Spiral\Files\FilesInterface;
use Spiral\Modules\Exceptions\RegistratorException;
use Spiral\Modules\RegistratorInterface;

/**
 * Provides ability to modify existed configuration files and inject specific set of lines.
 *
 * All altered config files has to be checked for valid syntax and validation using associated
 * config class before saving.
 *
 * @todo add ability to validate altered configs using associated config class
 */
class Registrator extends Component implements RegistratorInterface
{
    use LoggerTrait;

    /**
     * Injected sections.
     *
     * @var array
     */
    private $injected = [];

    /**
     * @var ConfigInjector[]
     */
    protected $injectors = [];

    /**
     * @var FilesInterface
     */
    protected $files = null;

    /**
     * @var DirectoriesInterface
     */
    protected $directories = null;

    /**
     * @param FilesInterface $files
     */
    public function __construct(FilesInterface $files, DirectoriesInterface $directories)
    {
        $this->files = $files;
        $this->directories = $directories;
    }

    /**
     * {@inheritdoc}
     */
    public function configure($config, $placeholder, $id, array $lines)
    {
        //Injecting config values
        $this->injector($config)->inject($placeholder, $id, $lines);
        $this->injected[] = compact('config', 'placeholder', 'lines');
    }

    /**
     * List of injected config sections.
     *
     * @return array
     */
    public function getInjected()
    {
        return $this->injected;
    }

    /**
     * Validate and save all altered configuration files.
     */
    public function save()
    {
        foreach ($this->injectors as $config => $injector) {
            if (!$injector->checkSyntax()) {
                throw new RegistratorException(
                    "Config syntax of '{$config}' does not valid after registrations."
                );
            } else {
                $this->logger()->debug("Syntax of config '{$config}' has been checked.");
            }

            //Saving to file
            $this->files->write($this->configFilename($config), $injector->render());
            $this->logger()->info("Config '{$config}' were updated with new content.");
        }
    }

    /**
     * Get injector associated with specific config.
     *
     * @param string $config
     * @return ConfigInjector
     * @throws RegistratorException
     */
    protected function injector($config)
    {
        if (isset($this->injectors[$config])) {
            return $this->injectors[$config];
        }

        $content = $this->files->read($this->configFilename($config));

        //todo: move to container?
        return $this->injectors[$config] = new ConfigInjector($content);
    }

    /**
     * @param string $config
     * @return string
     * @throws RegistratorException
     */
    protected function configFilename($config)
    {
        $filename = $this->directories->directory('config') . $config . Configurator::EXTENSION;

        if (!$this->files->exists($filename)) {
            throw new RegistratorException("Unable to find filename for config '{$config}'.");
        }

        return $filename;
    }
}