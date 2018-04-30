# ZippyExt
ZippyExt (aka ZippyExtended)  is the libruary extended over [Zippy](https://github.com/alchemy-fr/Zippy) with providing the strategy and the adapter for using 7-Zip, including supporting passwords.

## Installation

The only supported installation method is via [Composer](https://getcomposer.org). Run the following command to require ZippyExt in your project:

```
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
  - .zip
  
  ## Getting started

All the following code samples assume that ZippyExt is loaded and available as `$zippy`. You need the following code (or variation of) to load ZippyExt:

```
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
If you need to use 7zip archiving to create zip archive, you should use fourth parameter:
```php
// Creates an archive.zip by 7zip engine
$archive = $zippy->create('archive.zip', $files, true, '7zip');
```
And if you want to create the encrypted archive, you can use fifth parameter:
```php
// Creates an archive.zip with AES-256 encryption and your password 
$archive = $zippy->create('archive.zip', $files, true, '7zip', 'some_your_password');
```

## Documentation

Documentation in English and in Russian here, in [wiki](https://github.com/victor78/ZippyExt/wiki).

## License

This project is licensed under the [MIT license](https://github.com/victor78/ZippyExt/blob/master/LICENSE).
