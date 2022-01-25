# Creating referral records

A referral record is a type of activity record showing when someone was referred to another person or organisation. It
has a date, workarea, text notes, and list of profiles involved: usually the person making the referral; the person who
is being referred, and the person or organisation to whom they are being referred.

Referrals can be accepted via the API allowing you to include a referral form on your website.

You can't GET referral records, they can only be POSTed to save new ones.

## Endpoint

The uri to create a new referral is `https://lamplight.online/api/referral/add/`.

You will also need to send the following parameters

| Param    | Type          | Notes                                                                                                 |
|----------|---------------|-------------------------------------------------------------------------------------------------------|
| attendee | string or int | Identifier of the attendee - email address or Lamplight profile ID                                    |
| referral_reason | string | Plain text, which populates the 'referral reason' field in Lamplight                                  |
| date_from | string | Date of referral, in YYYY-mm-dd format. Uses current date time if none passed                         |
| workareaid | int | ID of workarea to attach to the record                                                                |
| Name_of_custom_field | string | Custom field data, with field name spaces replaced by _ and values matching the Lamplight text values |

Custom referral fields can also be added, where they have been set to be updatable in the system admin section. To add
custom field values, send the field name (with spaces replaced by underscores) and valid data.

The return data will be the ID of the record created:

```json
{
  "data": {
    "id": 123,
    "attend": true
  },
  "meta": ""
}
```

## Creating a referral record with the php client

To create a referral using the php client, first create a `\Lamplight\Record\Referral` record, and then 
`save` it with the `\Lamplight\Client`:

```php
// create the Referral - data can be passed to the constructor or set later
$record = new \Lamplight\Record\Referral([
    'attendee' => 653, 
    'reason' => 'testing add referrals', 
    'date' => '2022-01-01 13:13'
]);
$record->setWorkarea(22);
$record->set('My_custom_field', 'Orange');

// Save using the Client
$client = new \Lamplight\Client(null, $api_credentials);
$client->save($record);

// Get details of what happened:
$saved_details = $client->getDatainResponse();
if ($saved_details->isSuccessful()) {
    $id_of_referral = $saved_details->current()->getId();
} else {
    $error = $saved_details->getErrorMessage();
}

```
