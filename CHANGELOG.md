# CHANGELOG


## Version 2

The new version of the API aims to be as backward-compatible with version 1 as possible, but modernises
the client and making it installable using composer.

The new client and associated classes:
 - uses namespaces, so previous references to Lamplight_Client will need to be replaced with \Lamplight\Client etc.
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

If you had extended the \Lamplight_Client class and used this you may need to check your code




version
1.22    Change to new URL lamplight.online
1.21    Adds support for geographic search
1.2     Update to support add and edit profiles
1.11    Adds returnShortData() and returnFullData() methods for some people/org
1.1     Update to include 'attend work' and 'add referrals' datain module functionality

1.      Initial release.