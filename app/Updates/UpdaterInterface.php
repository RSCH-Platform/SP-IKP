<?php

namespace App\Updates;

use Illuminate\Console\Command;

interface UpdaterInterface
{
    /**
     * Run the update script.
     *
     * @param Command $command The console command instance for outputting messages.
     */
    public function run(Command $command): void;

    /**
     * Get the description of this update.
     */
    public function getDescription(): string;
}
