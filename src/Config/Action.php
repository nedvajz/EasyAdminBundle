<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Config;

use EasyCorp\Bundle\EasyAdminBundle\Dto\ActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;

final class Action
{
    public const DELETE = 'delete';
    public const DETAIL = 'detail';
    public const EDIT = 'edit';
    public const INDEX = 'index';
    public const NEW = 'new';
    public const SAVE_AND_ADD_ANOTHER = 'saveAndAddAnother';
    public const SAVE_AND_CONTINUE = 'saveAndContinue';
    public const SAVE_AND_RETURN = 'saveAndReturn';

    // these are the actions applied to a specific entity instance
    public const TYPE_ENTITY = 'entity';
    // these are the actions that are not associated to an entity
    // (they are available only in the INDEX page)
    public const TYPE_GLOBAL = 'global';
    // these are actions that can be applied to one or more entities at the same time
    public const TYPE_BATCH = 'batch';

    /** @var FieldDto */
    private $dto;

    private function __construct(ActionDto $actionDto)
    {
        $this->dto = $actionDto;
    }

    public function __toString()
    {
        return $this->dto->getName();
    }

    /**
     * @param string|false|null $label Use FALSE to hide the label; use NULL to autogenerate it
     */
    public static function new(string $name, $label = null, ?string $icon = null): self
    {
        $dto = new ActionDto();
        $dto->setType(self::TYPE_ENTITY);
        $dto->setName($name);
        $dto->setLabel($label ?? ucfirst($name));
        $dto->setIcon($icon);
        $dto->setHtmlElement('a');
        $dto->setHtmlAttributes([]);
        $dto->setTranslationParameters([]);

        return new self($dto);
    }

    public function createAsGlobalAction(): self
    {
        $this->dto->setType(self::TYPE_GLOBAL);

        return $this;
    }

    public function createAsBatchAction(): self
    {
        $this->dto->setType(self::TYPE_BATCH);

        return $this;
    }

    /**
     * @param string|false|null $label Use FALSE to hide the label; use NULL to autogenerate it
     */
    public function setLabel($label): self
    {
        $this->dto->setLabel($label);

        return $this;
    }

    public function setIcon(?string $icon): self
    {
        $this->dto->setIcon($icon);

        return $this;
    }

    public function setCssClass(string $cssClass): self
    {
        $this->dto->setCssClass($cssClass);

        return $this;
    }

    public function displayAsLink(): self
    {
        $this->dto->setHtmlElement('a');

        return $this;
    }

    public function displayAsButton(): self
    {
        $this->dto->setHtmlElement('button');

        return $this;
    }

    public function setHtmlAttributes(array $attributes): self
    {
        $this->dto->setHtmlAttributes($attributes);

        return $this;
    }

    public function setTemplate(string $templatePath): self
    {
        $this->dto->setTemplatePath($templatePath);

        return $this;
    }

    public function linkToCrudAction(string $crudActionName): self
    {
        $this->dto->setCrudActionName($crudActionName);

        return $this;
    }

    public function linkToRoute(string $routeName, array $routeParameters = []): self
    {
        $this->dto->setRouteName($routeName);
        $this->dto->setRouteParameters($routeParameters);

        return $this;
    }

    /**
     * If not defined, actions use the same domain as configured for the entire dashboard.
     */
    public function setTranslationDomain(string $domain): self
    {
        $this->dto->setTranslationDomain($domain);

        return $this;
    }

    public function setTranslationParameters(array $parameters): self
    {
        $this->dto->setTranslationParameters($parameters);

        return $this;
    }

    public function displayIf(callable $callable): self
    {
        $this->dto->setDisplayCallable($callable);

        return $this;
    }

    public function getAsDto(): ActionDto
    {
        if (null === $this->dto->getLabel() && null === $this->dto->getIcon()) {
            throw new \InvalidArgumentException(sprintf('The label and icon of an action cannot be null at the same time. Either set the label, the icon or both for the "%s" action.', $this->dto->getName()));
        }

        if (null === $this->dto->getCrudActionName() && null === $this->dto->getRouteName()) {
            throw new \InvalidArgumentException(sprintf('Actions must link to either a route or a CRUD action. Set the "linkToCrudAction()" or "linkToRoute()" method for the "%s" action.', $this->dto->getName()));
        }

        return $this->dto;
    }
}
