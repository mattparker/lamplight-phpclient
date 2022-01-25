# Requesting profiles - people or organisation records

You can use the API to request details of published profiles - people or organisations. You need to ensure
that they're allowed to be published, in system admin, and then that a particular profile is published.

The endpoint for organisation type records is `https://lamplight.online/api/orgs/` and for people is
`https://lamplight.online/api/people/`.  The two endpoints behave in the same way.

## Requesting all profiles

The endpoint to request all profiles is `https://lamplight.online/api/orgs/all/role/{role}` 
or `https://lamplight.online/api/people/all/role/{role}`.  You will need to include the {role} of the profiles you require.

The default values for these in Lamplight are: `user`, `contact`, `staff`, `funder`, or `org`.  Within the actual system
these defaults may be translated: in the API you need to use the untranslated versions.  A single profile must have one
role, and may have several.  A request may only have one role, though.

You can also include a `return` parameter.  If you set this to `full` you'll receive all the data for that record.
If it's `short` (or left blank) you'll get minimal data back.

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

The endpoint to request some profiles is `https://lamplight.online/api/orgs/some/role/{role}`
or `https://lamplight.online/api/people/some/role/{role}`.  You will need to include the {role} of the profiles you require.

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

The `near` parameter can be a UK postcode, a latitude/longitude, or a northing/easting. Lamplight will first check if it's a valid UK
postcode, and if that fails, use the presence of a decimal place to decide if it thinks it's Lat/Lng or
Northing/Easting. Moral is, don't send Northing/Easting requests with decimal points in. So, for example, any of `L22 0PJ`
or `51.1082257095090000,-1.0293690518454999` or `134773,468047` will work. Note
that address information will need to have been previously geo-coded in Lamplight for results to be returned.


## Requesting one profile

The endpoint to request a single profiles is `https://lamplight.online/api/orgs/some/role/{role}/id/{id}`
or `https://lamplight.online/api/people/some/role/{role}//id/{id}`.  
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
      "another_field": ["multi", "select"]
    }
  ],
  "meta": {
    "numRecords": 1,
    "totalRecords": 20
  }
}
```

Records may additionally contain publishable custom fields. Field names will be the field names with spaces replaced by 
underscores (_). Return values will depend on the field type - see `custom_field` and `another_field` in the sample above.
Multi-select fields will return arrays of strings.

If an organisation is requested, the `first_name` and `surname` fields will be replaced with `name`.