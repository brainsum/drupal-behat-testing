# Dependency Injection

## Setup
### NoExtension

Argument resolver is required for DI autowiring to work.

```yaml
    # Allows DI in contexts (uses autowiring).
    Zalas\Behat\NoExtension:
      argument_resolver: true
      imports:
        - "./config/services.yml"

```
### Services file

Set up your service like a regular symfony one.
If you want to use Interfaces, you need to do some alias magic.

```yaml
services:
  Brainsum\DrupalBehatTesting\DrupalExtension\Service\ExampleService: ~
  Brainsum\DrupalBehatTesting\DrupalExtension\Service\ExampleServiceInterface: '@Brainsum\DrupalBehatTesting\DrupalExtension\Service\ExampleService'
```

### Context

Simply add the argument. Symfony DI autowiring takes care of the rest.

```php
class ExampleContext {

  private $example;
  
  public function __construct(
    ExampleServiceInterface $example
  ) {
    $this->example = $example;
  }
  
}
```

## Roadmap

- It would be better if contexts could be set up as services, too.
    - @see: [SymfonyExtension](https://github.com/FriendsOfBehat/SymfonyExtension)
    
    