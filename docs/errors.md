# Error codes

The Lamplight API will return error codes in the following circumstances.  In some cases, the response message will
include additional information about the error to help you resolve it.

<dl>
    <dt>1001</dt>
    <dd>"Role of person not recognised" The role parameter was invalid. May be returned to person or organisation requests.</dd>
    <dt>1002</dt>
    <dd>"Person of type requested is not publishable" Either the global publishing settings do not allow publishing of this type of person/organisation, or the particular one being requested is not published.</dd>
    <dt>1003</dt>
    <dd>"Only people or organisations may be requested" Internal error.</dd>
    <dt>1004</dt>
    <dd>"Method not recognised: should be one of all, some or one": your uri is incorrect.</dd>
    <dt>1005</dt>
    <dd>"Method all not permitted with work records"</dd>
    <dt>1006</dt>
    <dd>"Work records are not publishable" Either the global publishing settings do not allow publishing of work records, or the particular one being requested is not published.</dd>
    <dt>1007</dt>
    <dd>"An unknown error occurred. It is likely an unpublished record was requested". The most likely situation that this error will be returned is if a non-existent, or non-published record is requested (perhaps by manually changing an ID value).</dd>
    <dt>1008</dt>
    <dd>Workareas can only use method all</dd>

    <dt>1020</dt>
    <dd>"The data in module for work records is not enabled". The datain functionality is not available with your current Lamplight subscription. Please contact us to enable it.</dd>
    <dt>1021</dt>
    <dd>"No valid work record id was passed to attend". The id parameter should be an integer, returned by Lamplight.</dd>
    <dt>1022</dt>
    <dd>"The attendee did not pass a valid identifier". The email address provided is not valid.</dd>
    <dt>1023</dt>
    <dd>"The record may not be added to". Work records in Lamplight need to have the 'allow attendees to be added via the API' checkbox selected.</dd>
    <dt>1024</dt>
    <dd>"The attendee could not be unambiguously identified in the database". This means that they either are not there at at all, or appear more than once. A message will be created within Lamplight for the system administrator, providing details.</dd>
    <dt>1025</dt>
    <dd>"There are no spaces available on the record requested". If set, the maximum number of attendees has been reached and no more people can be added to the record. A message will be created within Lamplight for the system administrator, providing details.</dd>
    <dt>1026</dt>
    <dd>"This attendee is already attending the record requested". They are already listed.</dd>
    <dt>1027</dt>
    <dd>"This type of record (work etc) may not be accessed through the API". You are trying to add someone to a record of a type that has been denied access - your system administrator will need to set this in the datain section of the admin menu.</dd>
    <dt>1040</dt>
    <dd>Referral data provided was not valid</dd>
    <dt>1050</dt>
    <dd>Updating person or organisation data must be POSTed</dd>
    <dt>1051</dt>
    <dd>If you want to update a record you need to provide a valid ID</dd>
    <dt>1052</dt>
    <dd>Do not have permission to update this type of body</dd>
    <dt>1053</dt>
    <dd>This particular profile may not be updated</dd>
    <dt>1054</dt>
    <dd>Do not have permission to add profiles of this type</dd>
    <dt>1055</dt>
    <dd>Data provided to add/update profile was not valid</dd>
    <dt>1056</dt>
    <dd>No valid data found to add/update</dd>
    <dt>1057</dt>
    <dd>There was a problem saving the core name/address fields for the update</dd>
    <dt>1058</dt>
    <dd>There was a problem saving some custom fields: most likely values provided are not valid for the field. Core details (name/address) will have been saved if provided.</dd>
    
    <dt>1070</dt>
    <dd>API access to relationships is not authorised. They need to be switched on within Lamplight</dd>
    <dt>1071</dt>
    <dd>Incorrect parameter types to create a relationship between profiles</dd>
    <dt>1072</dt>
    <dd>Relationship between profiles could not be created</dd>
    <dt>1073</dt>
    <dd>To create relationship data use a POST request</dd>

    <dt>9999</dt>
    <dd>Data provided may have been a malicious attack and has not been added. Specifically, POSTed content includes html tags and the request is immediately rejected.</dd>
</dl>