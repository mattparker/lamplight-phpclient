# Requesting profiles - people or organisation records

You can use the API to request details of published profiles - people, organisations, or families. You need to ensure
that they're allowed to be published, in system admin, and then that a particular profile is published.

The endpoint for organisation type records is `https://lamplight.online/api/orgs/`, for people is
`https://lamplight.online/api/people/` and for families is `https://lamplight.online/api/family/`. The endpoints behave
in the same way.

## Requesting all profiles

The endpoint to request all profiles is `https://lamplight.online/api/orgs/all/role/{role}`, 
`https://lamplight.online/api/people/all/role/{role}`, or `https://lamplight.online/api/family/all/role/{role}`. 
You will need to include the {role} of the profiles you require.

The default values for these in Lamplight are: `user`, `contact`, `staff`, `funder`, or `org`. Within the actual system
these defaults may be translated: in the API you need to use the untranslated versions. A single profile must have one
role, and may have several. A request may only have one role, though.

You can also include a `return` parameter. If you set this to `full` you'll receive all the data for that record. If
it's `short` (or left blank) you'll get minimal data back.

### Return values - `short`

```json
{
  "data": [
    {
      "id": 1,
      "name": "Matt Parker",
      "summary": "A string of text entered into the Publishing tab of this profile"
    }
  ],
  "meta": {
    "numRecords": 1,
    "totalRecords": 20
  }
}
```

See below for the data structure for `return=full` requests.

## Requesting some profiles

The endpoint to request some profiles is `https://lamplight.online/api/orgs/some/role/{role}`, 
`https://lamplight.online/api/people/some/role/{role}` or `https://lamplight.online/api/family/some/role/{role}`.
You will need to include the {role} of the profiles you require.

You can use the following search parameters for the profiles to list:

| Param | Type | Notes |
| --- | --- | --- |
| num | int | Number of records (optional: default is same as global settings for 'rows per page') |
| start | int | Offset to start from (optional: default = 0) |
| order | string | Field to order by. Must be a publishable field. The default is to return records sorted in ascending date order. |
| q | string | Search string. All publishable fields are searched. |
| return | string | `short` or `full` (see above) |
| near | string | geographic identifier (see below) |
| nearRadius | int | Radius of the circle to look around the `near` location, in metres |

### Searching using other fields

In addition, you may also append multiple fieldname/value pairs to the request. If these fields are recognised as
publishable fields data will be filtered using a 'contains' match.

This means that we strongly recommend that your custom fields do not contain underscores (_), as there will be no way to
distinguish between spaces and underscores, and the field will not be recognised.

For example: `https://lamplight.online/api/people/some/First_language/English`

### Geographic searches

The `near` parameter can be a UK postcode, a latitude/longitude, or a northing/easting. Lamplight will first check if
it's a valid UK postcode, and if that fails, use the presence of a decimal place to decide if it thinks it's Lat/Lng or
Northing/Easting. Moral is, don't send Northing/Easting requests with decimal points in. So, for example, any
of `L22 0PJ`
or `51.1082257095090000,-1.0293690518454999` or `134773,468047` will work. Note that address information will need to
have been previously geo-coded in Lamplight for results to be returned.

## Requesting one profile

The endpoint to request a single profiles is `https://lamplight.online/api/orgs/one/role/{role}/id/{id}`,
`https://lamplight.online/api/people/one/role/{role}//id/{id}` or `https://lamplight.online/api/family/one/role/{role}/id/{id}`  
You will need to include the {role} and id of the profile you require.

```json
{
  "data": [
    {
      "id": 1,
      "first_name": "Matt",
      "surname": "Parker",
      "address_line_1": "123 Test Road",
      "address_line_2": "Liverpool",
      "address_line_3": "etc",
      "address_line_4": "",
      "address_line_5": "",
      "postcode": "L22 0PJ",
      "email": "testing@example.com",
      "web": "www.lamplightdb.co.uk",
      "phone": "020 7558 8793",
      "mobile": "",
      "custom_field": "single value",
      "another_field": [
        "multi",
        "select"
      ]
    }
  ],
  "meta": {
    "numRecords": 1,
    "totalRecords": 20
  }
}
```

