# Metro Publisher Location Import Script

A simple PHP command-line script to import a CSV file of business locations directly into Metro Publisher.

##### Disclaimers
- This project has only been used and tested on Mac OS X 10.11.4 (El Capitan). If you run any other operating system, you may need to adapt
your instructions accordingly.
- This project assumes you have PHP 5.3.2+ installed on your system (although 5.5 is recommended). If you don't have it installed, see [PHP: General Installation Considerations](http://php.net/manual/en/install.general.php) to figure out how to best get it installed on your system.
- This project was built to do a one-time import on a localized environment. I am sharing this code in hopes that it may
help someone else import locations into MetroPublisher in the future. It has not been tested or designed to run in any
sort of automated or production environment so use at your own risk.
- Since this project was built for one-time use, I won't be actively updating this or even guaranteeing that it will work
if any of the various API systems change in the future. Feel free to fork and modify this project and adapt it however
you see fit.
- If anyone is interested in actively updating and maintaining this project and would like to build a more
complete PHP MetroPublisher library, let me know and I can add you as a collaborator on the project.


## How to Use It

### Installing for the first time.

1. Clone this repository onto your system using your system terminal:

  ```
  mkdir -p ~/Documents/github/
  cd ~/Documents/github/
  git clone https://github.com/WidgetsBurritos/metro-publisher-import.git
  cd metro-publisher-import
  ```
  
2. Next install and run composer to download necessary dependencies:
  ```
  curl -sS https://getcomposer.org/installer | php
  php composer.phar install
  ```
  
3. Copy `default.settings.php` to `settings.php`
  ```
  cp default.settings.php settings.php
  ```
  
4. Open `settings.php` in a text editor, and update your Metro Publisher api key and secret, and Geonames username.

---

### CSV File Format

