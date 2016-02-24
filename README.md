## BehatFormatter

Behat 3 extension for generating reports from your test results.

[![Latest Stable Version](https://poser.pugx.org/elkan/behatformatter/v/stable)](https://packagist.org/packages/elkan/behatformatter) [![Total Downloads](https://poser.pugx.org/elkan/behatformatter/downloads)](https://packagist.org/packages/elkan/behatformatter) [![Latest Unstable Version](https://poser.pugx.org/elkan/behatformatter/v/unstable)](https://packagist.org/packages/elkan/behatformatter) [![License](https://poser.pugx.org/elkan/behatformatter/license)](https://packagist.org/packages/elkan/behatformatter)

### Twig report

![Twig Screenshot](http://i.imgur.com/SlJuhq3.png)

## It's easy!!

* This tool can be installed easily with composer.
* Defining the formatter in the `behat.yml` file
* Modifying the settings in the `behat.yml`file

## Installation

### Prerequisites

This extension requires:

* PHP 5.3.x or higher
* Behat 3.x or higher

### Through composer

The easiest way to keep your suite updated is to use [Composer](http://getcomposer.org>):

#### Install with composer:

```bash
$ composer require --dev elkan/behatformatter
```

#### Install using `composer.json`

Add BehatFormatter to the list of dependencies inside your `composer.json`.

```json
{
    "require": {
        "behat/behat": "3.*@stable",
        "elkan/behatformatter": "v1.0.*",
    },
    "minimum-stability": "dev",
    "config": {
        "bin-dir": "bin/"
    }
}
```

Then simply install it with composer:

```bash
$ composer install --dev --prefer-dist
```

You can read more about Composer on its [official webpage](http://getcomposer.org).

## Basic usage

Activate the extension by specifying its class in your `behat.yml`:

```json
# behat.yml
default:
  suites:
    ... # All your awesome suites come here
  
  formatters: 
    html:
      output_path: %paths.base%/build/
      
  extensions:
    elkan\BehatFormatter\BehatFormatterExtension:
      projectName: BehatTest
      name: html
      renderer: Twig,Behat2
      file_name: Index
      print_args: true
      print_outp: true
      loop_break: true
      show_tags: true
```

## Configuration

* `output_path` - The location where Behat will save the HTML reports. The path defined here is relative to `%paths.base%` and, when omitted, will be default set to the same path.
* `renderer` - The engine that Behat will use for rendering, thus the types of report format Behat should output (multiple report formats are allowed, separate them by commas). Allowed values are:
 * *Behat2* for generating HTML reports like they were generated in Behat 2.
 * *Twig* A new and more modern format based on Twig.
 * *Minimal* An ultra minimal HTML output.
* `file_name` - (Optional) Behat will use a fixed filename and overwrite the same file after each build. By default, Behat will create a new HTML file using a random name (*"renderer name"*_*"date hour"*).
* `projectName` - (Optional) Give your report a page titel.
* `projectDescription` - (Optional) Include a project description on your testreport.
* `projectImage` - (Optional) Include a project image in your testreport.
* `print_args` - (Optional) If set to `true`, Behat will add all arguments for each step to the report. (E.g. Tables).
* `print_outp` - (Optional) If set to `true`, Behat will add the output of each step to the report. (E.g. Exceptions).
* `loop_break` - (Optional) If set to `true`, Behat will add a separating break line after each execution when printing Scenario Outlines.
* `show_tags` - (Optional) If set to `true`, Behat will add tags when printing Scenario's and features.

## Screenshots

To generate screenshots in your testreport you have to change your `FeatureContext.php`:
#### From:
```php
# FeatureContext.php
class FeatureContext extends MinkContext
{
...
}
```

#### To:
```php
# FeatureContext.php
class FeatureContext extends elkan\BehatFormatter\Context\BehatFormatterContext
{
...
}
```

## Todo:
- save html on failures
- save REST responses in testreport
- JSON output - if wanted?
- colors in print stylesheet
- custom footer image/text

## License and Authors

Authors: https://github.com/ElkanRoelen/BehatFormatter/graphs/contributors

