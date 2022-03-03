<?php

namespace Lamplight\Record;


/**
 *
 *
 * For adding profiles to groups or waiting lists
 *
 * @category   Lamplight
 * @package    Lamplight\Record
 * @copyright  Copyright (c) 2010 - 2022, Lamplight Database Systems Limited, http://www.lamplightdb.co.uk
 * @license    http://www.lamplight-publishing.co.uk/license.php    BSD License
 * @author     Matt Parker <matt@lamplightdb.co.uk>
 * @version    2.0 New version - add GroupMembership class
 *
 *
 */
class GroupMembership extends Mutable {

    /**
     * @var String        The method used for sending requests via the API
     */
    protected string $lamplightMethod = 'group';

    /**
     * @var String        The action used for sending requests via the API
     */
    protected string $lamplightAction = 'people';

    /**
     * @param int $profile_id
     * @param int $group_id
     * @param string $notes
     * @param \DateTime|null $date_joined
     * @return void
     * @throws \Exception
     */
    public function setGroupMembership (int $profile_id, int $group_id, string $notes = '', \DateTime $date_joined = null) {
        $this->set('id', $profile_id);
        $this->set('group_id', $group_id);
        if ($notes) {
            $this->set('notes', $notes);
        }
        if ($date_joined) {
            $this->set('date_joined', $date_joined);
        }
    }

    /**
     * @return array
     */
    public function toAPIArray (): array {

        $date_joined = $this->get('date_joined');
        if ($date_joined && $date_joined instanceof \DateTimeInterface) {
            $date_joined = $date_joined->format('Y-m-d H:i:s');
        }
        return [
            'id' => $this->get('id'),
            'group_id' => $this->get('group_id'),
            'notes' => (string)$this->get('notes'),
            'date_joined' => (string)$date_joined
        ];
    }

}
