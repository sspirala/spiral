<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */
namespace Spiral\Http\Request;

use Spiral\Validation\ValidatorInterface;

/**
 * Helper class needed to properly initiate request values and mount messages.
 */
class InputRouter
{
    /**
     * Default data source (POST).
     */
    const DEFAULT_SOURCE = 'data';

    /**
     * Used to define multiple nested models.
     */
    const NESTED_CLASS     = 0;
    const ISOLATION_PREFIX = 1;

    /**
     * Input routing schema, must be compatible with RequestFilter.
     *
     * @var array
     */
    private $schema = [];

    /**
     * @param array $schema
     */
    public function __construct(array $schema)
    {
        $this->schema = $this->normalizeSchema($schema);
    }

    /**
     * Create set of values based on a given schema.
     *
     * @param InputInterface     $input
     * @param ValidatorInterface $validator Validator instance to be used for nested models.
     *
     * @return array
     */
    public function createValues(InputInterface $input, ValidatorInterface $validator = null): array
    {
        $result = [];
        foreach ($this->schema as $field => $map) {
            if (!empty($map['class'])) {
                $class = $map['class'];

                //Working with nested models
                if ($map['multiple']) {
                    //Create model for each key in origin
                    foreach ($this->createOrigins($input, $map) as $index => $origin) {
                        $result[$field][$index] = new $class(
                            $input->withPrefix($origin),
                            !empty($validator) ? clone $validator : null
                        );
                    }
                    continue;
                }

                //Initiating sub model
                $result[$field] = new $class(
                    $input->withPrefix($map['origin']),
                    !empty($validator) ? clone $validator : null
                );

                continue;
            }

            //Reading value from input
            $result[$field] = $input->getValue($map['source'], $map['origin']);
        }

        return $result;
    }

    /**
     * Alter errors array so each field error associated with proper input origin name.
     *
     * @param array $errors
     *
     * @return array
     */
    public function originateErrors(array $errors): array
    {
        //De-mapping
        $mapped = [];
        foreach ($errors as $field => $message) {
            if (isset($this->schema[$field])) {
                //Mounting errors in a proper location
                $this->mountMessage($mapped, $this->schema[$field]['origin'], $message);
            } else {
                //Custom error
                $mapped[$field] = $mapped;
            }
        }

        return $mapped;
    }

    /**
     * Set element using dot notation.
     *
     * @param array  $array
     * @param string $path
     * @param mixed  $value
     */
    protected function mountMessage(array &$array, string $path, $value)
    {
        $step = explode('.', $path);
        while ($name = array_shift($step)) {
            $array = &$array[$name];
        }

        $array = $value;
    }

    /**
     * Pre-processing schema in order to property define field mapping.
     *
     * @param array $schema
     *
     * @return array
     */
    protected function normalizeSchema(array $schema): array
    {
        $result = [];
        foreach ($schema as $field => $definition) {
            //Short definition
            if (is_string($definition)) {
                if (class_exists($definition)) {
                    //Singular nested model
                    $result[$field] = [
                        'class'    => $definition,
                        'source'   => self::DEFAULT_SOURCE,
                        'origin'   => $field,
                        'multiple' => false
                    ];
                } else {
                    //Simple scalar field definition
                    list($source, $origin) = $this->parseDefinition($field, $definition);
                    $result[$field] = compact('source', 'origin');
                }

                continue;
            }

            //Complex definition
            if (is_array($definition)) {
                if (!empty($definition[self::ISOLATION_PREFIX])) {
                    list($source, $origin) = $this->parseDefinition($field, $definition[1]);

                    $result[$field] = [
                        'class'    => $definition[self::NESTED_CLASS],
                        'source'   => $source,
                        'origin'   => rtrim($origin, '.*'),
                        'multiple' => strpos($origin, '*') !== false
                    ];
                } else {
                    //Array of models (default isolation prefix)
                    $result[$field] = [
                        'class'    => $definition[self::NESTED_CLASS],
                        'source'   => self::DEFAULT_SOURCE,
                        'origin'   => $field,
                        'multiple' => true
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * Fetch source name and origin from schema definition.
     *
     * @param string $field
     * @param mixed  $definition
     *
     * @return array [$source, $origin, $class, $iterate]
     */
    private function parseDefinition(string $field, $definition): array
    {
        if (strpos($definition, ':') === false) {
            return [self::DEFAULT_SOURCE, $field];
        }

        return explode(':', $definition);
    }

    /**
     * Create set of origins and prefixed for a nested array of models.
     *
     * @param InputInterface $input
     * @param array          $definition
     *
     * @return array
     */
    private function createOrigins(InputInterface $input, array $definition)
    {
        $result = [];

        $iteration = $input->getValue($definition['source'], $definition['origin']);
        if (empty($iteration) || !is_array($iteration)) {
            return [];
        }

        foreach (array_keys($iteration) as $key) {
            $result[$key] = $definition['origin'] . '.' . $key;
        }

        return $result;
    }
}