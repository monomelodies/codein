# sensimedia/codein
Code analysis, creation and modification library

## Installation
```sh
$ composer require --dev sensimedia/codein
```

## Usage
Codein is a CLI tool to inspect PHP files or directories of PHP files for code
smells. It currently works best on classes.

```sh
$ vendor/bin/codein [OPTIONS] path/to/file/or/directory
```

If the supplied argument is a PHP file, just that file is analysed. If it is a
directory, all PHP files in it are scanned.

## Options
-r|--recursive Recursively iterate underlying directories
--check=name/of/check One or more names of things to check

## Adding checks
By default, Codein only scans for PHP parsing errors (hopefully, your editor is
already set up to do that!). To make it more useful, one should add _plugins_.

Why plugins one might ask? Well, my coding style isn't going to be yours, and
what *I* consider a code smell (missing return type hint, for instance) might be
perfectly valid in *your* codebase (because you need to support legacy PHP
versions, for instance).

Let's add a check for those return type hints:

```sh
$ composer require --dev sensimedia/codein-typehints
$ vendor/bin/codein --check=sensimedia/codein-typehints path/to/file
```

## Writing plugins
Each plugin should extend `Sensi\Codein\Check`. The grunt of the work is done in
the `check` method. It takes a single argument (the name of the file to check)
and returns a `Generator`. Every time your plugin encounters a code smell, it
should `yield` a string containing an error message. You can use the builtin
`extractClass` method to get the name of the class contained in the file,
allowing you to inspect.

The yielded messages are formatted using
[simple ansi colors](https://github.com/simoneast/simple-ansi-colors), with a
`"<reset>\n"` automatically appended. You can play with the colors; use <red>
for something really bad, <darkYellow> for a warning etc.

## The `codein.json` config file
Codein looks for a `codein.json` config file in the current working directory.
It allows you to specify a few things:

- A default array of `checks`. The checks are run no matter what `--check=...`
  options you specify. Handy, because with multiple plugins typing them all out
  each time quickly becomes old.
- A string or array of `bootstrap` files (relative to `getcwd()`, again). Your
  project, for instance, may rely on dependency injection, configured in a
  central file.
- A `constructors` object containing a key/value store of classnames with an
  array of the arguments to be used during construction when inspecting. Note
  that these arguments are verbatim; e.g. a string should be written a "'this is
  a string'" (note the nested quotes). This is because some classes will expect
  an instantiated object in their constructor arguments, allowing you to specify
  something like "new FooBar" as well.

