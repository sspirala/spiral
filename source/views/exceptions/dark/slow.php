<!DOCTYPE html>
<?php
/**
 * @see http://ppig.org/sites/default/files/2015-PPIG-26th-Sarkar.pdf
 * @var Throwable $exception
 */
$dumper = new \Spiral\Debug\Dumper(10, $styler = new \Spiral\Debug\Dumper\InversedStyle());
$dumps = [];

/**
 * Format arguments and create data dumps.
 *
 * @param array $arguments
 *
 * @return array
 */
$argumenter = function (array $arguments) use ($dumper, $styler, &$dumps) {
    $result = [];
    foreach ($arguments as $argument) {
        $display = $type = strtolower(gettype($argument));

        if (is_numeric($argument)) {
            $result[] = $styler->apply($argument, 'value', $type);
            continue;
        } elseif (is_bool($argument)) {
            $result[] = $styler->apply($argument ? 'true' : 'false', 'value', $type);
            continue;
        } elseif (is_null($argument)) {
            $result[] = $styler->apply('null', 'value', $type);
            continue;
        }

        if (is_object($argument)) {
            $reflection = new ReflectionClass($argument);
            $display = \Spiral\interpolate(
                "<span title=\"{title}\">{class}</span>", [
                    'title' => $reflection->getName(),
                    'class' => $reflection->getShortName()
                ]
            );
        }

        //Colorizing
        $display = $styler->apply($display, 'value', $type);
        if (($dumpID = array_search($argument, $dumps)) === false) {
            $dumps[] = $dumper->dump($argument, \Spiral\Debug\Dumper::OUTPUT_RETURN);
            $dumpID = count($dumps) - 1;
        }

        $display = \Spiral\interpolate(
            "<span onclick=\"_da({dumpID})\">{display}</span>",
            compact('display', 'dumpID')
        );

        $result[] = $display;
    }

    return $result;
};
?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>
        <?= \Spiral\Support\ExceptionHelper::createMessage($exception) ?>
    </title>
    <style>
        body.spiral-exception {
            font-family: Helvetica, sans-serif;
            background-color: #25000a;
            font-size: 14px;
            padding: 5px;
            color: #a1a1a1;
        }

        .spiral-exception .wrapper {
            padding: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, .7);
            background-color: #3a3a3a;
        }

        .spiral-exception .wrapper strong {
            font-weight: bold;
        }

        .spiral-exception .wrapper i {
            font-style: italic;
        }

        .spiral-exception .dump {
            padding: 5px;
            background-color: #232323;
            margin-top: 0;
            display: none;
            overflow-x: auto;
        }

        .spiral-exception .wrapper .header {
            margin-bottom: 5px;
            background: #d3833b;
            border: 2px solid #d3833b;
            padding: 8px 13px 8px 18px;
            color: #fff;
            box-shadow: inset 0 0 8px rgba(0, 0, 0, .2);
        }

        .spiral-exception .wrapper .stacktrace {
            display: inline-block;
            width: 100%;
        }

        .spiral-exception .wrapper .stacktrace .trace {
            font-family: Monospace;
            float: left;
            width: 60%;
        }

        .spiral-exception .wrapper .stacktrace .trace .container {
            padding: 15px;
            background-color: #2e2e2e;
            margin-bottom: 5px;
            overflow-x: auto;
            box-shadow: inset 0 0 20px rgba(0, 0, 0, .2);
        }

        .spiral-exception .wrapper .stacktrace .trace .container.no-trace {
            color: #6bbdff;
        }

        .spiral-exception .wrapper .stacktrace .trace .container.no-trace .arguments span {
            cursor: pointer;
        }

        .spiral-exception .wrapper .stacktrace .trace .container.no-trace .arguments span:hover {
            text-decoration: underline;
        }

        .spiral-exception .wrapper .stacktrace .trace .location {
            color: #6bbdff;
            margin-bottom: 5px;
        }

        .spiral-exception .wrapper .stacktrace .trace .location .arguments span:hover {
            text-decoration: underline;
            cursor: pointer;
        }

        .spiral-exception .wrapper .stacktrace .trace .location em {
            color: #a1a1a1;
            font-style: normal;
        }

        .spiral-exception .wrapper .stacktrace .trace .lines div {
            white-space: pre;
            color: #E6E1DC;
        }

        .spiral-exception .wrapper .stacktrace .trace .lines div .number {
            display: inline-block;
            width: 50px;
            color: #757575;
        }

        .spiral-exception .wrapper .stacktrace .trace .lines div:hover {
            background-color: #404040;
        }

        .spiral-exception .wrapper .stacktrace .trace .lines div.highlighted {
            background-color: #404040;
        }

        .spiral-exception .wrapper .stacktrace .chain {
            width: 40%;
            float: right;
        }

        .spiral-exception .wrapper .stacktrace .chain .calls {
            padding: 10px 10px 10px 10px;
            margin-left: 5px;
            background-color: #2e2e2e;
            margin-bottom: 5px;
            overflow-x: auto;
            box-shadow: inset 0 0 20px rgba(0, 0, 0, .2);
        }

        .spiral-exception .wrapper .stacktrace .chain .call .function {
            font-size: 11px;
            color: #6bbdff;
        }

        .spiral-exception .wrapper .stacktrace .chain .call .function .arguments span {
            cursor: pointer;
        }

        .spiral-exception .wrapper .stacktrace .chain .call .function .arguments span:hover {
            text-decoration: underline;
        }

        .spiral-exception .wrapper .stacktrace .chain .call .location {
            margin-bottom: 10px;
            font-size: 10px;
        }

        .spiral-exception .wrapper .stacktrace .chain .dumper {
            padding-left: 5px;
            padding-bottom: 5px;
            display: none;
        }

        .spiral-exception .wrapper .stacktrace .chain .dumper .close {
            text-align: right;
            padding: 2px;
            color: #fff;
            cursor: pointer;
            font-size: 12px;
            background-color: #232323;
        }

        .spiral-exception .wrapper .stacktrace .chain .dumper .close:hover {
            background-color: #2c2c2c;
        }

        .spiral-exception .wrapper .environment .container {
            margin-bottom: 9px;
        }

        .spiral-exception .wrapper .environment .title, .spiral-exception .wrapper .messages .title {
            padding: 10px 10px 10px 5px;
            background-color: #e7c35e;
            font-weight: bold;
            color: #444;
            cursor: pointer;
        }

        .spiral-exception .wrapper .footer {
            margin-top: 10px;
            margin-bottom: 5px;
            font-size: 12px;
        }

        .spiral-exception .wrapper .footer .date {
            color: #fafafa;
        }
    </style>
    <script type="text/javascript">
        function toggle(id) {
            var block = document.getElementById(id);
            block.style.display = (block.style.display == 'none' ? 'block' : 'none');
        }
        function _da(id) {
            var dump = document.getElementById('argument-dumper');
            dump.style.display = 'block';
            dump.innerHTML = '<div class="close" onclick="toggle(\'argument-dumper\')"> &cross; close</div> '
                + '<div class="dump" style="display: block">'
                + document.getElementById('argument-' + id).innerHTML
                + '</div>';
        }
    </script>
