<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Core\Bootloaders;

use Spiral\Core\Component;

/**
 * Provide ability to initiate set of container bindings using simple string form without closures.
 *
 * You can make any initializer automatically bootloadable by defining boot() method with
 * automatically resolved arguments.
 *
 * You can also declare Initializer classes as singletons while working using spiral container.
 * This is almost the same as ServiceProvider in Laravel.
 *
 * Attention, you are able to define your own set of shared (short bindings) components in your
 * bootloader, DO NOT share your business models this way - use regular DI.
 */
abstract class Bootloader extends Component implements BootloaderInterface
{
    /**
     * Not bootable by default.
     */
    const BOOT = false;

    /**
     * Bindings in string/array form, example:
     *
     * [
     *      'interface' => 'class',
     *      'class' => [self::class, 'createMethod']
     * ]
     *
     * @return array
     */
    protected $bindings = [];

    /**
     * Singletons in string/array form, example:
     *
     * [
     *      'class' => 'otherClass',
     *      'class' => [self::class, 'createMethod']
     * ]
     *
     * You don't need to bind classes which are declared with SINGLETON constant here, spiral will
     * resolve them as singleton automatically.
     *
     * @return array
     */
    protected $singletons = [];

    /**
     * {@inheritdoc}
     */
    public function defineBindings()
    {
        return $this->bindings;
    }

    /**
     * {@inheritdoc}
     */
    public function defineSingletons()
    {
        return $this->singletons;
    }
}