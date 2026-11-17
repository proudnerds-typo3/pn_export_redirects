<?php

declare(strict_types=1);

namespace ProudNerds\PnExportRedirects\Repository;

use TYPO3\CMS\Redirects\Repository\Demand;

/**
 * All redirects matching the filters of the redirects module, without pagination.
 *
 * Demand::fromRequest() is not used: it reads the "moduleData" request attribute,
 * which only exists on module routes.
 */
final class ExportDemand extends Demand
{
    /**
     * @param array<string, mixed> $queryParams
     */
    public static function fromQueryParams(array $queryParams): self
    {
        $filter = is_array($queryParams['demand'] ?? null) ? $queryParams['demand'] : [];
        $sourceHost = (string)($filter['source_host'] ?? '');
        $statusCode = (int)($filter['target_statuscode'] ?? 0);

        return new self(
            orderField: (string)($queryParams['orderField'] ?? self::DEFAULT_ORDER_FIELD),
            orderDirection: (string)($queryParams['orderDirection'] ?? self::ORDER_ASCENDING),
            redirectType: (string)($queryParams['redirectType'] ?? self::DEFAULT_REDIRECT_TYPE),
            sourceHosts: $sourceHost !== '' ? [$sourceHost] : [],
            sourcePath: (string)($filter['source_path'] ?? ''),
            target: (string)($filter['target'] ?? ''),
            statusCodes: $statusCode > 0 ? [$statusCode] : [],
            maxHits: (int)($filter['max_hits'] ?? 0),
            creationType: isset($filter['creation_type']) ? (int)$filter['creation_type'] : -1,
            protected: isset($filter['protected']) ? (int)$filter['protected'] : -1,
            integrityStatus: isset($filter['integrity_status']) ? (string)$filter['integrity_status'] : null,
            createdBy: isset($filter['createdby']) ? (int)$filter['createdby'] : -1,
        );
    }

    public function getLimit(): int
    {
        return PHP_INT_MAX;
    }

    public function getOffset(): int
    {
        return 0;
    }
}
