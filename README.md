# aGov

[![CircleCI](https://circleci.com/gh/previousnext/agov/tree/8.x-2.x.svg?style=svg)](https://circleci.com/gh/previousnext/agov/tree/8.x-2.x)

## Download

aGov is available as a full drupal site in tgz and zip format at: http://drupal.org/project/agov

## Building from Source

Source is available from GitHub at https://github.com/previousnext/agov

## Requirements

Docker for Mac


## Getting started

```bash
$ make init
$ make build
$ make login
```

## Testing

```bash
$ ./bin/phpunit
```

To run a specific test:

```$bash
./bin/phpunit --filter "testModuleConfig" tests/src/Kernel/DefaultConfigTest.php
```
