<?php

namespace Bazo\NewRelic;

use Nette\Diagnostics\Logger;



/**
 * @author Martin Bažík <martin@bazo.sk>
 */
class NewRelicLogger extends Logger
{

	public function log($message, $priority = self::INFO)
	{
		if (!extension_loaded('newrelic')) {
			return;
		}

		if ($priority === self::ERROR || $priority === self::CRITICAL) {
			if (is_array($message)) {
				$message = implode(' ', $message);
			}
			newrelic_notice_error($message);
		}
	}


}
