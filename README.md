# Metro Publisher Location Import Script

A simple PHP command-line script to import a CSV file of business locations directly into Metro Publisher.

[View the Sample CSV import file](https://github.com/WidgetsBurritos/metro-publisher-import/blob/master/sample.csv)

Note: System has only been used and tested on Mac OS X. If you run any other operating system,
you may need to adapt your instructions accordingly.

## How to Use It

### Installing for the first time.

1. Clone this respository onto your system using your system terminal:

  ```
  mkdir -p ~/github/ 
  cd ~/github/
  git clone https://github.com/WidgetsBurritos/metro-publisher-import.git
  cd metro-publisher-import
  ```
  
2. Next install and composer to download necessary dependencies:
  ```
  curl -sS https://getcomposer.org/installer | php
  php composer.phar install
  ```
  
3. Copy `default.settings.php` to `settings.php`
  ```
  cp default.settings.php settings.php
  ```
  
4. Open `settings.php` in a text editor, and add your Metro Publisher api key and secret.


### Importing Listings

If you wish to import data directly from a CSV file into Metro Publisher you can do so using the following command:

```
php import.php import-file.csv
```

***WARNING:** Every time you run this script, it will **replace** any locations in MetroPublisher that have matching UUIDs, so use cautiously.*

### Populating empty UUID fields

If any rows in your CSV file are missing a UUID (i.e. a Universally Unique Identifier) you can add one using the
`fix-uuid.php` script.

For example, if you wish to take the contents of `src.csv`, add a UUID to each row, and then save the results to
`dest.csv` you may do so using the following command:

```
php fix-uuid.php src.csv dest.csv
```

Note: In order to protect existing files from accidental overwrite, if dest.csv exists, you must use the `--force` flag
at the tail end of your command:

```
php fix-uuid.php src.csv dest.csv --force
```

Additionally if you want to update the same file you are importing from, just specify that file as both the source and
destination files:
```
php fix-uuid.php src.csv src.csv --force
```

### Populating Empty Latitude/Longitude Fields
If any rows in your CSV are missing latitude/longitude coordinates you can import them in this manner:
```
php gen-lat-long.php src.csv dest.csv
```

[Same rules as the UUID process apply if the dest.csv file already exists.](#populating-empty-uuid-fields)

Note: This uses Google's Geocoder service, which has rate limits. You may have to run this script multiple times over
multiple days to geocode every locations, if you have a large amount of locations in your CSV file.

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

### Splitting Addresses

If your source data has street numbers and names combined, you can split them into separate fields like this:

```
php split-address.php src.csv dest.csv
```

[Same rules as the UUID process apply if the dest.csv file already exists.](#populating-empty-uuid-fields)

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