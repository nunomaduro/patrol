<p align="center">
    <img src="https://raw.githubusercontent.com/nunomaduro/patrol/master/docs/example.png" width="600" alt="Patrol">
    <p align="center">
        <a href="https://packagist.org/packages/nunomaduro/patrol"><img alt="Total Downloads" src="https://img.shields.io/packagist/dt/nunomaduro/patrol"></a>
        <a href="https://packagist.org/packages/nunomaduro/patrol"><img alt="Latest Version" src="https://img.shields.io/packagist/v/nunomaduro/patrol"></a>
        <a href="https://packagist.org/packages/nunomaduro/patrol"><img alt="License" src="https://img.shields.io/packagist/l/nunomaduro/patrol"></a>
    </p>
</p>

------

> Note: this project may have some bugs! 😘

**Patrol** is an elegant command-line tool that keeps your PHP Project's dependencies in check.

## Installation / Usage

> **Requires [PHP 8.0+](https://php.net/releases/)**

First, install Patrol via the [Composer](https://getcomposer.org/) package manager:

```bash
composer require nunomaduro/patrol --dev
```

Then, use the `patrol` binary:

```bash
./vendor/bin/patrol
```

You may also specify the directory that should be inspected:

```bash
./vendor/bin/patrol inspect ../another/project-directory
```

## Patrol Sponsors

We would like to extend our thanks to the following sponsors for funding Patrol development. If you are interested in becoming a sponsor, please visit the Nuno Maduro's [Sponsors page](https://github.com/sponsors/nunomaduro).

### Premium Sponsors

- **[Akaunting](https://akaunting.com)**
- **[Meema](https://meema.io/)**
- **[Scout APM](https://scoutapm.com)**

## License

Patrol is an open-sourced software licensed under the [MIT license](LICENSE.md).
