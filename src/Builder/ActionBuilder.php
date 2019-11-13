<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Builder;

use EasyCorp\Bundle\EasyAdminBundle\Configuration\Action;
use EasyCorp\Bundle\EasyAdminBundle\Context\ActionContext;
use EasyCorp\Bundle\EasyAdminBundle\Context\ApplicationContext;
use EasyCorp\Bundle\EasyAdminBundle\Context\ApplicationContextProvider;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\ItemCollectionBuilderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ActionBuilder implements ItemCollectionBuilderInterface
{
    private $isBuilt;
    /** @var ActionContext[] */
    private $builtActions;
    /** @var Action[] */
    private $actionConfigs;
    private $authChecker;
    private $urlGenerator;
    private $translator;
    private $applicationContextProvider;

    public function __construct(ApplicationContextProvider $applicationContextProvider, AuthorizationCheckerInterface $authChecker, TranslatorInterface $translator, UrlGeneratorInterface $urlGenerator)
    {
        $this->applicationContextProvider = $applicationContextProvider;
        $this->authChecker = $authChecker;
        $this->translator = $translator;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param Action $actionConfig
     */
    public function addItem($actionConfig): ItemCollectionBuilderInterface
    {
        $this->actionConfigs[] = $actionConfig;
        $this->resetBuiltActions();

        return $this;
    }

    /**
     * @param Action[] $actionConfigs
     */
    public function setItems(array $actionConfigs): ItemCollectionBuilderInterface
    {
        $this->actionConfigs = $actionConfigs;
        $this->resetBuiltActions();

        return $this;
    }

    /**
     * @return ActionContext[]
     */
    public function build(): array
    {
        if (!$this->isBuilt) {
            $this->buildActions();
            $this->isBuilt = true;
        }

        return $this->builtActions;
    }

    private function resetBuiltActions(): void
    {
        $this->builtActions = [];
        $this->isBuilt = false;
    }

    private function buildActions(): void
    {
        $this->resetBuiltActions();

        $applicationContext = $this->applicationContextProvider->getContext();
        $defaultTranslationDomain = $applicationContext->getConfig()->getTranslationDomain();

        foreach ($this->actionConfigs as $actionConfig) {
            $actionContext = $actionConfig->getAsValueObject();
            if (!$this->authChecker->isGranted($actionContext->getPermission())) {
                continue;
            }

            $generatedActionUrl = $this->generateActionUrl($applicationContext, $actionContext);
            $translatedActionLabel = $this->translator->trans($actionContext->getLabel(), $actionContext->getTranslationParameters(), $actionContext->getTranslationDomain() ?? $defaultTranslationDomain);
            $translatedActionHtmlTitle = $this->translator->trans($actionContext->getHtmlTitle(), $actionContext->getTranslationParameters(), $actionContext->getTranslationDomain() ?? $defaultTranslationDomain);

            $this->builtActions[] = $actionContext->withProperties([
                'htmlTitle' => $translatedActionHtmlTitle,
                'label' => $translatedActionLabel,
                'url' => $generatedActionUrl,
            ]);
        }
    }

    private function generateActionUrl(ApplicationContext $applicationContext, ActionContext $actionContext): string
    {
        $requestParameters = $applicationContext->getRequest()->query->all();

        if (null !== $routeName = $actionContext->getRouteName()) {
            $routeParameters = array_merge($actionContext->getRouteParameters(), [
                'page' => $applicationContext->getPage(),
                'id' => $applicationContext->getEntity()->getIdValue(),
            ]);

            return $this->urlGenerator->generate($routeName, $routeParameters);
        }

        if ('index' !== $actionContext->getMethodName()) {
            $routeParameters = array_merge($requestParameters, [
                'page' => $applicationContext->getPage(),
                'id' => $applicationContext->getEntity()->getIdValue(),
            ]);

            return $this->urlGenerator->generate($applicationContext->getDashboardRouteName(), $routeParameters);
        }

        // for the 'index' action, try to use the 'referer' value if it exists
        if ($applicationContext->getRequest()->query->has('referer')) {
            return urldecode($applicationContext->getRequest()->query->has('referer'));
        }

        return $this->urlGenerator->generate($applicationContext->getDashboardRouteName(), array_merge($requestParameters, ['page' => 'index']));
    }
}
