<?php

namespace Lamplight\Datain;

use Lamplight\Client;
use Lamplight\Response as LamplightReponse;
use Lamplight\Datain\Exception\LastRequestWasNotDataInException;

class ResponseCollectionFactory {

    protected Client $client;
    protected \Lamplight\Response $response;

    public function createResponseFromClient (Client $client) : ResponseCollection {

        $this->client = $client;

        $this->validateClient();

        return $this->createResponsesFromResponse($client->getLastResponse());

    }



    protected function createResponsesFromResponse (LamplightReponse $response) {

        $this->response = $response;


        $response_body = json_decode($response->getBody()->getContents());
        $id_of_record_sent_by_client = $this->client->getParameter('id');

        $record_response_details = [];

        // check property exists and id matches originally passed id
        if ($response_body && property_exists($response_body, 'data')) {

            // this is the multiple one:
            if (is_array($response_body->data)) {

                foreach ($response_body->data as $rec) {

                    $record_response_details[] = new SavedRecordResponse(
                        (int)$rec->id,
                        (bool)$rec->attend,
                        (property_exists($rec, 'error') && $rec->error > 0 ? (int)$rec->error : 0),
                        (property_exists($rec, 'msg') ? $rec->msg : '')
                    );

                }


            } else {

                if (!$id_of_record_sent_by_client && $response_body->data > 0) {

                    $record_response_details[] = new SavedRecordResponse(
                        (int)$response_body->data, true
                    );

                } else {
                    $record_response_details[] = new SavedRecordResponse(
                        $id_of_record_sent_by_client, true
                    );

                }
            }

        } else if ($response_body && property_exists($response_body, 'error')) {
            $record_response_details[] = new SavedRecordResponse(
                0, false, $response_body->error, $response_body->msg
            );
        }

        return new ResponseCollection($response, $record_response_details);

    }



    /**
     * @throws LastRequestWasNotDataInException
     */
    protected function validateClient () {
        $client = $this->client;
        // check last request was a datain one:
        $valid_datain_method_action_pairs = array(
            "work/attend",
            "referral/add",
            "people/add",
            "people/update",
            "orgs/add",
            "orgs/update"
        );
        $last_method_action = $client->getLastLamplightAction() . "/" . $client->getLastLamplightMethod();


        if (!in_array($last_method_action, $valid_datain_method_action_pairs)) {

            throw new LastRequestWasNotDataInException("The last request was not a datain one.");

        }

    }


}
