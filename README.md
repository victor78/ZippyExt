# ZippyExt
ZippyExt (aka ZippyExtended) is a library extended over [Zippy](https://github.com/alchemy-fr/Zippy) providing the strategy and adapter for using 7-Zip, including password support.

## Requirements

- PHP >= 7.0
- [7-Zip](https://www.7-zip.org/) (`7za` binary in `PATH`) — required only for 7zip adapter

## Installation

The only supported installation method is via [Composer](https://getcomposer.org). Run the following command to require ZippyExt in your project:

```bash
composer require victor78/zippy-ext
```

## Adapters

ZippyExt currently supports the following drivers and file formats:

- zip
  - .zip
- PHP zip extension
  - .zip
- GNU tar
  - .tar
  - .tar.gz
  - .tar.bz2
- BSD tar
  - .tar
  - .tar.gz
  - .tar.bz2
- 7zip
  - .zip (with optional AES-256 password encryption)

## Getting started

All the following code samples assume that ZippyExt is loaded and available as `$zippy`. You need the following code (or variation of) to load ZippyExt:

```php
<?php

use Victor78\ZippyExt\Zippy;

// Require Composer's autoloader
require __DIR__ . '/vendor/autoload.php';

// Load Zippy
$zippy = Zippy::load();
```

### List an archive's contents:

```php
// Open an archive
$archive = $zippy->open('build.tar');

// Iterate through members
foreach ($archive as $member) {
    echo "Archive contains $member" . PHP_EOL;
}
```

### Extract an archive to a specific directory:

```php
// Open an archive
$archive = $zippy->open('build.tar');

// Extract archive contents to `/tmp`
$archive->extract('/tmp');
```

### Create a new archive

```php
// Creates an archive.zip that contains a directory "folder" that contains
// files contained in "/path/to/directory" recursively
$archive = $zippy->create('archive.zip', array(
    'folder' => '/path/to/directory'
), true);
```

### Use 7zip

If you need to use 7zip archiving to create a zip archive, use the fourth parameter:

```php
// Creates an archive.zip using the 7zip engine
$archive = $zippy->create('archive.zip', $files, true, '7zip');
```

To create an AES-256 encrypted archive, pass the password as the fifth parameter:

```php
// Creates an archive.zip with AES-256 encryption
$archive = $zippy->create('archive.zip', $files, true, '7zip', 'your_password');
```

To open and extract a password-protected 7zip archive:

```php
// Open archive with password
$archive = $zippy->open('archive.zip', '7zip', 'your_password');
$archive->extract('folder_for_extracted');
```

## Known Limitations

- Password protection is only supported with the **7zip adapter**. Standard zip and tar adapters ignore the password parameter.
- `extractMembers()` is not supported for the 7zip adapter (use `extract()` instead).

## Documentation

Documentation in English and Russian is available in the [wiki](https://github.com/victor78/ZippyExt/wiki).

## License

This project is licensed under the [MIT license](https://github.com/victor78/ZippyExt/blob/master/LICENSE).
