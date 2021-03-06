<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Http\Routing;

use Spiral\Http\Routing\Traits\CoreTrait;

/**
 * {@inheritdoc} General purpose route.
 */
class Route extends AbstractRoute
{
    use CoreTrait;

    /**
     * Use this string as your target action to resolve action from routed URL.
     *
     * Example: new Route('name', 'userPanel/<action>', 'Controllers\UserPanel::<action>');
     *
     * Attention, you can't route controllers this way, use DirectRoute for such purposes.
     */
    const DYNAMIC_ACTION = '<action>';

    /**
     * Route target in a form of callable or string pattern.
     *
     * @var callable|string
     */
    protected $target = null;

    /**
     * New Route instance.
     *
     * @param string          $name
     * @param string          $pattern
     * @param string|callable $target Route target. Can be in a form of controler:action
     * @param array           $defaults
     */
    public function __construct($name, $pattern, $target, array $defaults = [])
    {
        parent::__construct($name, $defaults);

        $this->pattern = $pattern;
        $this->target = $target;
    }

    /**
     * {@inheritdoc}
     */
    protected function createEndpoint()
    {
        if (is_object($this->target) || is_array($this->target)) {
            return $this->target;
        }

        if (is_string($this->target) && strpos($this->target, ':') === false) {
            //Endpoint
            return $this->container()->get($this->target);
        }

        $route = $this;

        return function () use ($route) {
            list($controller, $action) = explode(':', str_replace('::', ':', $route->target));

            if ($action == self::DYNAMIC_ACTION) {
                $action = $route->getMatches()['action'];
            }

            return $route->callAction($controller, $action, $route->getMatches());
        };
    }
}