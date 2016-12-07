<?php

namespace Bazo\NewRelic\DI;

use Nette\DI\CompilerExtension;
use Nette\PhpGenerator\ClassType;



/**
 * New Relic Extension
 *
 * @author Martin Bažík <martin@bazo.sk>
 */
class NewRelicExtension extends CompilerExtension
{

	/** @var array */
	public $defaults = [
		'useLogger'	 => TRUE,
		'appName'	 => 'NetteApp'
	];
	private $useLogger;
	private $configuration;

	/**
	 * Processes configuration data
	 *
	 * @return void
	 */
	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();

		$this->configuration	 = $config			 = $this->getConfig($this->defaults, TRUE);
		$this->useLogger = $config['useLogger'];
		unset($config['useLogger']);

		if ($this->useLogger) {
			$container->addDefinition($this->prefix('logger'))
					->setClass('\Bazo\NewRelic\NewRelicLogger', [$container->expand('%appDir%/log')])
					->setAutowired(FALSE);
		}

		$container->addDefinition($this->prefix('profiler'))
				->setClass('\Bazo\NewRelic\NewRelicProfiler')
				->setAutowired(FALSE);
	}


	public function afterCompile(ClassType $class)
	{
		if (extension_loaded('newrelic')) {
			$initialize = $class->methods['initialize'];

			$initialize->addBody('$app = $this->getService(?);', ['application']);
			$initialize->addBody('$profiler = $this->getService(?);', [$this->prefix('profiler')]);
			$initialize->addBody('$app->onRequest[] = [$profiler, \'onRequest\'];');
			$initialize->addBody('$app->onError[] = [$profiler, \'onError\'];');

			if ($this->useLogger === TRUE) {
				$initialize->addBody('\Tracy\Debugger::setLogger($this->getService(?));', [$this->prefix('logger')]);
			}

			$initialize->addBody('newrelic_set_appname(?);', [$this->configuration['appName']]);
		}
	}


}