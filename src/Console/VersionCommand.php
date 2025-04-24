<?php

namespace Baum\Console;

use Illuminate\Console\Command;
use Baum\BaumServiceProvider;

class VersionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'baum:version';

    /**
     * The console command description
     *
     * @var string
     */
    protected $description = 'Display baum\'s version information.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->line('<info>Baum</info> v<comment>'.BaumServiceProvider::VERSION.'</comment>');
        $this->line('A Nested Set pattern implementation for Laravel\'s Eloquent ORM.');
        $this->line('');
        $this->line('Licensed under the terms of the MIT License <http://opensource.org/licenses/MIT>.');
        $this->line('Coded by Estanislau Trepat <estanis@etrepat.com>.');
    }
}
