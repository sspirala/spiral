<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Core\Containers;

use Spiral\Core\Container;
use Spiral\Core\ContainerInterface;

/**
 * Default spiral container with pre-defined set of core bindings (since this is default container
 * singleton flags are not forced).
 */
class SpiralContainer2 extends Container implements ContainerInterface
{
    /**
     * {@inheritdoc}
     *
     * @invisible
     */
    protected $bindings = [
        //Instrumental bindings
        'Psr\Log\LoggerInterface'                           => 'Spiral\Debug\SharedLogger',
        'Spiral\Debug\LogsInterface'                        => 'Spiral\Debug\Debugger',
        'Spiral\Encrypter\EncrypterInterface'               => 'Spiral\Encrypter\Encrypter',

        //Views
        'Spiral\Views\ViewsInterface'                       => 'Spiral\Views\ViewManager',

        //Storage manager interfaces
        'Spiral\Storage\StorageInterface'                   => 'Spiral\Storage\StorageManager',
        'Spiral\Storage\BucketInterface'                    => 'Spiral\Storage\Entities\StorageBucket',
        'Spiral\Session\SessionInterface'                   => 'Spiral\Session\SessionStore',

        //Validation and translation
        'Spiral\Validation\ValidatorInterface'              => 'Spiral\Validation\Validator',
        'Symfony\Component\Translation\TranslatorInterface' => 'Spiral\Translator\TranslatorInterface',
        'Spiral\Translator\TranslatorInterface'             => 'Spiral\Translator\Translator',
        'Spiral\Translator\SourceInterface'                 => 'Spiral\Translator\TranslationSource',

        //Http
        'Spiral\Http\HttpInterface'                         => 'Spiral\Http\HttpDispatcher',
        'Spiral\Http\Request\InputInterface'                => 'Spiral\Http\Input\InputManager',

        //Modules
        'Spiral\Modules\PublisherInterface'                 => 'Spiral\Modules\Entities\Publisher',
        'Spiral\Modules\RegistratorInterface'               => 'Spiral\Modules\Entities\Registrator',

        //Default snapshotter
        //  'Spiral\Debug\SnapshotInterface'                    => 'Spiral\Debug\QuickSnapshot'
    ];
}