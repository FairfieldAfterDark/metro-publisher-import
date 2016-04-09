# Metro Publisher Location Import Script

A simple PHP command-line script to import a CSV file of business locations directly into Metro Publisher.

Note: System has only been used and tested on Mac OS X. If you run any other operating system,
you may need to adapt your instructions accordingly.

## How to Use It

### Installing for the first time.

1. Clone this respository onto your system using your system terminal:
  ```
  mkdir -p ~/github/ && cd ~/github/ && git clone https://github.com/WidgetsBurritos/metro-publisher-import.git && cd metro-publisher-import
  ```
2. Next install and composer to download necessary dependencies:
  ```
  curl -sS https://getcomposer.org/installer | php && php composer.phar install
  ```
3. Copy `default.settings.php` to `settings.php`
  ```
  cp default.settings.php settings.php
  ```
4. Open `settings.php` in a text editor, and add your Metro Publisher api key and secret.


### Importing Listings

If you wish to import data directly from a CSV file into Metro Publisher you can do so using the following script:

```
php import.php import-file.csv
```

### Populating empty UUID fields

If any rows in your CSV file are missing a UUID (i.e. a Universally Unique Identifier) you can add one using the
`add-uuid-to-csv.php` script.

For example, if you wish to take the contents of `src.csv`, add a UUID to each row, and then save the results to
`dest.csv` you may do so in the following manner:

```
php add-uuid-to-csv.php src.csv dest.csv
```

Note: In order to protect existing files from accidental overwrite, if dest.csv exists, you must use the `--force` flag
at the tail end of your command:

```
php add-uuid-to-csv.php src.csv dest.csv --force
```

Additionally if you want to update the same file you are importing from, just specify that file as both the source and
destination files:
```
php add-uuid-to-csv.php src.csv src.csv --force
```