Records may additionally contain publishable custom fields. Field names will be the field names with spaces replaced by
underscores (_). Return values will depend on the field type - see `custom_field` and `another_field` in the sample
above. Multi-select fields will return arrays of strings.

If an organisation is requested, the `first_name` and `surname` fields will be replaced with `name`.

## Creating Profiles

The endpoint to create a new profile for a person is `https://lamplight.online/api/people/add/role/{role}`, an
organisation is `https://lamplight.online/api/orgs/add/role/{role}` and a family is `https://lamplight.online/api/family/add/role/{role}`.
You will need to ensure that the settings to enable creation of the relevant type of profile is enabled in system admin.

Requests must be POSTed.  `{role}` is a required field and must be one of  `user`, `contact`, `staff`, `funder`,
or `org`.

| Param    | Type          | Notes                                                                                                 |
|----------|---------------|-------------------------------------------------------------------------------------------------------|
| first_name | string |Their first name. |
| surname | string | Their surname. |
| address_line_1 | string | Their address line 1. |
| address_line_2 | string | Their address line 2. |
| address_line_3 | string | Their address line 3. |
| address_line_4 | string | Their address line 4. |
| address_line_5 | string | Their address line 5. |
| postcode | string | Their postcode |
| mobile | string | Their mobile number |
| phone | string | Their phone number |
| email | string | Their email |
| web | string | Their website address |
| publishable | bool | Whether to make the profile publishable immediately |

Creating organisations and families is the same, except the `first_name` and `surname` fields will not be recognised, and the `name`
field should be used.

Data must be valid to be accepted. The regex for postcodes used
is `/^[A-Z]{1,2}[0-9R][0-9A-Z]? [0-9][ABD-HJLNP-UW-Z]{2}$/`. For UK mobile phones it
is `/^(0|\+44 |\+44)7[0-9]{1}[\d]{2}[\s]{0,1}[\d]{6}$/` and for international phones
`/^\+(9[976]\d|8[987530]\d|6[987]\d|5[90]\d|42\d|3[875]\d|2[98654321]\d|9[8543210]|8[6421]|6[6543210]|5[87654321]|4[987654310]|3[9643210]|2[70]|7|1)\d{1,14}$/`
. Republic of Ireland phones are `/^(0|\+353( )?)8[\d]{1}( )?[\d]{3}( )?[\d]{4}$/`

You can also add custom fields, where they have been enabled for updating within Lamplight. The format and approach is
the same as when requesting these fields. The field name should be the text of the field, in all lower case, with
underscores '_' replacing any spaces. The value should be valid for the field. If the field has options (i.e. a select,
checkbox, radio etc) the value should be the text value of the option. For example:

```
gender=Male
date_of_birth=1982-04-16
```

To set custom fields to be able to be alterable via the API you will need to go to edit profile custom fields in the
main admin menu.

### Creating profiles using the php client

Create a `\Lamplight\Record\People`, `\Lamplight\Record\Orgs` or `\Lamplight\Record\Family` record, and save it with the Client:

```php

// Create the record:
$profile = new \Lamplight\Record\People([
    'role' => Client::USER_ROLE, // required
    'first_name' => 'Matt',
    'surname' => 'Parker',
    'postcode' => 'SW1A 1AA',
    'Gender' => 'Not willing to say', // Custom field, set to 'allow updates via the API'
    'Date_of_birth' => '1980-01-01', // Custom field, set to 'allow updates via the API'
    'Lives_in_area?' => '1', // Custom field, set to 'allow updates via the API'
    'publishable' => '1'
]);
// can also set() data:
$profile->set('address_line_1', '123 Test Lane');

// create the client and save the profile
$client = new \Lamplight\Client(null, $api_credentials);
$client->save($profile);

$saved_response = $client->getDatainResponse();

if ($saved_response->success()) {
    $profile_id = $saved_response->current()->getId();
} else {
    // handle the error
}

```

## Altering profiles

Profiles can also be updated, using the  `https://lamplight.online/api/people/update/role/{role}`,
`https://lamplight.online/api/orgs/update/role/{role}` or `https://lamplight.online/api/family/update/role/{role}` 
URLs for people, organisations and families respectively. You will need to
ensure that the settings to enable updating of profiles is enabled in system admin.

You will also need to set the ID of the record you wish to amend. That profile will need to be set to allow updating via
the API, using the publishing tab in that profile in Lamplight.

