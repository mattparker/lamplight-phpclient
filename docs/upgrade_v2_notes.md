# Notes on upgrading from previous version to current version of the php client

The new version of the API aims to be as backward-compatible with version 1 as possible, but modernises
the client and making it installable using composer.  The API endpoints etc. have not changed.

The new client and associated classes:
- use namespaces, so previous references to Lamplight_Client will need to be replaced with \Lamplight\Client etc.
- injects a GuzzleHttp\Client into the constructor
- is fully unit tested

## Backward incompatible changes

### The \Lamplight\Client no longer extends \Zend_Http_Client

This makes it much easier to test, as the http client (now Guzzle) is injected into the constructor.

The GuzzleHttp\Client dependency has been made the optional first argument of the constructor.  If it is not
passed then the constructor will instantiate the GuzzleHttp\Client required for you.  The second argument to the
constructor is technically optional but will usually be passed the API credentials.  This maintains close
backward compatibility: the first paramater of the \Lamplight_Client class was previously the URI, but wasn't
required because it was set by the client.  So although the type of the first parameter has changed, it should
be easy to swap to the new version.

However, methods previously inherited from the \Zend_Http_Client are for the most part not implemented,
so any reliance on the parent class in your implementation will need to be updated.

Methods that are implemented are:
- setParameterGet($key, $value)
- setParameterPost($key, $value)

As these are likely to be used in implementations for custom field data.

### protected function _constructUri () now returns string

If you had extended the \Lamplight_Client class and used this you may need to check your code.


### Significant changes to \Lamplight_Datain_Response class

These classes are used to parse responses to API calls that create records.  The previous version of the library
had a single `\Lamplight_Datain_Response` class that did a lot.

This has been replaced with a `\Lamplight\Datain\ResponseCollection` which holds `\Lamplight\Datain\SavedRecordResponse`
objects.  These are created by the `\Lamplight\Datain\ResponseCollection\Factory`, which in turn is called from
`\Lamplight\Client::getDatainResponse`.

So in short, a call now to `\Lamplight\Client::getDatainResponse` returns a `\Lamplight\Datain\ResponseCollection`.
While this has some similarity to the the old class, it also implements the `\Lamplight\Response` interface to try and
make the interaction with requesting and sending data more consistent.

### New features

- The API allows creation of relationships between profiles.
- Referrals can send a 'referrer' parameter
- Attendances at work records can include 'referral_notes'


