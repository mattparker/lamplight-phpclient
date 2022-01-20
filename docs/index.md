# Lamplight Publishing Module API Documentation

The Lamplight publishing module allows you to interact with a Lamplight system.  You can request certain types of 
record and insert and update others.

Lamplight comes with a set of built-in widgets that allow you to use the publishing module that are sufficient
for many cases.  Sometimes however you'll need to customise your integration beyond what's available through
the built-in widgets.

This API documentation documents the API endpoints and also the php client that uses them and is available from
this repository.

## Getting started:

1. Login to the Lamplight system you are integrating with as a system administrator.
2. Ensure you have the publishing module enabled (admin > system administration > add or remove modules and projects)
3. Go to admin > system administration > manage publishing settings
4. Grab the API key and access parameters from the last tab.
5. Logout and go to https://lamplight.online/api/workareas/all/format/json?lampid=<lampid>&project=<project>&key=<api key>

You should see a json data structure listing the workareas in the system.  If you've got this far then you've got valid
credentials and enough set up in Lamplight to get a valid response.


## Getting started with the php client:

1. Install using composer (or clone from GitHub).
2. Save the API credentials you got earlier.  The best way to handle these will depend on your environment, but
a simple way is to save them in a file you can include:

`lamplight_credentials.php`:

```php
define('LAMPLIGHT_LAMPID', '<lampid>');
define('LAMPLIGHT_PROJECT', '<project>');
define('LAMPLIGHT_APIKEY', '<key>');

```

3. Instantiate a Lamplight\Client and request the workareas:

```php 
require_once 'lamplight_credentials.php';

$client = new \Lamplight\Client(null, ['key' => LAMPLIGHT_APIKEY, 'lampid' => LAMPLIGHT_LAMPID, 'project' => LAMPLIGHT_PROJECT]);
$workarea_response = $client->fetchWorkarea()->fetchAll()->request();

// raw json:
$workareas_json = $workarea_response->getBody()->getContents();

// parsed for you:
$workareas = $client->getRecordSet();
echo $workareas->render('ID is {id} and value is {text}. <br>');

```