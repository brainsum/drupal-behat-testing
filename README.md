# Behat for Drupal

@todo: Finish.

## Setup

### Docker

Use https://github.com/wodby/docker4drupal

### Drush 9

Create a `drush/sites` folder with `self.sites.yml` and add:
```yaml
docker-local:
    root: /var/www/html/web
uri: 'http://nginx'
```

## Start

@todo: Add helper scripts.

- `docker-compose <docker-compose.yml for project> <docker-compose.selenium.yml from this package> up -d`
- Wait for your site and selenium to spin up (shouldn't take long, a few seconds at most)
- `docker-compose <docker-compose.yml for project> <docker-compose.selenium.yml from this package> exec php sh -c "cd tests/behat && ../../vendor/bin/behat --out=std --config <your behat.yml> --stop-on-failure --verbose"`
