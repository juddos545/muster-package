<?php


namespace Juddos545\Muster\Commands;


use Illuminate\Console\Command;
use Juddos545\Muster\GenerateValidation;

class GenerateValidationCommand extends Command
{
    protected $signature = 'muster:validation {table}';
    protected $description = 'Generates an array to be used by the Laravel Validator';

    public function fire()
    {
        $tableName = $this->argument('table');
        $validation = (new GenerateValidation())->validator($tableName);
        $this->info("Generated validation for {$tableName}:");
        $this->line($validation);
    }
}