<?php

namespace Bazo\NewRelic;

use Nette\Application\Application;
use Nette\Application\BadRequestException;
use Nette\Application\Request;



/**
 * @author Martin Bažík <martin@bazo.sk>
 */
class NewRelicProfiler
{

	public function onRequest(Application $app, Request $request)
	{
		$params = $request->getParameters();
		newrelic_name_transaction($request->getPresenterName() . ':' . http_build_query($params, NULL, ' '));
	}


	public function onError(Application $app, \Exception $e)
	{
		if ($e instanceof BadRequestException) {
			return; // ignore
		}

		newrelic_notice_error($e->getMessage(), $e);
	}


}