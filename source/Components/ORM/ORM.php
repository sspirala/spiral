<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM;

use Spiral\Components\DBAL\DatabaseManager;
use Spiral\Components\ORM\Exporters\DocumentationExporter;
use Spiral\Components\ORM\Schemas\RecordSchema;
use Spiral\Components\ORM\Schemas\RelationSchemaInterface;
use Spiral\Components\ORM\Selector\LoaderInterface;
use Spiral\Core\Component;
use Spiral\Core\ConfiguratorInterface;
use Spiral\Core\Container;
use Spiral\Core\CoreInterface;
use Spiral\Core\RuntimeCacheInterface;

class ORM extends Component
{
    /**
     * Required traits.
     */
    use Component\SingletonTrait, Component\ConfigurableTrait, Component\EventsTrait;

    /**
     * Declares to IoC that component instance should be treated as singleton.
     */
    const SINGLETON = __CLASS__;

    /**
     * Core component.
     *
     * @var CoreInterface
     */
    protected $runtime = null;

    /**
     * DatabaseManager.
     *
     * @var DatabaseManager
     */
    protected $dbal = null;

    /**
     * Container instance.
     *
     * @invisible
     * @var Container
     */
    protected $container = null;

    /**
     * Loaded entities schema. Schema contains full description about model behaviours, relations,
     * columns and etc.
     *
     * @var array|null
     */
    protected $schema = null;

    /**
     * ORM component instance.
     *
     * @param ConfiguratorInterface $configurator
     * @param RuntimeCacheInterface $runtime
     * @param DatabaseManager       $dbal
     * @param Container             $container
     */
    public function __construct(
        ConfiguratorInterface $configurator,
        RuntimeCacheInterface $runtime,
        DatabaseManager $dbal,
        Container $container
    )
    {
        $this->runtime = $runtime;
        $this->dbal = $dbal;
        $this->container = $container;

        $this->config = $configurator->getConfig('orm');
    }

    /**
     * Change associated DatabaseManager.
     *
     * @param DatabaseManager $dbal
     */
    public function setDBAL(DatabaseManager $dbal)
    {
        $this->dbal = $dbal;
    }

    /**
     * Most of classes with ORM access will need DatabaseManager as well.
     *
     * @return DatabaseManager
     */
    public function getDBAL()
    {
        return $this->dbal;
    }

    /**
     * Get schema for specified document class or collection.
     *
     * @param string $item   Document class or collection name (including database).
     * @param bool   $update Automatically update schema if requested schema is missing.
     * @return mixed
     */
    public function getSchema($item, $update = true)
    {
        if ($this->schema === null)
        {
            $this->schema = $this->runtime->loadData('ormSchema');
        }

        if (!isset($this->schema[$item]) && $update)
        {
            $this->updateSchema();
        }

        return $this->schema[$item];
    }




    //    public function getRelation(
    //        ActiveRecord $parent = null,
    //        $type,
    //        array $definition,
    //        $data = []
    //    )
    //    {
    //        $class = $this->config['relations'][$type]['class'];
    //
    //        return new $class($this, $parent, $definition, $data);
    //    }

    /**
     * Get instance of Loader associated with relation type and relation defitition.
     *
     * @param int             $type       Relation type.
     * @param string          $container  Container related to parent loader.
     * @param array           $definition Relation definition.
     * @param LoaderInterface $parent     Parent loader (if presented).
     * @return LoaderInterface
     */
    public function getLoader($type, $container, array $definition, LoaderInterface $parent = null)
    {
        $class = $this->config['relations'][$type]['loader'];

        //TODO: we may need add container here, due some loaders may have external requiments
        return new $class($this, $container, $definition, $parent);
    }

    /**
     * Get ORM schema reader. Schema will detect all declared entities, their tables, columns,
     * relationships and etc.
     *
     * @return SchemaBuilder
     */
    public function schemaBuilder()
    {
        return SchemaBuilder::make([
            'config' => $this->config,
            'dbal'   => $this->dbal
        ], $this->container);
    }

    /**
     * Refresh ODM schema state, will reindex all found document models and render documentation for
     * them. This is slow method using Tokenizer, refreshSchema() should not be called by user request.
     *
     * @return SchemaBuilder
     */
    public function updateSchema()
    {
        $builder = $this->schemaBuilder();

        if (!empty($this->config['documentation']))
        {
            //Virtual ORM documentation to help IDE
            DocumentationExporter::make(compact('builder'), $this->container)->render(
                $this->config['documentation']
            );
        }

        //Building database!
        $builder->executeSchema();

        $this->schema = $this->event('schema', $builder->normalizeSchema());
        ActiveRecord::clearSchemaCache();

        //Saving
        $this->runtime->saveData('ormSchema', $this->schema);

        return $builder;
    }

    /**
     * Normalized entity constants.
     */
    const E_ROLE_NAME   = 0;
    const E_TABLE       = 1;
    const E_DB          = 2;
    const E_COLUMNS     = 3;
    const E_HIDDEN      = 4;
    const E_SECURED     = 5;
    const E_FILLABLE    = 6;
    const E_MUTATORS    = 7;
    const E_VALIDATES   = 8;
    const E_RELATIONS   = 9;
    const E_PRIMARY_KEY = 10;

    /**
     * Normalized relation options.
     */
    const R_TYPE       = 0;
    const R_TABLE      = 1;
    const R_DEFINITION = 2;

    /**
     * Key to be used as primary for pivot tables.
     */
    const PIVOT_PRIMARY_KEY = 'id';

    /**
     * Pivot table location in ActiveRecord data.
     */
    const PIVOT_DATA = '@pivot';
}