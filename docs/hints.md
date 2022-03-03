# Hints and tips

If you're developing using the Lamplight publishing module for the first time, the following hints may be helpful:

## 1. Familiarise yourself with Lamplight and where the data should end up

Understanding what profiles, relationships, work records, and referrals are, and how they relate to one another,
will make things much easier.  

One common use of the publishing module is to accept referrals.  However, what the charity might actually mean by this is
a) create a profile for the service user
b) create a profile for the referring person
c) create a referral record, with the service user and referring person listed on it.

Knowing where the data's got to end up in situations like this will make your use of the API much smoother.

You'll also want to be able to find records.  To list profiles go to `people > view`, and you may need to change the filter
to 'Limit list to' profiles added via the API.  To see referrals and work records go to `activity > view` on the main menu. 

## 2. Understand the security model

Still inside of Lamplight, you'll need to find your way around the settings you need to enable to get data in and out:

a) the publishing module settings (in `admin > system admin > manage publishing settings`). This is where different aspects
of the API can be turned on or off, and various settings can be configured.

b) custom tabs and fields (in `admin > system admin > manage custom tabs and fields in profiles`).  If you need to access
or update custom fields (for profiles or referrals), you'll need to ensure that they are set to allow publishing out
or data in to *each field*.

c) individual records need to be published.  In profiles and work records, this is in the `publishing` tab. 

If you are working with custom tabs and fields, we'd strongly recommend you download an Excel version of the settings 
in the system (`admin > system admin > Download field settings from your system`), which gives you the exact wording
of every field and option, and likely save some time building forms.


## 3. Start by getting a list of workareas

If you can get that much working you know you're on the right track - you have the right credentials, and if you're using
the php client you've got your php configuration working.  A list of workareas is the simplest API call.

## 4. Make it easy to run tests

The php client, and Lamplight itself, has unit tests, and you'll thank yourself for writing your code in a testable way.

Something like the example below allows you to write unit tests by mocking the `\Lamplight\Client`.  
It also allows you to run live tests using an array of data, rather than having to keep completing your web form each time.

```php
class MyImplementationToCreateAReferral {

    protected \Lamplight\Client $client;
    
    /**
     * @param \Lamplight\Client $client
     */
    public function __construct (\Lamplight\Client $client) {
        $this->client = $client;
    }
    
    /**
     * @param array $form_data
     * @return int
     */
    public function createReferral (array $form_data) {
        
        try {
            $profile_1_id = $this->createClientProfile($form_data);
        } catch (\Exception $e) {
            return 0; // handle your errors...
        }
        
        try {
            $profile_2_id = $this->createReferrerProfile($form_data);
        } catch (\Exception $e) {
            return 0; // handle your errors...
        }
        
        try {
            $referral = $this->createLamplightReferral($profile_1, $profile_2, $form_data);
        } catch (\Exception $e) {
            return 0; // handle your error states...
        }
        return 1;
    }    
    
    /**
     * @param array $form_data
     * @return int
     * @throws Exception
     */
    protected function createClientProfile (array $form_data) :int {
        
        $profile = new \Lamplight\Record\Person();
        $profile->set('first_name', $form_data['first_name']);
        // etc.
        
        $this->client->resetClient();  // this is important if you're making lots of calls
        $this->client->save($profile);
        $response = $this->client->getDatainResponse();
        if ($response->isSuccessful()) {
            return $response->current()->getId();
        }
        throw new \Exception("no profile 1: " . $response->getErrorMessage());
        
    }
    
    protected function createReferrerProfile (array $form_data) : int {
        // TODO - similar to above
    }
    
    protected function createLamplightReferral (int $profile_1, int $profile_2, array $form_data) {
        
        $referral = new \Lamplight\Record\Referral([
            'attendee' => $profile_1, 'referrer' => $profile_2, 'reason' => $form_data['referral_reason']
        ]);
        // etc.
        
        $this->client->resetClient();
        $this->client->save($referral);
        
        return $this->client->getDatainResponse();
        
    }
    
    
}

```

The tests in `SandboxTests\GetLiveDataFromSandboxTest.php` may provide helpful pointers too.


## 5. Create a testing sandbox

We can't provide a standardised testing sandbox because every system is different in the workareas, settings, tabs
and fields.

But you can add short-lived projects to the live system that you can use to develop against, and then switch to the live
project when ready.


## 6. Make sure all system admins understand the dependencies

Your integration is likely to depend on the settings in Lamplight.  Make sure that all the system administrators understand
that if they change fields involved in the implementation, they are likely to break it.  We'd suggest these are documented
for them.


## 7. Call resetClient() if you're using the php client

If you are building something that involved multiple calls to the \Lamplight\Client, call `resetClient()` first.





## Contents

[Introduction and authentication](api.html)

[Requesting and creating profiles](profiles.html)

[Requesting and attending work records](work.html)

[Creating referral records](referral.html)

[Error codes](errors.html)

[Hints and tips](hints.htmls)