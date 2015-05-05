<extends:http.layouts.error code="404" title="[[This page cannot be found]]"/>

<block:message>
    <?php
    /**
     * Expecting error to happen inside request scope declared by HttpDispatcher in perform() method.
     *
     * @var \Spiral\Components\Http\Request\Uri $uri
     */
    $uri = \Spiral\Core\Container::getInstance()->get('request')->getUri();
    ?>
    [[The requested URL]] <a href="<?= $uri->getPath() ?>"><?= $uri->getPath() ?></a> [[was not
    found on this server.]]
</block:message>
