<?php
/**
 * @author Xunnamius <me@xunn.io>
 */

namespace Application\ORM\Entity;

/**
 * This class represents AuxiliaryEvent entities within the ORM, as well as the
 * static gateway for all AuxiliaryEvent entities. In essence, this class is
 * both entity and entity gateway.
 */
class AuxiliaryEvent extends AbstractEntityStaticEntityGateway
{
    public function __get($property)
    {
        // TODO: implement sub-entities
        return parent::__get($property);
    }

    /**
     * Returns some unique (per table) identifier representing this entity.
     * 
     * @return mixed
     */
    public function getUniqueId()
    {
        return $this->eventId;
    }

    /**
     * Returns the SQL table this entity actually belongs to.
     * 
     * @return string
     */
    public static function getOriginTable()
    {
        return 'auxiliary_events';
    }

    /**
     * Returns the primary key column name this entity represents.
     * 
     * @return string
     */
    public static function getPrimaryProperties()
    {
        return 'event_id';
    }

    /**
     * Returns the internal data schema this entity adheres to.
     * 
     * @return array
     */
    public static function getSchema()
    {
        return [
            'event_id',
            'title',
            'description',
            'start_dt',
            'end_dt',
        ];
    }
}
