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
 - **Opt-in Caching.** FileSystem supports [PSR-16](tps://www.php-fig.org/psr/psr-16/) caches to improve performance, 
   independent of the adapter used.
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
    $file = $fileSystem->readFile('/foo/bar.txt');
    $contents = $file->getStream(); // PSR-7 StreamInterface
    $metaData = $file->getMetaData(); 
} catch (FileSystemException $exception) {
    // Handle the error
    $exception->getPath(); // Getter for the affected path
}
```

Method reference
----------------
The file system exposes the following methods:

### `exists(string $path): bool`
Checks whether an entity exists at a given path. This works for both files and directories.

**Possible errors:**  
 - [Entity Not Found](#entitynotfoundexception). Will be thrown if the entity does not exist.
 - [Entity Is Not Accessible](#entitynotaccessibleexception). Will be thrown if the entity is not accessible due to 
   insufficient permissions, or other OS-level errors.
 - [Root Violation](#rootviolationexception). Will be thrown if the entity is located outside of the configured file 
   system root.

### `readFile(string $path): ?File`
Reads a file at a given path and returns a new [`File`](#file-objects) instance. Files expose a range of convenience 
methods in addition to getters for the content stream and [`MetaData`](#metadata-objects) instance.  

**Possible errors:**  
 - [Entity Not Found](#entitynotfoundexception). Will be thrown if the file does not exist.
 - [Entity Is Not A Directory](#entityisnodirectoryexception). Will be thrown if the parent segment of the file path is 
   not a directory.
 - [Entity Is Not Accessible](#entitynotaccessibleexception). Will be thrown if the file is not accessible due to 
   insufficient permissions, or other OS-level errors.
 - [Root Violation](#rootviolationexception). Will be thrown if the file is located outside of the configured file 
   system root.

### `getMetaData(string $path): ?MetaData`
Retrieves a [`MetaData`](#metadata-objects) instance, _if the adapter supports it_. Null will be returned otherwise.

**Possible errors:**  
 - [Entity Not Found](#entitynotfoundexception). Will be thrown if the file or directory does not exist.
 - [Entity Is Not Accessible](#entitynotaccessibleexception). Will be thrown if the entity is not accessible due to 
   insufficient permissions, or other OS-level errors.
 - [Root Violation](#rootviolationexception). Will be thrown if the entity is located outside of the configured file 
   system root.

### `readDirectory(?string $path = null, bool $recursive = false): Generator<FileSystemEntity>`
Lists the contents of a directory at the given path. The method always returns a generator, allowing you to efficiently
iterate the results, even if they grow very large. It's up to the adapter implementation to make use of the generator, 
allowing for both files being fetched during iteration or beforehand, including pagination handling etc.  
The results will be instances of `FileSystemEntity`, giving you access to their content and meta data.

Adapters that don't support directories should handle this call by returning a flat list of files or use the path to 
otherwise figure out the files the user is looking for.

**Possible errors:**  
 - [Entity Not Found](#entitynotfoundexception). Will be thrown if the directory does not exist.
 - [Entity Is Not A Directory](#entityisnodirectoryexception). Will be thrown if the entity at the target path is not a
   directory.
 - [Entity Is Not Accessible](#entitynotaccessibleexception). Will be thrown if the directory is not accessible due to 
   insufficient permissions, or other OS-level errors.
 - [Root Violation](#rootviolationexception). Will be thrown if the directory is located outside of the configured file 
   system root.

### `writeFile(string $path, StreamInterface $contents, ?int $flags = null): void`
Writes to a file at the given path.

**Possible errors:**  
 - [Entity Is Not A Directory](#entityisnodirectoryexception). Will be thrown if the parent segment of the file path is 
   not a directory.
 - [Entity Is Not Accessible](#entitynotaccessibleexception). Will be thrown if the file is not accessible due to 
   insufficient permissions, or other OS-level errors.
 - [Root Violation](#rootviolationexception). Will be thrown if the file is located outside of the configured file 
   system root.

### `rename(string $path, string $newName): void`
Renames a file _or directory_ in place: While the first parameter has to be the full file path, the second is designed 
to be the new file base name only. Therefore, to rename `/foo/bar/baz.txt` to `/foo/bar/quz.json`, you would call it as
`rename('/foo/bar/baz.txt', 'quz.json')`. To move the file to a new path, use the
[`move`](#movestring-sourcepath-string-destinationpath-void) method instead. Adapter implementations will forward this 
to a `move` call in most cases.

**Possible errors:**  
 - [Entity Not Found](#entitynotfoundexception). Will be thrown if the entity does not exist.
 - [Entity Exists](#entityexistsexception). Will be thrown if an entity exists at the target path.
 - [Entity Is Not Accessible](#entitynotaccessibleexception). Will be thrown if the entity is not accessible due to 
   insufficient permissions, or other OS-level errors.
 - [Root Violation](#rootviolationexception). Will be thrown if the entity is located outside of the configured file  
   system root.

### `copy(string $sourcePath, string $destinationPath): void`
Copies a file _or directory_ from the source path to the destination path. Directories will be copied recursively, if 
the underlying file system supports it. 

**Possible errors:**  
 - [Entity Not Found](#entitynotfoundexception). Will be thrown if the source entity does not exist.
 - [Entity Exists](#entityexistsexception). Will be thrown if an entity exists at the target path.
 - [Entity Is Not A Directory](#entityisnodirectoryexception). Will be thrown if the parent segment of the target path  
   is not a directory.
 - [Entity Is Not Accessible](#entitynotaccessibleexception). Will be thrown if the entity is not accessible due to 
   insufficient permissions, or other OS-level errors.
 - [Root Violation](#rootviolationexception). Will be thrown if the entity is located outside of the configured file  
   system root.

### `move(string $sourcePath, string $destinationPath): void`
Moves a file _or directory_ from the source path to the destination path.

**Possible errors:**  
 - [Entity Not Found](#entitynotfoundexception). Will be thrown if the source entity does not exist.
 - [Entity Is A Directory](#entityisdirectoryexception). Will be thrown if the source entity is a file and the target
   entity is a directory.
 - [Entity Is Not A Directory](#entityisnodirectoryexception). Will be thrown if the source entity is a directory and 
   the target entity is a file, or the parent segment of the target path is not a directory.
 - [Entity Is Not Accessible](#entitynotaccessibleexception). Will be thrown if the entity is not accessible due to 
   insufficient permissions, or other OS-level errors.
 - [Root Violation](#rootviolationexception). Will be thrown if the entity is located outside of the configured file  
   system root.

### `deleteFile(string $path): void`
Deletes a file on the file system. This operation will fail for directories, so you should use the 
[`deleteDirectory`](#deletedirectorystring-path-void) method instead. While it would technically be possible do support
both from the same method, prohibiting this was a deliberate design choice: Using the `deleteDirectory` method makes the
intent to delete a full directory absolutely clear, potentially helping to avoid shredding entire directory trees by 
accident.

**Possible errors:**  
 - [Entity Not Found](#entitynotfoundexception). Will be thrown if the directory does not exist.
 - [Entity Is Not A Directory](#entityisnodirectoryexception). Will be thrown if the parent segment of the file path is 
   not a directory.
 - [Entity Is Not Accessible](#entitynotaccessibleexception). Will be thrown if the file is not accessible due to 
   insufficient permissions, or other OS-level errors.
 - [Root Violation](#rootviolationexception). Will be thrown if the file is located outside of the configured file  
   system root.

### `deleteDirectory(string $path): void`
Deletes a directory _and all its contents_. This may or may not be supported by the underlying file system but adapter 
implementations should take care to handle this fact transparently.

**Possible errors:**  
 - [Entity Not Found](#entitynotfoundexception). Will be thrown if the directory does not exist.
 - [Entity Is Not A Directory](#entityisnodirectoryexception). Will be thrown if the entity at the given path is not a 
   directory.
 - [Entity Is Not Accessible](#entitynotaccessibleexception). Will be thrown if the file is not accessible due to 
   insufficient permissions, or other OS-level errors.
 - [Root Violation](#rootviolationexception). Will be thrown if the directory is located outside of the configured file 
   system root.

### `createDirectory(string $path): void`
Creates a new directory. This may or may not be supported by the underlying file system but adapter implementations 
should take care to handle this fact transparently.

**Possible errors:**  
 - [Entity Exists](#entityexistsexception). Will be thrown if an entity exists at the target path.
 - [Entity Is Not A Directory](#entityisnodirectoryexception). Will be thrown if the parent segment of the file path is 
   not a directory.
 - [Entity Is Not Accessible](#entitynotaccessibleexception). Will be thrown if the directory is not accessible due to 
   insufficient permissions, or other OS-level errors.
 - [Root Violation](#rootviolationexception). Will be thrown if the file is located outside of the configured file  
   system root.
   
### `getAdapter(): AdapterInterface`
Retrieves the adapter instance. This method exists as an escape hatch in case you need to perform an operation not 
directly supported by FileSystem without breaking the encapsulation. I recommend avoiding this, tho.

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

Caching and Logging
-------------------
FileSystem includes several extension interfaces for the `FileSystem` class that add caching or logging as an add-in. To
use them, you need a file system instance that implements those interfaces. The default implementation already extends 
these interfaces, so all you need to add caching (if your DI container doesn't inject it already), is using the 
`setCache(CacheInterface $cache)` method, and the same goes for PSR-3 logging.

Available Adapters
------------------
From the get-go, only a small number of adapters is supported. This will increase over time as new adapters are added.  
If you feel an adapter is missing, please open a PR or an issue.

### Included
 - Local disk file systems: `Radiergummi\FileSystem\Adapters\LocalAdapter`
 - (S)FTP file systems: `Radiergummi\FileSystem\Adapters\FtpAdapter`
 - AWS S3 file systems: `Radiergummi\FileSystem\Adapters\AwsS3Adapter`

Creating new Adapters
---------------------
All adapters must implement the [`AdapterInterface`](./src/Interfaces/AdapterInterface.php). The methods define a clear
set of parameter and return types, including `@throws` tags for all known exceptions the method might throw. Make sure
to adhere to the reasons laid out [above](#method-reference).  
For any exception an adapter might throw _that doesn't fit in those categories_, you should clearly document the 
behaviour. All exceptions should bubble up for the user to catch, but that requires them to know about it. 

Contributing
------------
Send in a PR or open an issue :) The process will be fleshed out after the initial release.
