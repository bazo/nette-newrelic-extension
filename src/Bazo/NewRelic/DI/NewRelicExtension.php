<?php

namespace Bazo\NewRelic\DI;

/**
 * WatchdogExtension
 *
 * @author Martin Bažík
 */
class NewRelicExtension extends \Nette\DI\CompilerExtension
{

	/** @var array */
	public $defaults = [
		'useLogger' => TRUE,
		'appName' => 'NetteApp'
	];
	private $useLogger;
	private $config;



	/**
	 * Processes configuration data
	 *
	 * @return void
	 */
	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();

		$this->config = $config = $this->getConfig($this->defaults, TRUE);
		$this->useLogger = $config['useLogger'];
		unset($config['useLogger']);

		$container->addDefinition($this->prefix('logger'))
				->setClass('\Bazo\NewRelic\NewRelicLogger')
				->setAutowired(FALSE);

		$container->addDefinition($this->prefix('profiler'))
				->setClass('\Bazo\NewRelic\NewRelicProfiler')
				->setAutowired(FALSE);

		$container->addDefinition('newRelicLogger')
				->setClass('\Bazo\NewRelic\NewRelicLogger')
				->addTag('logger')
				->setFactory('@container::getService', [$this->prefix('logger')]);
	}


	public function afterCompile(\Nette\PhpGenerator\ClassType $class)
	{
		if (extension_loaded('newrelic')) {
			$initialize = $class->methods['initialize'];

			$initialize->addBody('$app = $this->getService(?);', ['application']);
			$initialize->addBody('$profiler = $this->getService(?);', [$this->prefix('profiler')]);
			$initialize->addBody('$app->onRequest[] = callback($profiler, \'onRequest\');');
			$initialize->addBody('$app->onError[] = callback($profiler, \'onError\');');

			if ($this->useLogger === TRUE) {
				$initialize->addBody('\Nette\Diagnostics\Debugger::$logger = $this->getService(?);', [$this->prefix('logger')]);
			}

			$initialize->addBody('newrelic_set_appname(?);', [$this->config['appName']]);
		}
	}


}
