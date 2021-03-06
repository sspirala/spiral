<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Core;

use Interop\Container\ContainerInterface as InteropContainer;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\Traits\SaturateTrait;
use Spiral\Core\Traits\SharedTrait;

/**
 * Generic spiral service only provide simplified access to shared components and instances.
 */
class Service extends Component implements SingletonInterface
{
    /**
     * Access to shared components and entities.
     */
    use SharedTrait, SaturateTrait;

    /**
     * @var InteropContainer
     */
    protected $container = null;

    /**
     * @param InteropContainer $container Sugared. Used to drive shared/virtual bindings, if any.
     */
    public function __construct(InteropContainer $container = null)
    {
        $this->container = $this->saturate($container, InteropContainer::class);
    }
}