<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM\Schemas\Relations;

use Spiral\Components\ORM\Entity;
use Spiral\Components\ORM\ORMException;
use Spiral\Components\ORM\Schemas\MorphedRelationSchema;

class ManyToMorphedSchema extends MorphedRelationSchema
{
    /**
     * Relation type.
     */
    const RELATION_TYPE = Entity::MANY_TO_MORPHED;

    /**
     * Default definition parameters, will be filled if parameter skipped from definition by user.
     *
     * @invisible
     * @var array
     */
    protected $defaultDefinition = array(
        Entity::PIVOT_TABLE       => '{name:singular}_map',
        Entity::INNER_KEY         => '{entity:primaryKey}',
        Entity::OUTER_KEY         => '{outer:primaryKey}',
        Entity::THOUGHT_INNER_KEY => '{entity:roleName}_{definition:INNER_KEY}',
        Entity::THOUGHT_OUTER_KEY => '{name:singular}_{definition:OUTER_KEY}',
        Entity::MORPH_KEY         => '{name:singular}_type',
        Entity::CONSTRAINT        => true,
        Entity::CONSTRAINT_ACTION => 'CASCADE',
        Entity::CREATE_PIVOT      => true
    );

    /**
     * Create all required relation columns, indexes and constraints.
     */
    public function buildSchema()
    {
        if (empty($this->outerEntities) || !$this->definition[Entity::CREATE_PIVOT])
        {
            //No targets found, no need to generate anything
            return;
        }

        $pivotTable = $this->ormSchema->declareTable(
            $this->entitySchema->getDatabase(),
            $this->definition[Entity::PIVOT_TABLE]
        );

        $pivotTable->bigPrimary('id');

        $localKey = $pivotTable->column($this->definition[Entity::THOUGHT_INNER_KEY]);
        $localKey->type($this->getInnerKeyType());
        $localKey->index();

        $morphKey = $pivotTable->column($this->definition[Entity::MORPH_KEY]);
        $morphKey->string(static::TYPE_COLUMN_SIZE);

        $outerKey = $pivotTable->column($this->definition[Entity::THOUGHT_OUTER_KEY]);
        $outerKey->type($this->getOuterKeyType());

        //Complex index
        $pivotTable->unique(
            $this->definition[Entity::INNER_KEY],
            $this->definition[Entity::MORPH_KEY],
            $this->definition[Entity::OUTER_KEY]
        );

        if ($this->definition[Entity::CONSTRAINT])
        {
            $foreignKey = $localKey->foreign(
                $this->entitySchema->getTable(),
                $this->entitySchema->getPrimaryKey()
            );
            $foreignKey->onDelete($this->definition[Entity::CONSTRAINT_ACTION]);
            $foreignKey->onUpdate($this->definition[Entity::CONSTRAINT_ACTION]);
        }
    }

    /**
     * Create reverted relations in outer entity or entities.
     *
     * @param string $name Relation name.
     * @param int    $type Back relation type, can be required some cases.
     * @throws ORMException
     */
    public function revertRelation($name, $type = null)
    {
        foreach ($this->getOuterEntities() as $entity)
        {
            $entity->addRelation($name, array(
                Entity::MANY_TO_MANY      => $this->entitySchema->getClass(),
                Entity::PIVOT_TABLE       => $this->definition[Entity::PIVOT_TABLE],
                Entity::OUTER_KEY         => $this->definition[Entity::INNER_KEY],
                Entity::INNER_KEY         => $this->definition[Entity::OUTER_KEY],
                Entity::THOUGHT_INNER_KEY => $this->definition[Entity::THOUGHT_OUTER_KEY],
                Entity::THOUGHT_OUTER_KEY => $this->definition[Entity::THOUGHT_INNER_KEY],
                Entity::MORPH_KEY         => $this->definition[Entity::MORPH_KEY],
                Entity::CREATE_PIVOT      => $this->definition[Entity::CREATE_PIVOT]
            ));
        }
    }
}