<?php

namespace Baum\Playground;

use Psy\Configuration;
use Psy\Shell;

class Console
{
    /**
     * Shell instance.
     *
     * @var \Psy\Shell
     */
    protected $shell;

    /**
     * Class constructor
     *
     * @param \Psy\Shell|null $shell
     */
    public function __construct($shell = null)
    {
        $this->shell = $shell;
    }

    /**
     * Static instantiator and executor.
     *
     * @return int
     */
    public static function start()
    {
        $instance = new static;

        return $instance->run();
    }

    /**
     * Run the shell
     *
     * @return int
     */
    public function run()
    {
        if (is_null($this->shell)) {
            $this->shell = $this->newShell();
        }

        return $this->shell->run();
    }

    /**
     * Return the actual shell instance.
     *
     * @return \Psy\Shell
     */
    public function getShell()
    {
        return $this->shell;
    }

    /**
     * Set the actual shell instance.
     *
     * @param \Psy\Shell $shell
     * @return void
     */
    public function setShell($shell)
    {
        $this->shell = $shell;
    }

    /**
     * Builds a new shell instance with default options.
     *
     * @return \Psy\Shell
     */
    protected function newShell()
    {
        $shell = new Shell($this->getShellConfig());

        return $shell;
    }

    /**
     * Build a new Psy\Shell configuration object instance.
     *
     * @return \Psy\Configuration
     */
    protected function getShellConfig()
    {
        $config = new Configuration(['updateCheck' => 'never']);

        $config->getPresenter()->addCasters($this->getShellCasters());

        return $config;
    }

    /**
     * Get the object casters to use.
     *
     * @return array
     */
    protected function getShellCasters()
    {
        return Caster::availableCasters();
    }
}
