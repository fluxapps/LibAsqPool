<?php
declare(strict_types = 1);

namespace srag\asq\QuestionPool\Application;

use ILIAS\Data\UUID\Uuid;
use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use srag\asq\QuestionPool\Application\Command\StorePoolCommand;
use srag\asq\QuestionPool\Application\Command\StorePoolCommandHandler;
use srag\asq\QuestionPool\Domain\Model\QuestionPoolData;
use srag\asq\QuestionPool\Domain\Model\QuestionPoolListItem;
use Fluxlabs\CQRS\Aggregate\AbstractValueObject;
use Fluxlabs\CQRS\Command\CommandBus;
use Fluxlabs\CQRS\Command\CommandConfiguration;
use Fluxlabs\CQRS\Command\Access\OpenAccess;
use srag\asq\Application\Service\ASQService;
use srag\asq\QuestionPool\Application\Command\AddQuestionCommand;
use srag\asq\QuestionPool\Application\Command\AddQuestionCommandHandler;
use srag\asq\QuestionPool\Application\Command\RemoveQuestionCommand;
use srag\asq\QuestionPool\Application\Command\RemoveQuestionCommandHandler;
use srag\asq\QuestionPool\Domain\Model\QuestionPool;
use srag\asq\QuestionPool\Domain\Model\QuestionPoolRepository;
use ILIAS\Data\UUID\Factory;
use srag\asq\QuestionPool\Application\Command\CreatePoolCommand;
use srag\asq\QuestionPool\Application\Command\CreatePoolCommandHandler;

/**
 * Class QuestionPoolService
 *
 * @package srag\asq\QuestionPool
 *
 * @author studer + raimann ag - Team Core 2 <al@studer-raimann.ch>
 */

class QuestionPoolService
{
    private CommandBus $command_bus;

    private QuestionPoolRepository $repo;

    public function __construct()
    {
        $this->command_bus = new CommandBus();

        $this->command_bus->registerCommand(new CommandConfiguration(
            CreatePoolCommand::class,
            new CreatePoolCommandHandler(),
            new OpenAccess()
        ));

        $this->command_bus->registerCommand(new CommandConfiguration(
            AddQuestionCommand::class,
            new AddQuestionCommandHandler(),
            new OpenAccess()
        ));

        $this->command_bus->registerCommand(new CommandConfiguration(
            RemoveQuestionCommand::class,
            new RemoveQuestionCommandHandler(),
            new OpenAccess()
        ));

        $this->command_bus->registerCommand(new CommandConfiguration(
            StorePoolCommand::class,
            new StorePoolCommandHandler(),
            new OpenAccess()
        ));

        $this->repo = new QuestionPoolRepository();
    }

    /**
     * @return Uuid
     */
    public function createQuestionPool(?string $name = null, ?string $description = null, Uuid $uuid = null) : Uuid
    {
        if ($uuid === null) {
            $uuid_factory = new Factory();
            $uuid = $uuid_factory->uuid4();
        }

        $data = new QuestionPoolData($name, $description);

        // CreateQuestion.png
        $this->command_bus->handle(
            new CreatePoolCommand(
                $uuid,
                $data
            )
        );

        return $uuid;
    }

    /**
     * @param Uuid $pool_id
     * @param Uuid $question_id
     */
    public function addQuestion(Uuid $pool_id, Uuid $question_id) : void
    {
        $this->command_bus->handle(
            new AddQuestionCommand(
                $pool_id,
                $question_id
            )
        );
    }

    /**
     * @param Uuid $pool_id
     * @param Uuid $question_id
     */
    public function removeQuestion(Uuid $pool_id, Uuid $question_id) : void
    {
        $this->command_bus->handle(
            new RemoveQuestionCommand(
                $pool_id,
                $question_id
            )
        );
    }

    public function storePoolData(Uuid $pool_id, QuestionPoolData $data) : void
    {
        /** @var $pool QuestionPool */
        $pool = $this->repo->getAggregateRootById($pool_id);

        $pool->setData($data, $this->getActiveUser());

        $this->command_bus->handle(
            new StorePoolCommand(
                $pool,
            )
        );
    }

    public function getPoolData(Uuid $pool_id) : QuestionPoolData
    {
        /** @var $pool QuestionPool */
        $pool = $this->repo->getAggregateRootById($pool_id);

        return $pool->getData();
    }

    public function setConfiguration(Uuid $pool_id, AbstractValueObject $config, string $config_for) : void
    {
        /** @var $pool QuestionPool */
        $pool = $this->repo->getAggregateRootById($pool_id);

        $pool->setConfiguration($config, $config_for);

        $this->command_bus->handle(
            new StorePoolCommand(
                $pool,
            )
        );
    }

    public function getConfiguration(Uuid $pool_id, string $config_for) : ?AbstractValueObject
    {
        /** @var $pool QuestionPool */
        $pool = $this->repo->getAggregateRootById($pool_id);

        return $pool->getConfiguration($config_for);
    }

    public function getConfigurations(Uuid $pool_id) : ?array
    {
        /** @var $pool QuestionPool */
        $pool = $this->repo->getAggregateRootById($pool_id);

        return $pool->getConfigurations();
    }

    public function removeConfiguration(Uuid $pool_id, string $config_for) : void
    {
        /** @var $pool QuestionPool */
        $pool = $this->repo->getAggregateRootById($pool_id);

        $pool->removeConfiguration($config_for);

        $this->command_bus->handle(
            new StorePoolCommand(
                $pool,
            )
        );
    }

    /**
     * @param Uuid $pool_id
     * @return Uuid[]
     */
    public function getQuestionsOfPool(Uuid $pool_id) : array
    {
        /** @var $pool QuestionPool */
        $pool = $this->repo->getAggregateRootById($pool_id);

        return $pool->getQuestions();
    }

    const FILTER_NAME = 'pool_filter_name';
    const FILTER_CREATOR = 'pool_filter_creator';

    /**
     * @param ?array $filters
     * @return QuestionPoolListItem[]
     */
    public function getPools(?array $filters = null) : array
    {
        return $this->repo->getPools($filters);
    }
}