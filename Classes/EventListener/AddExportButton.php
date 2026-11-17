<?php

declare(strict_types=1);

namespace ProudNerds\PnExportRedirects\EventListener;

use ProudNerds\PnExportRedirects\Controller\ExportController;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\Components\Buttons\LinkButton;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\IconSize;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Redirects\Event\ModifyRedirectManagementControllerViewDataEvent;

/**
 * The button links to the export with the module's current filters and ordering,
 * so the CSV contains exactly the list the editor is looking at (all pages of it).
 */
#[AsEventListener(identifier: 'pn-export-redirects/add-export-button')]
final class AddExportButton
{
    public function __construct(
        private readonly UriBuilder $uriBuilder,
        private readonly IconFactory $iconFactory,
    ) {}

    public function __invoke(ModifyRedirectManagementControllerViewDataEvent $event): void
    {
        $view = $event->getView();
        if (!$view instanceof ModuleTemplate) {
            return;
        }

        $demand = $event->getDemand();
        $parameters = [
            'demand' => $demand->getParameters(),
            'orderField' => $demand->getOrderField(),
            'orderDirection' => $demand->getOrderDirection(),
            'redirectType' => $demand->getRedirectType(),
        ];

        // makeInstance instead of ButtonBar::makeLinkButton(), which is deprecated in TYPO3 v14
        $button = GeneralUtility::makeInstance(LinkButton::class)
            ->setHref((string)$this->uriBuilder->buildUriFromRoute(ExportController::ROUTE, $parameters))
            ->setTitle($this->getLanguageService()->sL('LLL:EXT:pn_export_redirects/Resources/Private/Language/locallang.xlf:button'))
            ->setShowLabelText(true)
            ->setIcon($this->iconFactory->getIcon('actions-download', IconSize::SMALL));

        $view->getDocHeaderComponent()->getButtonBar()->addButton($button, ButtonBar::BUTTON_POSITION_LEFT, 20);
    }

    private function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
