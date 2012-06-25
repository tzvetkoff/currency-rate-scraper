# Currency Rate Scraper

An ugly, but yet useful PHP port of the [Open Exchange Rates](https://github.com/currencybot/open-exchange-rates) project, intended for hosted/cronjob use.

## How to use?

In your terminal:
```bash
git clone git://github.com/tzvetkoff/currency-rate-scraper
cd currency-rate-scraper
./scraper.php
ls
```

If you see a `latest.json` file then everything is OK.
You should probably want to schedule the script to execute automatically in the background, so, again in your terminal:
```bash
crontab -e
```
This should open you a text editor with some cryptic lines - if you don't know what they mean, read the fine manual [here](http://linux.die.net/man/5/crontab).
I usually schedule the script every 15 minutes:
```
*/15 * * * * /path/to/currency-rate-scraper/scraper.php >/dev/null 2>&1
```

Other than that, you can keep history of the rates in the `./historical/` directory, configure the script to automatically `commit` the changes in a git repository, and further `push` the changes to a remote host (such as [GitHub](https://github.com/), although it's not recommended - the idea behind is to use and modify the script for your own needs, thus host currency rates by yourself), but if you know what `git` is, you should not need any further explanations - just hack the code.