### Altering profiles using the php client

Create a `\Lamplight\Record\People`, `\Lamplight\Record\Orgs` or `\Lamplight\Record\Family` record, ensuring it has 
an ID, and save it with the `Client`:

```php

// Create the record:
$profile = new \Lamplight\Record\People([
    'id' => 1234,
    'role' => Client::USER_ROLE, // still required
    'postcode' => 'SW1A 1AA', // if fields are missing, it won't be overwritten
    'Gender' => 'Not willing to say', // Custom field, set to 'allow updates via the API'
    'Date_of_birth' => '1980-01-01', // Custom field, set to 'allow updates via the API'
    'Lives_in_area?' => '1', // Custom field, set to 'allow updates via the API'
    'publishable' => '1'
]);
// can also set() data:
$profile->set('address_line_1', '123 Test Lane');

// create the client and save the profile
$client = new \Lamplight\Client(null, $api_credentials);
$client->save($profile);

$saved_response = $client->getDatainResponse();

if ($saved_response->success()) {
    $profile_id = $saved_response->current()->getId();
} else {
    // handle the error
}

```

## Creating relationships between profiles

You can create relationships between profiles (e.g. parent-child; employer-employee etc, depending on your system)
using the `https://lamplight.online/api/people/relationship/` url.

You will need to POST the following data:

| Param    | Type          | Notes                                                                                                 |
|----------|---------------|-------------------------------------------------------------------------------------------------------|
| id | int | Profile ID of the first profile to link |
| related_profile_id | int | Profile ID of the second profile to link |
| relationship_id | int | ID of the relationship, from Lamplight system admin |
| role | string | Role of the profile |

The response is a simple message:

```json
{
  "msg": "Relationship created"
}
```

### Creating relationships using the php client

Create a `\Lamplight\Record\Relationship` record, ensuring it has an ID, and save it with the Client:

```php
$relationship = new \Lamplight\Record\Relationship();
$relationship->setRelationship($profile_1_id, $profile_2_id, $relationship_id);

$client = new \Lamplight\Client(null, $api_credentials);
$client->save($relationship);

$response = $this->sut->getDatainResponse();
if ($response->success()) {
    // good
} else {
    // handle error
}

```


## Adding a profile to a Manual Group or Waiting List

Manual Groups and Waiting Lists in Lamplight are related types of entities - essentially lists of profiles.  You can
use the API to add a profile to a Manual Group or Waiting List using the `https://lamplight.online/api/people/group/` url.

You will need to enable this in Lamplight settings before you can use the API method.

You will need to POST the following data:

| Param           | Type          | Notes                                                                       |
|-----------------|---------------|-----------------------------------------------------------------------------|
| id              | int | Profile ID of the first profile to link                                     |
| group_id        | int | Lamplight ID of the group or waiting list                                   |
| role | string | Role of the profile                                                         |
| date_joined | date | (optional) YYYY-mm-dd HH:ii:ss format date to register when they were added |
| notes | string | (optional)) Text to be added to their membership of the group               |

The response is a simple message:

```json
{
  "msg": "Added to group"
}
```

You can't add profiles to auto or merge groups like this.  They are saved searches, so you can only add a profile to 
one of these by setting their attributes to meet the conditions of the search.  If you try you will receive an error
message.


### Adding to groups or waiting lists using the php client

Create a `\Lamplight\Record\GroupMembership` record, with the profile ID and group ID, and save it using the Client:

```php
$profile_1 = 934;
$group_id = 123;
$notes = 'Added to their membership of the group';
$date_joined = new \DateTime(); // Relevant for waiting lists

$group_membership = new \Lamplight\Record\GroupMembership();
$group_membership->setGroupMembership($profile_1, $group_id, $notes, $date_joined);

$client = new \Lamplight\Client(null, $api_credentials);
$client->save($group_membership);

$response = $this->sut->getDatainResponse();
if ($response->success()) {
    // good
} else {
    // handle error
}

```

## Contents

[Introduction and authentication](api.html)

[Requesting and creating profiles](profiles.html)

[Requesting and attending work records](work.html)

[Creating referral records](referral.html)

[Error codes](errors.html)

[Hints and tips](hints.htmls)
