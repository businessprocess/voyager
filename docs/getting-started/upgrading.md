# Upgrading

## Upgrading 1.7 to 1.8

Voyager 1.8 supports PHP 8.3 - 8.5 and Laravel 12 (12.69+) / 13 (13.30+). Older PHP and Laravel versions are no longer supported.

### Update your Composer.json

Update the version of Voyager inside the require declaration of your composer.json to:

`"tcg/voyager": "1.8.*"`

And then run `composer update`

### Doctrine DBAL 4

Laravel no longer ships Doctrine DBAL, so Voyager now creates its own DBAL 4 connection on top of your Laravel database connection.
If you use Voyager's database classes (`TCG\Voyager\Database\...`) in your own code, note the DBAL 4 changes:

- Built-in Doctrine types have no `getName()` anymore. Use `\Doctrine\DBAL\Types\Type::lookupName($type)`.
- `TCG\Voyager\Database\Schema\SchemaException` replaces `Doctrine\DBAL\Schema\SchemaException::tableDoesNotExist()` / `tableAlreadyExists()`.
- `getSQLDeclaration()` in custom types must declare the `string` return type.

### Intervention Image 4

Voyager now uses Intervention Image 4 and no longer registers the Intervention service provider or the `Image` facade
(Laravel 13 binds its own image manager to `image`). If you extended Voyager's content types or controllers,
use `Voyager::imageManager()` instead of `Image::make()`, and the v4 methods (`orient()`, `scale()`, `cover()`, `encodeUsingFileExtension()`).

### Migrations

Since Laravel 11 `->change()` drops column modifiers that are not repeated. If you copied Voyager's migrations into your app,
compare them with the updated ones (for example `users.role_id` must stay `nullable()`).

### Date formats in BREAD

Carbon 3 removed `formatLocalized()`. The `format` option of date and timestamp fields keeps working with the same
strftime-style formats (e.g. `%Y-%m-%d`), now rendered through the `voyager_format_date()` helper.

## Upgrading 1.5 to 1.6

### Update your Composer.json

To update to the latest version inside of your composer.json file make sure to update the version of Voyager inside the require declaration of your composer.json to:

`tcg/voyager": "1.6.*`

And then run `composer update`

### Check your TinyMCE configuration

TinyMCE was updated to version 6 and with that, a lot of configurations have changed.  
If there are any errors in the console and you changed the TinyMCE configuration, make sure you are using the latest options and values from their docs.

### Troubleshooting

Be sure to ask us on our slack channel if you are experiencing any issues and we will try and assist. Thanks.
