<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Session;

use Spiral\Session\Exceptions\SessionException;

/**
 * Singular session segment (session data isolator).
 *
 * @todo INACTIVE AT THIS MOMENT!
 * @todo hasChanged?
 */
interface SegmentInterface
{
    /**
     * All values stored in session.
     *
     * @return array
     * @throws SessionException
     */
    public function all();

    /**
     * Set data in session.
     *
     * @param string $name
     * @param mixed  $value
     * @return mixed
     * @throws SessionException
     */
    public function set($name, $value);

    /**
     * Check if value presented in session.
     *
     * @param string $name
     * @return bool
     * @throws SessionException
     */
    public function has($name);

    /**
     * Get value stored in session.
     *
     * @param string $name
     * @param mixed  $default
     * @return mixed
     * @throws SessionException
     */
    public function get($name, $default = null);

    /**
     * Delete data from session.
     *
     * @param string $name
     * @throws SessionException
     */
    public function delete($name);

    /**
     * Increment numeric value stored in cache. Must return incremented value.
     *
     * @param string $name
     * @param int    $delta How much to increment by. Set to 1 by default.
     * @return int
     * @throws SessionException
     */
    public function inc($name, $delta = 1);

    /**
     * Decrement numeric value stored in cache. Must return decremented value.
     *
     * @param string $name
     * @param int    $delta How much to decrement by. Set to 1 by default.
     * @return int
     * @throws SessionException
     */
    public function dec($name, $delta = 1);

    /**
     * Read item from session and delete it after.
     *
     * @param string $name
     * @return mixed
     * @throws SessionException
     */
    public function pull($name);
}
