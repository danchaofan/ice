<?php
namespace Ice\Frame\Daemon;
class Request {#extends \Ice\Frame\Request {

    public $options;
    public $argv;

    public $requestTime;

    // router info
    public $class;
    public $method;

    public $originalArgv;

    public $stdin;

    public function __construct() {
        $this->originalArgv = $_SERVER['argv'];

        $this->stdin = fopen('php://stdin', 'r');

        #$this->requestTime = isset($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : \U_Time::now();

        $this->initArguments();
    }

    public function __get($name) {
        if ($name == 'id') {
            $this->id = md5(sprintf("%s|%s|%s", gethostname(), posix_getpid(), microtime(TRUE), rand(0, 999999)));
            return $this->id;
        }
    }

    public function getOption($name, $default = null) {
        return isset($this->options[$name]) ? $this->options[$name] : $default;
    }

    public function hadOption($name) {
        return isset($this->options[$name]);
    }

    protected function initArguments() {
        $argv = array_slice($this->originalArgv, 1);
        $argc = count($argv);
        $i = 0;
        while ($i < $argc) {
            $cArg = $argv[$i];
            $nArg = $i + 1 < $argc ? $argv[$i + 1] : null;
            if (preg_match(';^--([\w-]++)$;', $cArg, $match)) {
                if (isset($nArg) && strpos($nArg, '-') !== 0) {
                    $this->options[$match[1]] = $nArg;
                    $i ++;
                } else {
                    $this->options[$match[1]] = '';
                }
            } else if (preg_match(';^--([\w-]++)=(.*+)$;', $cArg, $match)) {
                $this->options[$match[1]] = $match[2];
            } else if (preg_match(';^-([a-zA-Z]+)$;', $cArg, $match)) {
                $opts = str_split($match[1]);
                $lOpt = array_pop($opts);
                // last option would have value
                if (isset($nArg) && strpos($nArg, '-') !== 0) {
                    $this->options[$lOpt] = $nArg;
                    $i ++;
                } else {
                    $this->options[$lOpt] = '';
                }
                // other options
                foreach ($opts as $opt) {
                    $this->options[$opt] = '';
                }
            } else if (preg_match(';^-([a-zA-Z])=(.*+)$;', $cArg, $match)) {
                $this->options[$match[1]] = $match[2];
            } else {
                $this->argv[] = $cArg;
            }
            $i ++;
        }

    }

}