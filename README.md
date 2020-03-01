FileSystem
==========
> A convenient abstraction layer for file system operations, wrapping all kinds of storage types with a single API.

Features
--------
 - **Use any kind of storage backend interchangeably.** FileSystem includes a generic 
   [`AdapterInterface`](./src/AdapterInterface.php) for storage adapters. They can be used as drop-in replacements.
 - **Streams and generators.** FileSystem relies on [PSR-7](https://www.php-fig.org/psr/psr-7/) and 
   [PSR 17](https://www.php-fig.org/psr/psr-17/) streams for reading file content. This has massive benefits in terms of
   performance, as adapters can stream file content on demand.  
   Directory content is delivered as traversable generators, reducing the memory footprint even further.
 - **Common Exceptions for common errors.** FileSystem provides a range of default exceptions for common error cases 
   that might occur in any implementation: Missing or existing files, bad permissions or invalid target path specs, for
   example.
 - **Easily extensible.** FileSystem exposes several interfaces, making it really easy to add new adapters. 


### Why shouldn't I just use [FlySystem](https://github.com/thephpleague/flysystem)?
While FlySystem is an excellent library and has always been my go-to solution whenever I had to work with files, I think
it has outlived it's usefulness: Since it's conception, the PHP ecosystem has vastly improved. We now have PSR 
standards, ubiquitous exception handling, type hints and generators. FlySystem does not make use of any of these and in
some cases the maintainers have declared outright resistance.

FileSystem strives to provide a modern, fast and standards-compliant way to work with storage, independent of the 
underlying implementation.

Installation
------------
> **Note:** FileSystem is still in early development and there is no package available via composer yet. If you would 
> like to contribute, fork the project and clone it via git.

Install using composer:
```bash
composer require radiergummi/filesystem
```

Usage
-----
In the simplest case, all you need is an adapter and a `FileSystem` instance:
```php
use Radiergummi\FileSystem\Adapters\LocalAdapter;
use Radiergummi\FileSystem\Exceptions\FileSystemException;
use Radiergummi\FileSystem\FileSystem;

$adapter = new LocalAdapter();
$fileSystem = new FileSystem($adapter);

try {
    $file = $fileSystem->read('/foo/bar.txt');
    $contents = $file->getStream()->getContents();
    $metaData = $file->getStats();
} catch (FileSystemException $exception) {
    // Handle the error
    $exception->getPath(); // Getter for the affected path
}
```

The file system exposes a few methods:

### `exists(string $path): bool`
Checks whether an entity exists at a given path. This works for both files and directories.

### `read(string $path): ?File`
Reads a file at a given path and returns a new [`File`](#file-objects) instance. Files expose a range of convenience 
methods in addition to getters for the content stream and [`MetaData`](#metadata-objects) instance.  

**Possible errors:**  
 - [Entity Not Found](#entitynotfoundexception). Will be thrown if the file does not exist.
 - [Entity Is Not A Directory](#entity-not-a-directory-error). Will be thrown if the parent segment of the file path is 
   not a directory.
 - [Entity Is Not Accessible](#entity-not-accessible-error). Will be thrown if the file is not accessible due to 
   insufficient permissions, or other OS-level errors.
 - [Root Violation](#root-violation-error). Will be thrown if the file is located outside of the configured file system 
   root.

### `getMetaData(string $path): ?MetaData`

### `readDirectory(?string $path = null, bool $recursive = false): ?Directory`

### `write(string $path, StreamInterface $contents, ?int $flags = null): void`

### `rename(string $path, string $newName): void`

### `copy(string $sourcePath, string $destinationPath): void`

### `move(string $sourcePath, string $destinationPath): void`

### `delete(string $path): void`

### `deleteDirectory(string $path): void`

### `createDirectory(string $path): void`

### `getAdapter(): AdapterInterface`

Errors
------
FileSystem includes a range of exceptions for common file system errors. All of them inherit from a single base 
exception, which in turn inherits from the [`FileSystemException`](./src/Exceptions/FileSystemException.php). This 
allows you to handle errors as fine or coarse as needed.

### [`EntityExistsException`](./src/Exceptions/EntityExistsException.php)
Will be thrown if an entity exists but the current call requires it to not exist.

### [`EntityIsDirectoryException`](./src/Exceptions/EntityIsDirectoryException.php)
Will be thrown if an entity is a directory but the current call requires a file.

### [`EntityIsNoDirectoryException`](./src/Exceptions/EntityIsNoDirectoryException.php)
Will be thrown if an entity is not a directory but the current call requires a directory.

### [`EntityNotAccessibleException`](./src/Exceptions/EntityNotAccessibleException.php)
Will be thrown if an entity is not accessible. This might have a wide number of reasons, among them insufficient 
permissions, network or protocol errors or other OS-level failures.

### [`EntityNotFoundException`](./src/Exceptions/EntityNotFoundException.php)
Will be thrown if an entity cannot be found on the file system.

### [`RootViolationException`](./src/Exceptions/RootViolationException.php)
Will be thrown if a target path resolves to a target outside of the configured file system root. This effectively 
prevents [directory traversal attacks](https://en.wikipedia.org/wiki/Directory_traversal_attack).

`File` Objects
--------------
File objects provide a wrapper around files, their content and associated meta data. They are constructed with lazy
getters for content and meta data which are only invoked the first time you call them.

`MetaData` Objects
------------------
Meta data objects provide a set of general meta data that _should_ be common to most storage backends:
 - **File size:** Size of the file in bytes. Should be `0` for directories.
 - **Last modification time:** Timestamp of the last modification as an immutable `DateTime`. Will be `null` if not 
   applicable.
 - **Creation time:** Timestamp of creation as an immutable `DateTime`. Will be `null` if not applicable.
 - **Other meta data:** Associative array of additional, adapter-specific meta data.

Available Adapters
------------------
From the get-go, only a small number of adapters is supported. This will increase over time as new adapters are added.  
If you feel an adapter is missing, please open a PR or an issue.

### Included
 - Local disk file systems: `Radiergummi\FileSystem\Adapters\LocalAdapter`
 - (S)FTP file systems: `Radiergummi\FileSystem\Adapters\LocalAdapter`
 - AWS S3 file systems: `Radiergummi\FileSystem\Adapters\LocalAdapter`

Creating new Adapters
---------------------

Contributing
------------
