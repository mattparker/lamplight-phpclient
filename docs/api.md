# API endpoints

## Base URL

The base uri to use is https://lamplight.online/api. To this uri, you need to append two parameters, separated by 
a forward slash (/), which describe what kind of data you want, and the type of request: 
https://lamplight.online/api/what/howmany: for example https://lamplight.online/api/workarea/all

## Credentials

All requests need the API credentials you get from within Lamplight (admin > system admin > publishing settings).
These should be added to every request.  You also need to include a format=json key-pair.  A full request 
would like something like this:

```
https://lamplight.online/api/workarea/all?format=json&lampid=123&project=4&key=<api_key>
```

Note that these parameters can be passed as query pairs or as part of the path if you prefer.

## Data returned

Data returned from GET requests will by a json structure like this:

```json
{
  "data": [
    {"id":1, "title":"This is a test record"},
    {"id":2, "title":"This is another"}
  ],"meta":{
    "numRecords": 2,
    "totalRecords": 20
 }
}
```

Although the fields in the data array will vary depending on the data type.




## Contents

[Introduction and authentication](api.html)

[Requesting and creating profiles](profiles.html)

[Requesting and attending work records](work.html)

[Creating referral records](referral.html)

[Error codes](errors.html)

[Hints and tips](hints.htmls)