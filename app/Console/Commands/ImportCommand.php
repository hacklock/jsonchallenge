<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use JsonChallenge\Customer\Service\ImportService;
use JsonChallenge\Reader\Exception\ReaderException;

class ImportCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'customer:import {file}';

    /**
     * @var string
     */
    protected $description = 'Leest een JSON-bestand en schrijft de klantgegevens naar DB';

    public function handle(CustomerImportService $service): void
    {
        try {
            $service->run($this->argument("file"));
        } catch (ReaderException $e) {
            $this->error($e->getMessage());
        }
    }
}
