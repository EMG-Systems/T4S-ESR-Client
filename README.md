# T4S ESR API client PHP

PHP implementation of the T4S ESR's public API for Scheme, Course, Expert and Project entities.

## Getting started

### Installation

This package is recommended to be installed using composer.

```bash
# CLI
$ composer require emg-systems/t4s-api-client
```
```json lines
// Using composer.json
"require": {
  "emg-systems/t4s-api-client": "^1.0.0"
}
```
### Running the test

This package contains PHPUnit tests covering all the implemented endpoints of the API.

```bash
$ ./vendor/bin/phpunit test --configuration .phpunit.xml --coverage-text
```

### Example

```php
// Create an instance of the API client
$client = new Client();

// Retrieve the first 10 items of an unfiltered list of experts in reversed creation date order.
$client->getExperts();

// Retrieve the 2nd 8 items of a list of experts sorted by name
// where any of the textual properties contains the word carpenter.
$client->getExperts('carpenter', null, null, null, 8, 8, [['field' => 'name', 'dir' => 'asc']]);

// Retrieve experts having at least the level of 4 in the expertises identified by 2 and 5.
$client->getExperts(null, null, [2, 5], 4);

// Retrieve the expert identified by 29.
$client->getExpert(29);

// Retrieve the passport of the expert identified by 29.
$client->getExpertPassport(29);

// Compare two Qualification Schemes.
$client->compareSchemes(12, 69);

// Retrieve all Dimensions in CQS.
$client->getDimensions();

// Retrieve all Thematic Fields in CQS.
$client->getThematicFields();

// Retrieve all Thematic Fields that belongs to the Dimension identified by 123.
$client->getThematicFields(123);

// Retrieve all Areas of Expertise in CQS.
$client->getExpertises();

// Retrieve all Areas of Expertise that belongs to the Thematic Field identified by 321.
$client->getExpertises(321);





```

## Versioning

We use [SemVer](http://semver.org/) for versioning. For the versions available, see the [tags on this repository](https://github.com/emg-group/simona-api-client-php/tags).

## Authors

* **Péter Gábriel**, *EMG Group*

## License

This project is distributed under GPL-3.0 - see the [LICENCE](LICENCE) file for details.
