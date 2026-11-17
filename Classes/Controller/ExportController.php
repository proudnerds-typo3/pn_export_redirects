<?php

declare(strict_types=1);

namespace ProudNerds\PnExportRedirects\Controller;

use ProudNerds\PnExportRedirects\Repository\ExportDemand;
use ProudNerds\PnExportRedirects\Service\RedirectExportService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Backend\Module\ModuleProvider;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Redirects\Repository\RedirectRepository;

#[AsController]
final class ExportController
{
    public const ROUTE = 'pn_export_redirects_export';

    private const MODULE = 'redirects';

    public function __construct(
        private readonly RedirectRepository $redirectRepository,
        private readonly RedirectExportService $exportService,
        private readonly ModuleProvider $moduleProvider,
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly StreamFactoryInterface $streamFactory,
    ) {}

    public function exportAction(ServerRequestInterface $request): ResponseInterface
    {
        $backendUser = $this->getBackendUser();
        if (!$this->moduleProvider->accessGranted(self::MODULE, $backendUser)
            || !$backendUser->check('tables_select', 'sys_redirect')
        ) {
            return $this->responseFactory->createResponse(403);
        }

        $demand = ExportDemand::fromQueryParams($request->getQueryParams());
        $redirects = $this->redirectRepository->findRedirectsByDemand($demand);
        $csv = $this->exportService->createCsv($redirects, $request);

        return $this->responseFactory->createResponse()
            ->withHeader('Content-Type', 'text/csv; charset=utf-8')
            ->withHeader('Content-Disposition', 'attachment; filename="redirects-' . date('Ymd-Hi') . '.csv"')
            ->withBody($this->streamFactory->createStream($csv));
    }

    private function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
