<?php

namespace Lamplight\Datain;

use Lamplight\Client;
use Lamplight\Response as LamplightResponse;
use Lamplight\Datain\Exception\LastRequestWasNotDataInException;

/**
 *
 * Lamplight php API client
 *
 * Copyright (c) 2010 - 2022, Lamplight Database Systems Limited, http://www.lamplightdb.co.uk
 * Code licensed under the BSD License:
 * http://www.lamplight-publishing.co.uk/license.php
 *
 * @category   Lamplight
 * @author     Matt Parker <matt@lamplightdb.co.uk>
 * @copyright  Copyright (c) 2010 - 2022, Lamplight Database Systems Limited, http://www.lamplightdb.co.uk
 * @license    http://www.lamplight-publishing.co.uk/license.php   BSD License
 * @version    2.0 New version - Creates a ResponseCollection from the last request/response
 */
class ResponseCollectionFactory {


    /**
     * @var Client
     */
    protected Client $client;

    /**
     * @var LamplightResponse
     */
    protected LamplightResponse $response;

    /**
     * @param Client $client
     * @return ResponseCollection
     * @throws LastRequestWasNotDataInException
     */
    public function createResponseFromClient (Client $client) : ResponseCollection {

        $this->client = $client;

        $this->validateClient();

        return $this->createResponseCollectionFromResponse($client->getLastResponse());

    }


    /**
     * @param LamplightResponse $response
     * @return ResponseCollection
     */
    protected function createResponseCollectionFromResponse (LamplightResponse $response) : ResponseCollection {

        $this->response = $response;
        $response_body = json_decode($response->getBody()->getContents());


        if ($response_body && property_exists($response_body, 'data')) {

            // this is the multiple one:
            if (is_array($response_body->data)) {
                return $this->handleMultipleResponses($response, $response_body);
            }

            return $this->handleSingleResponse($response, $response_body, $this->client->getParameter('id'));


        } else if ($response_body && property_exists($response_body, 'error')) {

            return $this->handleErrorResponse($response, $response_body);

        }

        return new ResponseCollection($response);

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


    /**
     * @param \stdClass $rec
     * @return SavedRecordResponse
     */
    protected function makeResponseFromData (\stdClass $rec): SavedRecordResponse {
        return new SavedRecordResponse(
            (int)$rec->id,
            (bool)$rec->attend,
            (property_exists($rec, 'error') && $rec->error > 0 ? (int)$rec->error : 0),
            (property_exists($rec, 'msg') ? $rec->msg : '')
        );
    }


    /**
     * @param LamplightResponse $response
     * @param $response_body
     * @return ResponseCollection
     */
    protected function handleMultipleResponses (LamplightResponse $response, $response_body): ResponseCollection {
        $collection = new ResponseCollection($response);
        foreach ($response_body->data as $rec) {
            $collection->addSavedResponse($this->makeResponseFromData($rec));
        }
        return $collection;
    }


    /**
     * @param LamplightResponse $response
     * @param $response_body
     * @param $id_of_record_sent_by_client
     * @return ResponseCollection
     */
    protected function handleSingleResponse (LamplightResponse $response, $response_body, $id_of_record_sent_by_client): ResponseCollection {

        $collection = new ResponseCollection($response);

        if (is_object($response_body->data)) {

            $collection->addSavedResponse($this->makeResponseFromData($response_body->data));
            return $collection;

        }
        if (!$id_of_record_sent_by_client && $response_body->data > 0) {

            $collection->addSavedResponse(new SavedRecordResponse(
                (int)$response_body->data, true
            ));
            return $collection;

        }
        $collection->addSavedResponse(new SavedRecordResponse(
            $id_of_record_sent_by_client, true
        ));

        return $collection;
    }


    /**
     * @param LamplightResponse $response
     * @param $response_body
     * @return ResponseCollection
     */
    protected function handleErrorResponse (LamplightResponse $response, $response_body): ResponseCollection {

        return new ResponseCollection($response, [
            new SavedRecordResponse(
                0, false, $response_body->error, $response_body->msg
            )
        ]);

    }


}
