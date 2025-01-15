<?php

namespace App\Traits;

use Log;

trait LogApi 
{
    /**
     * @var  $logChannel;
     */
    protected $logChannel;

    /**
     * Setup to log the output in the console and to the logChannel
     *
     */
    public function error($string, $verbosity = null) 
    {
        $parent = get_parent_class($this);
        if ($parent && method_exists($parent, 'error')) {
            parent::error($string, $verbosity);
        }
        Log::channel($this->logChannel)->error($string);
    }
    public function warn($string, $verbosity = null) 
    {
        $parent = get_parent_class($this);
        if ($parent && method_exists($parent, 'warn')) {
            parent::warn($string, $verbosity);
        }
        Log::channel($this->logChannel)->warning($string);
    }
    public function info($string, $verbosity = null) 
    {
        $parent = get_parent_class($this);
        if ($parent && method_exists($parent, 'info')) {
            parent::info($string, $verbosity);
        }
        Log::channel($this->logChannel)->info($string);
    }
    public function debug($string, $verbosity = null) 
    {
        //  Symfony\Component\Console\Output\OutputInterface
        //   -q  OutputInterface::VERBOSITY_QUIET           16
        //       OutputInterface::VERBOSITY_NORMAL          32
        //   -v  OutputInterface::VERBOSITY_VERBOSE         64
        //  -vv  OutputInterface::VERBOSITY_VERY_VERBOSE    128
        // -vvv  OutputInterface::VERBOSITY_DEBUG           256
        $parent = get_parent_class($this);
        if ($parent && method_exists($parent, 'debug')) {
            parent::debug($string, $verbosity);
        }
        Log::channel($this->logChannel)->debug($string);
    }
}
