# Lamplight Publishing Module API Documentation

The Lamplight publishing module allows you to interact with a Lamplight system.  You can request certain types of 
record and insert and update others.

Lamplight comes with a set of built-in widgets that allow you to use the publishing module that are sufficient
for many cases.  Sometimes however you'll need to customise your integration beyond what's available through
the built-in widgets.

This API documentation documents the API endpoints and also the php client that uses them and is available from
this repository.

## Contents

[Introduction and authentication](api.html)

[Requesting and creating profiles](profiles.html)

[Requesting and attending work records](work.html)

[Creating referral records](referral.html)

[Error codes](errors.html)

[Hints and tips](hints.htmls)


## Getting started:

1. Login to the Lamplight system you are integrating with as a system administrator.
2. Ensure you have the publishing module enabled (admin > system administration > add or remove modules and projects)
3. Go to admin > system administration > manage publishing settings
4. Grab the API key and access parameters from the last tab.
5. Logout and go to https://lamplight.online/api/workareas/all/format/json?lampid={lampid}&project={project}&key={api key}

You should see a json data structure listing the workareas in the system.  If you've got this far then you've got valid
credentials and enough set up in Lamplight to get a valid response.


## Getting started with the php client:

1. Install using composer (or clone from GitHub).
2. Save the API credentials you got earlier.  The best way to handle these will depend on your environment, but
a simple way is to save them in a file you can include:

**lamplight_credentials.php**:

```php
<?php
define('LAMPLIGHT_LAMPID', '{lampid}');
define('LAMPLIGHT_PROJECT', '{project}');
define('LAMPLIGHT_APIKEY', '{key}');
```

3. Instantiate a Lamplight\Client and request the workareas:

```php
<?php 
require_once 'lamplight_credentials.php';

$client = new \Lamplight\Client(null, [
    'key' => LAMPLIGHT_APIKEY, 
    'lampid' => LAMPLIGHT_LAMPID, 
    'project' => LAMPLIGHT_PROJECT
]);
$workarea_response = $client->fetchWorkarea()->fetchAll()->request();

// raw json:
$workareas_json = $workarea_response->getBody()->getContents();

// parsed for you:
$workareas = $client->getRecordSet();
echo $workareas->render('ID is {id} and value is {text}. <br>');

```


## Security

The API exposed by Lamplight is the minimal required for the kinds of things that our customers
need to do. It is not a full API for all functionality.  We assume that the data held in Lamplight is private
and should only be made available through the API in a very controlled fashion.

Lamplight provides:

- Secret API key and credentials: your password to the data. Keys are long and well-encrypted. Keys can be renewed at any time.
- SSL: all data is transferred using https, meaning it's encrypted as it travels from our server to yours
- Publishing is switched off by default: you have to turn it on explicitly
- Even then, nothing is publishable. You have to enable publishing by type of data (work records, organisations, people) and what fields may be shared
- Even then, nothing is publishable! You have to explicitly publish particular records (a particular work records, the profile for a particular organisation) etc.

You should ensure that the API credentials are stored securely on your server.  They can be changed at any time, and 
we'd recommend that you store them in such a way that it's easy to update them if needed.

If something isn't working, the first thing to check will be the settings within Lamplight to ensure that the functionality
you want is enabled.

