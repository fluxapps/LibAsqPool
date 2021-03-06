<?php
declare(strict_types = 1);

namespace srag\asq\QuestionPool\Domain\Event;

use ILIAS\Data\UUID\Uuid;
use DateTimeImmutable;
use Fluxlabs\CQRS\Event\AbstractDomainEvent;
use ILIAS\Data\UUID\Factory;

/**
 * Class QuestionRemovedEvent
 *
 * @package srag\asq\QuestionPool
 *
 * @author studer + raimann ag - Team Core 2 <al@studer-raimann.ch>
 */
class QuestionRemovedEvent extends AbstractDomainEvent
{
    protected ?Uuid $question_id;

    public function __construct(
        Uuid $aggregate_id,
        DateTimeImmutable $occured_on,
        ?Uuid $question_id = null
        ) {
            $this->question_id = $question_id;
            parent::__construct($aggregate_id, $occured_on);
    }

    public function getQuestionId() : Uuid
    {
        return $this->question_id;
    }

    public function getEventBody() : string
    {
        return $this->question_id->toString();
    }

    protected function restoreEventBody(string $event_body) : void
    {
        $factory = new Factory();
        $this->question_id = $factory->fromString($event_body);
    }

    public static function getEventVersion() : int
    {
        // initial version 1
        return 1;
    }
}