The CSV file format is very much tied to the [Location fields used in the Metro Publisher API](https://api.metropublisher.com/resources/location.html#resource-put-location-parameters).

Generally speaking,you should try to match those columns as much as possible.  That said, there are a few extra columns,
in addition to those, that you will need to add to your CSV file.

1. `address_city` -- Used by `gen_geonames.php` to convert a city/state into a Geoname ID
2. `address_state` -- Used by `gen_geonames.php` to convert a city/state into a Geoname ID
3. `address_street_combined` -- Used by `split-address.php` to convert a combined street address, like `123 Main St.`
into two separate columns `streetnumber` = `123` and `street` = `Main St.`

**[View a Sample CSV import file](https://github.com/WidgetsBurritos/metro-publisher-import/blob/master/sample.csv)**

---

### Importing Listings

If you wish to import data directly from a CSV file into Metro Publisher you can do so using the following command:

```
php import.php import-file.csv
```

This process assumes your CSV contains proper fields, including: UUIDs, Geonames, Latitude/Longitude. If you don't, read further down this page on how to generate that.

##### Replacement Warning:
Every time you run this script, it will **replace** any locations in MetroPublisher that have matching UUIDs, so use cautiously.

##### Tags:
This import script does not create tags, but assumes tags already exist in Metro Publisher. This was intentional to avoid cluttering our system with a bunch of incorrect tags. Instead, the import script alerts you if you use an invalid tag, so you can go back and correct it and reimport the listing.

All tags should placed in the `tags` column of your CSV using `URLName` for that listing.

So for example, if you have a listing that should be marked with the `Downtown` and `Tourist Attraction` tags, make sure those tags exist in Metro Publisher. Then find their respective URLNames. In this case `downtown` and `tourist-attraction`.

The value for the last column should be `downtown,tourist-attraction`.

*Note: If you're editing the CSV with a text editor (instead of spreadsheet software like Excel, Numbers, etc...), then make sure the entire string is surrounded with quotation marks, to avoid splitting the field.*

##### Unicode Characters:

Many of the APIs used here have issues with Unicode characters outside of the accepted ASCII character range. As a result, whenever importing a CSV, these special characters get stripped out. If you must have these characters, you will need to add them back manually after import.

##### Memory Issues:

Since this script is importing all rows of a CSV file into active memory, really large CSV files may cause memory issues.  If you run into this problem, is recommended you split your CSV files into to multiple files and try importing in chunks instead of all at once.

---

### Populating empty UUID fields

If any rows in your CSV file are missing a UUID (i.e. a Universally Unique Identifier) you can add one using the
`gen-uuid.php` script.

For example, if you wish to take the contents of `src.csv`, add a UUID to each row, and then save the results to
`dest.csv` you may do so using the following command:

```
php gen-uuid.php src.csv dest.csv
```

Note: In order to protect existing files from accidental overwrite, if dest.csv exists, you must use the `--force` flag
at the tail end of your command:

```
php gen-uuid.php src.csv dest.csv --force
```

Additionally if you want to update the same file you are importing from, just specify that file as both the source and
destination files:
```
php gen-uuid.php src.csv src.csv --force
```

---

### Populating Empty Latitude/Longitude Fields
If any rows in your CSV are missing latitude/longitude coordinates you can import them in this manner:
```
php gen-lat-long.php src.csv dest.csv
```

[Same rules as the UUID process apply if the dest.csv file already exists.](#populating-empty-uuid-fields)

Note: This uses Google's Geocoder service, which has rate limits. You may have to run this script multiple times over
multiple days to geocode every locations, if you have a large amount of locations in your CSV file.

---

### Populating Geoname IDs

You need to have a geonames.org account before you populate geoname ids in your CSV file.

1. [Register an account with geonames.org](http://www.geonames.org/login)
2. Set your geoname user name in your settings.php file.
3. Run the following script to update your geoname id's:
  ```
php gen-geonames.php src.csv dest.csv
```

[Same rules as the UUID process apply if the dest.csv file already exists.](#populating-empty-uuid-fields)

Note: This uses Geoname's ID look up service, which has rate limits. You may have to run this script multiple times over
multiple days to find the geoname id for every location, if you have a large number of locations in your CSV file.

---

### Splitting Addresses

If your source data has street numbers and names combined, you can split them into separate fields like this:

```
php split-address.php src.csv dest.csv
```

[Same rules as the UUID process apply if the dest.csv file already exists.](#populating-empty-uuid-fields)

---

### Recommended Sequence of Events:

The features were explained above by order of importance. That said, it's not a recommended order
to run the commands.

While your situation may be slightly different, here is a recommended sequence of events, assuming your original CSV is named `import.csv`:

1. [Generate UUIDs](#populating-empty-uuid-fields) (if necessary)
```
php gen-uuids.php import.csv import-step1.csv --force
```
2. [Split Addresses](#splitting-addresses) (if necessary)
```
php gen-split-address.php import-step1.csv import-step2.csv --force
```
3. [Generate Latitude/Longitude](#populating-empty-latitudelongitude-fields) (if necessary)
```
php gen-lat-long.php import-step2.csv import-step3.csv --force
```
4. [Generate Geoname IDs](#populating-geoname-ids) (if necessary)
```
php gen-geonames.php import-step3.csv import-step4.csv --force
```
5. [Import your CSV file](#importing-listings)
```
php import.php import-step4.csv
```


---

## Using the MetroPublisher PHP Class

If you wish to just use the MetroPublisher PHP Class to build your own custom import script, you must first import the
Library into your code:


```
require __DIR__ . '/vendor/autoload.php';

use WidgetsBurritos\MetroPublisher;
```

Then create a new MetroPublisher class, and pass in your API key and API secret into the constructor:

```
$MP = new MetroPublisher(API_KEY, API_SECRET);
```

##### To Retrieve a List of Available Tags
```
$all_tags = $MP->getAllTags();
```

##### To Retrieve a Page of Tags
```
$page_tags = $MP->getTagsByPage($page_num, $page_size);
```

##### To Retrieve a Location by UUID
```
$location = $MP->getLocation($uuid);
```

##### To Add/Update a Location on MetroPublisher

Here is an example listing import.
```
$listing_array = array(
    'uuid' => '12345678-1234-4321-9999-123456789012',
    'title' => 'The Alamo',
    'content' => 'Some Content Here',
    'description' => 'The Alamo Mission in San Antonio, commonly called the Alamo and originally known as Misión San Antonio de Valero, is part of the San Antonio Missions World Heritage Site in San Antonio, Texas, United States.',
    'email' => 'Listing email address here',
    'opening_hours' => 'Every day: 9am-5:30pm (Closed Christmas Eve and Day)',
    'streetnumber' => 300,
    'street' => 'Alamo Plaza',
    'state' => 'TX',
    'phone' => '555-555-5555',
    'fax' => '555-555-5556',
    'geoname_id' => '1234567',
    'lat' => '29.4260',
    'long' => '98.4861',
    'website' => 'http://www.thealamo.org/',
    'tags' => 'mission,tourist-attraction,downtown',
);
$MP->putLocation($listing_array);
```

##### To Add a Tag to a Location on MetroPublisher

Assuming you have a UUID for a location and a tag, you can add a tag to a location in the following manner:
```
$MP->setLocationTag($location_uuid, $tag_uuid);
```

##### To generate a UUID

In the event you need a new UUID, you can utilize the Ramsey UUID class.

```
use Ramsey\Uuid\Uuid;

$new_uuid = Uuid::uuid4();
```

*Note: This doesn't guarantee a unique ID, but based on a the very low mathematical probability of a collision, it's a pretty safe bet. If you wish to guarantee uniqueness, that functionality shouldn't be too hard to write.*

##### Exceptions

It is highly recommended that you wrap your code in a try/catch statement, especially if you are having issues.

For example:
```
try {
  $MP = new MetroPublisher(API_KEY, API_SECRET);

  // Additional code goes here.
} catch (\WidgetsBurritos\MetroPublisherException $e) {
  die($e->getMessage()."\n");
}
```

---

## Using the CSVListings PHP Class

If you just want to do simple importing/exporting on your CSV files, you can use the CSVListings class for this. This class is MetroPublisher-specific, and hasn't been thoroughly tested in other contexts.

#### Import the class:
```
require __DIR__ . '/vendor/autoload.php';

use WidgetsBurritos\CSVListings;
```

#### Importing Listings from a CSV File

To import all rows into an array that you can then iterate through, do the following:

```
$file_name = 'import.csv';
$csv_rows = CSVListings::importFromFile($file_name);
```

##### Header Row
This class assumes you have one header row in your csv file. The header row is used to key each row as an associative array, so uniqueness is important.

#### Exporting Listings to a CSV File

To export all rows into a CSV file:
```
$file_name = 'export.csv';
CSVListings::exportToFile($csv_rows, $file_name);
```

##### Header Row
Much like the import, the export will generate a header row. This gets determined by the first row in `$csv_rows`. The script will output all array_keys as the header.

