<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Models\Accessors;

use Spiral\Database\Entities\Driver;
use Spiral\Models\EntityInterface;
use Spiral\ODM\DocumentEntity;
use Spiral\ODM\ODM;
use Spiral\ORM\RecordAccessorInterface;

/**
 * JsonDocument utilizes abilities of ODM AbstractDocument and uses it to represent json values
 * stored inside ORM Record field. You can perform full set of Document operations including sub
 * documents, compositions (even aggregations!), validations and filtering to simplify work with
 * your denormalized data. If you going to use Postgres document fields can even be used in your
 * queries.
 *
 * @see PostgresDriver
 * @see http://www.postgresql.org/docs/9.3/static/datatype-json.html
 * @see http://www.postgresql.org/docs/9.3/static/functions-json.html
 */
abstract class JsonDocument extends DocumentEntity implements RecordAccessorInterface
{
    /**
     * Let's force solid state... just in case.
     *
     * @var bool
     */
    protected $solidState = true;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        $fields = [],
        EntityInterface $parent = null,
        ODM $odm = null,
        $odmSchema = null
    ) {
        if (is_string($fields)) {
            try {
                $fields = json_decode($fields, true);
            } catch (\ErrorException $exception) {
                $fields = [];
            }
        }

        parent::__construct($fields, $parent, $odm, $odmSchema);
    }

    /**
     * {@inheritdoc}
     */
    public function defaultValue(Driver $driver = null)
    {
        return $this->serializeData();
    }

    /**
     * {@inheritdoc}
     */
    public function serializeData()
    {
        return json_encode(parent::serializeData());
    }

    /**
     * {@inheritdoc}
     */
    public function compileUpdates($field = '')
    {
        //No atomic operations allowed
        return $this->serializeData();
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($data)
    {
        if (is_string($data)) {
            $data = json_decode($data);
        }

        return parent::setValue($data);
    }
}