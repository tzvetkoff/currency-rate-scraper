#!/usr/bin/env php
<?php

/**
 * URL to fetch Yahoo data from
 */
define('SCRAPER_URL', 'http://finance.yahoo.com/webservice/v1/symbols/allcurrencies/quote;currency=true?view=basic&format=json');

/**
 * Whether to keep /historical/ logs or not
 */
define('SCRAPER_HISTORY', false);

/**
 * History files format (relative to this script's directory, strftime-compatible)
 */
define('SCRAPER_HISTORY_FORMAT', 'historical/%Y-%m-%d.json');

/**
 * Whether to `git commit` or not
 */
define('SCRAPER_GIT_COMMIT', false);

/**
 * Whether to `git push` or not
 */
define('SCRAPER_GIT_PUSH', false);

/**
 * Base currency (Yahoo's rates are based to USD, rebase is not supported at the moment)
 */
define('SCRAPER_BASE', 'USD');


if (!defined('JSON_PRETTY_PRINT')) {
	define('JSON_PRETTY_PRINT', 128);
}


class Scraper {
	public static function scrape() {
		if ($response = file_get_contents(SCRAPER_URL)) {
			if ($json = json_decode($response)) {
				if (isset($json->list->resources)) {
					$result = array(
						SCRAPER_BASE	=> 1.0,
					);

					foreach ($json->list->resources as $resource) {
						$resource = $resource->resource->fields;

						if (preg_match('!USD/([A-Z]{3})!', $resource->name, $matches)) {
							$result[$matches[1]] = /*doubleval*/($resource->price);
						}
					}

					return $result;
				}
			}
		}
	}
}


if ($rates = Scraper::scrape()) {
	chdir($dir = dirname(__FILE__));

	if (file_exists($temp = $dir . '/latest.json')) {
		rename($temp, $dir . '/previous.json');
	}

	$ts = time();
	$date = date('c', $ts);

	file_put_contents($dir . '/latest.json', $encoded = json_encode(array(
		'disclaimer'	=> 'This data is collected from various providers and provided free of charge for informational purposes only, with no guarantee whatsoever of accuracy, validity, availability, or fitness for any purpose; use at your own risk.',
		'license'		=> 'Data collected from various providers with public-facing APIs; copyright may apply; not for resale; no warranties given.',
		'date'			=> $date,
		'timestamp'		=> $ts,
		'base'			=> SCRAPER_BASE,
		'rates'			=> $rates,
	), JSON_PRETTY_PRINT));

	if (SCRAPER_HISTORY) {
		$hist = $dir . '/' . strftime(SCRAPER_HISTORY_FORMAT, $ts);

		if (!is_dir($temp = dirname($hist))) {
			mkdir($temp);
		}

		file_put_contents($hist, $encoded);
	}

	if (SCRAPER_GIT_COMMIT) {
		`git add .`;
		`git commit -m 'exchange rates as of [{$date}]'`;

		if (SCRAPER_GIT_PUSH) {
			`git push origin master`;
		}
	}
}
