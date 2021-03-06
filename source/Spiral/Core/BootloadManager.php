<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Core;

use Spiral\Core\Bootloaders\BootloaderInterface;

/**
 * Provides ability to bootload ServiceProviders.
 */
class BootloadManager
{
    /**
     * List of bootloaded classes.
     *
     * @var array
     */
    private $classes = [];

    /**
     * @invisible
     * @var ContainerInterface
     */
    protected $container = null;

    /**
     * @invisible
     * @var HippocampusInterface
     */
    protected $memory = null;

    /**
     * @param ContainerInterface   $container
     * @param HippocampusInterface $memory
     */
    public function __construct(ContainerInterface $container, HippocampusInterface $memory)
    {
        $this->container = $container;
        $this->memory = $memory;
    }

    /**
     * Bootload set of classes
     *
     * @param array       $classes
     * @param string|null $memory Memory section to be used for caching, set to null to disable
     *                            caching.
     */
    public function bootload(array $classes, $memory = null)
    {
        if (!empty($memory)) {
            $schema = $this->memory->loadData($memory);
        }

        if (empty($schema) || $schema['snapshot'] != $classes) {
            //Schema expired or empty
            $schema = $this->generateSchema($classes, $this->container);

            if (!empty($memory)) {
                $this->memory->saveData($memory, $schema);
            }

            return;
        }

        //We can initiate schema thought the cached schema
        $this->schematicBootload($this->container, $schema);
    }

    /**
     * Get bootloaded classes.
     *
     * @return array
     */
    public function getClasses()
    {
        return $this->classes;
    }

    /**
     * Bootload based on schema.
     *
     * @param ContainerInterface $container
     * @param array              $schema
     */
    protected function schematicBootload(ContainerInterface $container, array $schema)
    {
        foreach ($schema['bootloaders'] as $bootloader => $options) {
            $this->classes[] = $bootloader;

            if (array_key_exists('bindings', $options)) {
                $this->initBindings($container, $options);
            }

            if ($options['init']) {
                $object = $container->get($bootloader);

                //Booting
                if ($options['boot']) {
                    $boot = new \ReflectionMethod($object, 'boot');
                    $boot->invokeArgs($object, $container->resolveArguments($boot));
                }
            }
        }
    }

    /**
     * Generate cached bindings schema.
     *
     * @param array              $classes
     * @param ContainerInterface $container
     * @return array
     */
    protected function generateSchema(array $classes, ContainerInterface $container)
    {
        $schema = [
            'snapshot'    => $classes,
            'bootloaders' => []
        ];

        foreach ($classes as $class) {
            $this->classes[] = $class;

            $initSchema = ['init' => true, 'boot' => false];
            $bootloader = $container->get($class);

            if ($bootloader instanceof BootloaderInterface) {
                $initSchema['bindings'] = $bootloader->defineBindings();
                $initSchema['singletons'] = $bootloader->defineSingletons();

                $reflection = new \ReflectionClass($bootloader);

                //Can be booted based on it's configuration
                $initSchema['boot'] = (bool)$reflection->getConstant('BOOT');
                $initSchema['init'] = $initSchema['boot'];

                //Let's initialize now
                $this->initBindings($container, $initSchema);
            } else {
                $initSchema['init'] = true;
            }

            //Need more checks here
            if ($initSchema['boot']) {
                $boot = new \ReflectionMethod($bootloader, 'boot');
                $boot->invokeArgs($bootloader, $container->resolveArguments($boot));
            }

            $schema['bootloaders'][$class] = $initSchema;
        }

        return $schema;
    }

    /**
     * Bind declared bindings.
     *
     * @param ContainerInterface $container
     * @param array              $bootSchema
     */
    protected function initBindings(ContainerInterface $container, array $bootSchema)
    {
        foreach ($bootSchema['bindings'] as $aliases => $resolver) {
            $container->bind($aliases, $resolver);
        }

        foreach ($bootSchema['singletons'] as $aliases => $resolver) {
            $container->bindSingleton($aliases, $resolver);
        }
    }
}