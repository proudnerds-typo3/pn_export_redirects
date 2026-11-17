<?php

declare(strict_types=1);

namespace Proudnerds\PnExportRedirects\Service;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

final class RedirectExportService
{
    private const LABELS = 'LLL:EXT:pn_export_redirects/Resources/Private/Language/locallang.xlf:';

    private const COLUMNS = [
        'uid',
        'source_host',
        'source_path',
        'is_regexp',
        'target',
        'target_url',
        'target_statuscode',
        'active',
        'protected',
        'starttime',
        'endtime',
        'hitcount',
        'lasthiton',
        'creation_type',
        'createdon',
        'updatedon',
    ];

    public function __construct(
        private readonly CsvWriter $csvWriter,
    ) {}

    /**
     * @param array<int, array<string, mixed>> $redirects sys_redirect rows
     */
    public function createCsv(array $redirects, ServerRequestInterface $request): string
    {
        // Same link resolution as the target column in the redirects module (f:uri.typolink)
        $contentObject = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $contentObject->setRequest($request);

        $rows = [array_map(fn(string $column): string => $this->translate('column.' . $column), self::COLUMNS)];
        foreach ($redirects as $redirect) {
            $target = (string)($redirect['target'] ?? '');
            $rows[] = [
                (string)($redirect['uid'] ?? ''),
                (string)($redirect['source_host'] ?? ''),
                (string)($redirect['source_path'] ?? ''),
                $this->yesNo((bool)($redirect['is_regexp'] ?? false)),
                $target,
                $target !== '' ? $contentObject->createUrl(['parameter' => $target, 'forceAbsoluteUrl' => true]) : '',
                (string)($redirect['target_statuscode'] ?? ''),
                $this->yesNo(!($redirect['disabled'] ?? false)),
                $this->yesNo((bool)($redirect['protected'] ?? false)),
                $this->formatTimestamp($redirect['starttime'] ?? 0),
                $this->formatTimestamp($redirect['endtime'] ?? 0),
                (string)($redirect['hitcount'] ?? '0'),
                $this->formatTimestamp($redirect['lasthiton'] ?? 0),
                $this->formatCreationType($redirect['creation_type'] ?? null),
                $this->formatTimestamp($redirect['createdon'] ?? 0),
                $this->formatTimestamp($redirect['updatedon'] ?? 0),
            ];
        }

        return $this->csvWriter->write($rows);
    }

    private function formatTimestamp(mixed $timestamp): string
    {
        return (int)$timestamp > 0 ? date('Y-m-d H:i', (int)$timestamp) : '';
    }

    private function formatCreationType(mixed $creationType): string
    {
        return match ((string)$creationType) {
            '0' => $this->translate('creation_type.automatic'),
            '1' => $this->translate('creation_type.manual'),
            default => (string)$creationType,
        };
    }

    private function yesNo(bool $value): string
    {
        return $this->translate($value ? 'yes' : 'no');
    }

    private function translate(string $key): string
    {
        return $this->getLanguageService()->sL(self::LABELS . $key);
    }

    private function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
