<?php
declare(strict_types = 1);

namespace srag\asq\QuestionPool\Domain\Event;

use ILIAS\Data\UUID\Uuid;
use DateTimeImmutable;
use srag\asq\QuestionPool\Domain\Model\QuestionPoolData;
use Fluxlabs\CQRS\Event\AbstractDomainEvent;

/**
 * Class PoolDataSetEvent
 *
 * @package srag\asq\QuestionPool
 *
 * @author Fluxlabs AG - Adrian Lüthi <adi@fluxlabs.ch>
 */
class PoolDataSetEvent extends AbstractDomainEvent
{
    protected ?QuestionPoolData $data;

    protected ?string $config_for;

    public function __construct(
        Uuid $aggregate_id,
        DateTimeImmutable $occured_on,
        QuestionPoolData $data
    ) {
        $this->data = $data;
        parent::__construct($aggregate_id, $occured_on);
    }

    public function getData() : QuestionPoolData
    {
        return $this->data;
    }

    public function getEventBody() : string
    {
        return json_encode($this->data);
    }

    protected function restoreEventBody(string $event_body) : void
    {
        $this->data = QuestionPoolData::deserialize($event_body);
    }

    public static function getEventVersion() : int
    {
        // initial version 1
        return 1;
    }
}
