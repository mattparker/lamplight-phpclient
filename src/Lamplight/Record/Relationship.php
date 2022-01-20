<?php

namespace Lamplight\Record;

use Lamplight\Client;

/**
 *
 *
 * For creating relationships between profiles
 *
 * @category   Lamplight
 * @package    Lamplight\Record
 * @copyright  Copyright (c) 2010 - 2022, Lamplight Database Systems Limited, http://www.lamplightdb.co.uk
 * @license    http://www.lamplight-publishing.co.uk/license.php    BSD License
 * @author     Matt Parker <matt@lamplightdb.co.uk>
 * @version    2.0 New version - add Relationship class
 * @link       http://www.lamplight-publishing.co.uk/api/phpclient.php  Worked examples and documentation for using the
 *     client library
 *
 *
 */
class Relationship extends Mutable {


    /**
     * @var String        The method used for sending requests via the API
     */
    protected string $lamplightMethod = 'relationship';

    /**
     * @var String        The action used for sending requests via the API
     */
    protected string $lamplightAction = 'people';

    /**
     * @param int $profile_id_1
     * @param int $profile_id_2
     * @param int $relationship_id
     * @return $this
     * @throws \Exception
     */
    public function setRelationship (int $profile_id_1, int $profile_id_2, int $relationship_id) : Relationship {
        $this->set('id', $profile_id_1);
        $this->set('related_profile_id', $profile_id_2);
        $this->set('relationship_id', $relationship_id);
        return $this;
    }

    public function toAPIArray (): array {
        return [
            'id' => $this->get('id'),
            'related_profile_id' => $this->get('related_profile_id'),
            'relationship_id' => $this->get('relationship_id')
        ];
    }
}