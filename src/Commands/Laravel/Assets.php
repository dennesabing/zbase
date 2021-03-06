<?php

namespace Zbase\Commands\Laravel;

/**
 * Zbase-Command Assets
 *
 * @link http://zbase.dennesabing.com
 * @author Dennes B Abing <dennes.b.abing@gmail.com>
 * @license proprietary
 * @copyright Copyright (c) 2016 ClaremontDesign/MadLabs-Dx
 * @file Assets.php
 * @project Zbase
 * @package Zbase/Traits
 */
use Illuminate\Console\Command;
use Zbase\Interfaces;

class Assets extends Command
{

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $signature = 'zbase:assets {options}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Publish public files';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		zbase()->setConsoleCommand($this);
		$phpCommand = env('ZBASE_PHP_COMMAND', 'php');
		$artisanFile = env('ZBASE_ARTISAN_FILE', 'artisan');
		$packages = zbase()->packages();
		if(!empty($packages))
		{
			foreach ($packages as $packageName)
			{
				$zbase = zbase_package($packageName);
				if($zbase instanceof Interfaces\AssetsCommandInterface)
				{
					$this->info($this->signature . '.pre - ' . $packageName);
					$zbase->assetsCommand($phpCommand, ['assets.pre' => true, 'command' => $this]);
				}
			}
		}
		echo shell_exec($phpCommand . ' ' . $artisanFile . '  vendor:publish --tag=public --force');
		$commands = [];
		if(!empty($commands))
		{
			foreach ($commands as $command)
			{
				if($command instanceof \Closure)
				{
					$command();
				}
				else
				{
					echo shell_exec($phpCommand . ' ' . $artisanFile . ' ' . $command);
				}
			}
		}
		if(!empty($packages))
		{
			foreach ($packages as $packageName)
			{
				$zbase = zbase_package($packageName);
				if($zbase instanceof Interfaces\AssetsCommandInterface)
				{
					$this->info($this->signature . '.post - ' . $packageName);
					$zbase->assetsCommand($phpCommand, ['assets.post' => true, 'command' => $this]);
				}
			}
		}
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array();
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array();
	}


    /**
     * Write a string as information output.
     *
     * @param  string  $string
     * @param  null|int|string  $verbosity
     * @return void
     */
	public function info($string, $verbosity = null)
	{
		if(zbase_is_console())
		{
			parent::info(' --- ' . $string, $verbosity);
		}
		else
		{
			var_dump(' --- ' . $string);
		}
	}
    /**
     * Write a string as error output.
     *
     * @param  string  $string
     * @param  null|int|string  $verbosity
     * @return void
     */
	public function error($string, $verbosity = null)
	{
		if(zbase_is_console())
		{
			parent::error(' --- ' . $string, $verbosity);
		}
		else
		{
			var_dump('ERROR: --- ' . $string);
		}
	}
}
