# pn_export_redirects

Adds an **Export (CSV)** button to the redirects module (Link Management > Redirects).

- Exports exactly the list in the module: the active filters and ordering apply, pagination does not.
- CSV for a Dutch Excel: semicolon-separated, UTF-8 with BOM. Cells that a spreadsheet would evaluate as a formula get a leading `'`.
- Besides the stored target (`t3://page?uid=…`) the file contains the resolved, absolute target URL.
- Only users with access to the redirects module and read access to `sys_redirect` can export. Non-admins get the same redirects as in the module.

Requires TYPO3 14.3. No configuration.

## Installation

```bash
composer require proudnerds/pn-export-redirects
```

## Notes for maintainers

- Built on `ModifyRedirectManagementControllerViewDataEvent` and `RedirectRepository::findRedirectsByDemand()`. `Demand` is marked `@internal` in EXT:redirects; check `ExportDemand` on every TYPO3 major upgrade.
- In TYPO3 14 the module shows one redirect type at a time (default, QR code, short URL); the export follows the selected type.

## Development

```bash
composer install
.Build/bin/phpunit -c phpunit.xml
```

## License

GPL-2.0-or-later, see [LICENSE](LICENSE).
