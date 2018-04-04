# WordPress Menu Cache

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]

WordPress mu-plugin for a faster `wp_nav_menu`.

*Please read carefully the code and understand how it works before you roll it on production. It uses [transient API](https://codex.wordpress.org/Transients_API) and generates the cache key for every page so a menu can keep track of an active state. Make sure you use Memcache or Redis as an object cache otherwise it could bloat your `wp_options` table.*

## Requirements

Make sure all dependencies have been installed before moving on:

* [PHP](http://php.net/manual/en/install.php) >= 5.6
* [Composer](https://getcomposer.org/download/)

## Install

Pull the package via Composer:

``` bash
$ composer require nasyrov/wordpress-menu-cache
```

## Testing

``` bash
$ composer lint
```

## Security

If you discover any security related issues, please email inasyrov@ya.ru instead of using the issue tracker.

## Credits

- [Evgenii Nasyrov][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/nasyrov/wordpress-menu-cache.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/nasyrov/wordpress-menu-cache.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/nasyrov/wordpress-menu-cache
[link-downloads]: https://packagist.org/packages/nasyrov/wordpress-menu-cache
[link-author]: https://github.com/nasyrov
[link-contributors]: ../../contributors