</head>
<body class="spiral-exception">
<a name="dump-top"></a>

<div class="wrapper">
    <div class="header">
        <?= get_class($exception) ?>:
        <strong><?= $exception->getMessage() ?></strong>
        in&nbsp;<i><?= $exception->getFile() ?></i>&nbsp;at&nbsp;<strong>line&nbsp;<?= $exception->getLine() ?></strong>

        <span style="float: right; opacity: 0.7;">SLOW MODE</span>
    </div>

    <div class="stacktrace">
        <div class="trace">
            <?php
            $stacktrace = $exception->getTrace();

            //Let's let's clarify exception location
            $header = [
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine()
                ] + $stacktrace[0];

            if ($stacktrace[0] != $header) {
                array_unshift($stacktrace, $header);
            }

            foreach ($stacktrace as $trace) {

                $arguments = [];
                if (isset($trace['args'])) {
                    $arguments = $argumenter($trace['args']);
                }

                $function = '<strong>' . $trace['function'] . '</strong>';
                if (isset($trace['type']) && isset($trace['class'])) {
                    $reflection = new ReflectionClass($trace['class']);
                    $function = \Spiral\interpolate(
                        "<span title=\"{title}\">{class}</span>{type}{function}",
                        [
                            'title'    => $reflection->getName(),
                            'class'    => $reflection->getShortName(),
                            'type'     => $trace['type'],
                            'function' => $function
                        ]
                    );
                }

                if (!isset($trace['file'])) {
                    ?>
                    <div class="container no-trace">
                        <?= $function ?>(<span class="arguments"><?= join(
                                ', ',
                                $arguments
                            ) ?></span>)
                    </div>
                    <?php
                    continue;
                }

                ?>
                <div class="container">
                    <div class="location">
                        <?= $function ?>(<span class="arguments"><?= join(
                                ', ',
                                $arguments
                            ) ?></span>)<br/>
                        <em>
                            In&nbsp;<?= $trace['file'] ?>&nbsp;at&nbsp;<strong>line <?= $trace['line'] ?></strong>
                        </em>
                    </div>
                    <div class="lines">
                        <?= \Spiral\Support\ExceptionHelper::highlightSource(
                            $trace['file'],
                            $trace['line'],
                            10,
                            new \Spiral\Tokenizer\Highlighter\InversedStyle()
                        ) ?>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
        <div class="chain">
            <div class="calls">
                <?php
                foreach (array_reverse($stacktrace) as $index => $trace) {
                    if (empty($trace['file']) && isset($stacktrace[$index - 1]['file'])) {
                        $trace['file'] = $stacktrace[$index - 1]['file'];
                        $trace['line'] = $stacktrace[$index - 1]['line'];
                    }

                    if (!isset($stacktrace[$index + 1])) {
                        $trace['file'] = $exception->getFile();
                        $trace['line'] = $exception->getLine();
                    }

                    if (!isset($trace['function']) || !isset($trace['file'])) {
                        continue;
                    }

                    $function = '<strong>' . $trace['function'] . '</strong>';
                    if (isset($trace['type']) && isset($trace['class'])) {
                        $reflection = new ReflectionClass($trace['class']);
                        $function = \Spiral\interpolate(
                            "<span title=\"{title}\">{class}</span>{type}{function}",
                            [
                                'title'    => $reflection->getName(),
                                'class'    => $reflection->getShortName(),
                                'type'     => $trace['type'],
                                'function' => $function
                            ]
                        );
                    }

                    $arguments = [];
                    if (isset($trace['args'])) {
                        $arguments = $argumenter($trace['args']);
                    }

                    ?>
                    <div class="call">
                        <div class="function">
                            <?= $function ?>(<span class="arguments"><?= join(
                                    ', ',
                                    $arguments
                                ) ?></span>)
                        </div>
                        <div class="location">
                            <i><?= $trace['file'] ?></i> at
                            <strong>line <?= $trace['line'] ?></strong>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
            <div class="dumper" id="argument-dumper"></div>
        </div>
    </div>

    <div class="environment">
        <?php
        $variables = [
            'GET'     => '_GET',
            'POST'    => '_POST',
            'COOKIES' => '_COOKIE',
            'SESSION' => '_SESSION',
            'SERVER'  => '_SERVER',
        ];

        foreach ($variables as $name => $variable) {
            if (!empty($GLOBALS[$variable])) {
                if ($name == 'SERVER' && isset($_SERVER['PATH']) && is_string($_SERVER['PATH'])) {
                    $_SERVER['PATH'] = explode(PATH_SEPARATOR, $_SERVER['PATH']);
                }

                ?>
                <div class="container">
                    <div class="title" onclick="toggle('environment-<?= $name ?>')">
                        <?= $name ?> (<?= number_format(count($GLOBALS[$variable])) ?>)
                    </div>
                    <div class="dump" id="environment-<?= $name ?>" style="display: none;">
                        <?= $dumper->dump($GLOBALS[$variable], \Spiral\Debug\Dumper::OUTPUT_RETURN) ?>
                    </div>
                </div>
                <?php
            }
        }
        ?>
    </div>
    <div class="footer">
        <div class="date"><?= date('r') ?></div>
        <div class="elapsed time">
            <?php
            if (!defined('SPIRAL_INITIAL_TIME')) {
                define(
                    'SPIRAL_INITIAL_TIME',
                    isset($_SERVER['REQUEST_TIME_FLOAT']) ? $_SERVER['REQUEST_TIME_FLOAT'] : 0
                );
            }
            ?>
            <span>Elapsed:</span>
            <?= number_format(microtime(true) - SPIRAL_INITIAL_TIME, 3) ?> seconds
        </div>
        <div class="elapsed memory">
            <span>Memory peak usage:</span> <?= number_format(memory_get_peak_usage() / 1024, 2) ?> Kb
        </div>
    </div>
    <?php
    foreach ($dumps as $argumentID => $dump) {
        echo "<div id=\"argument-{$argumentID}\" style=\"display: none\">{$dump}</div>";
    }
    ?>
</div>
</body>
</html>