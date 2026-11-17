<?php

declare(strict_types=1);

namespace Proudnerds\PnExportRedirects\Tests\Unit\Service;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Proudnerds\PnExportRedirects\Service\CsvWriter;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class CsvWriterTest extends UnitTestCase
{
    private const BOM = "\xEF\xBB\xBF";

    private CsvWriter $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new CsvWriter();
    }

    #[Test]
    public function writeStartsWithUtf8ByteOrderMarkSoExcelDetectsTheEncoding(): void
    {
        self::assertStringStartsWith(self::BOM, $this->subject->write([['a']]));
    }

    #[Test]
    public function writeSeparatesColumnsWithSemicolonsAndRowsWithCrlf(): void
    {
        $csv = $this->subject->write([['ID', 'Bronpad'], ['12', '/oude-pagina']]);

        self::assertSame(self::BOM . "ID;Bronpad\r\n12;/oude-pagina\r\n", $csv);
    }

    #[Test]
    public function writeQuotesValuesContainingDelimitersOrQuotes(): void
    {
        $csv = $this->subject->write([['a;b', 'zeg "hoi"']]);

        self::assertSame(self::BOM . '"a;b";"zeg ""hoi"""' . "\r\n", $csv);
    }

    #[Test]
    public function writeDoesNotTreatBackslashesAsEscapeCharacter(): void
    {
        $csv = $this->subject->write([['#^/nieuws/(\d+)\\"#']]);

        self::assertSame(self::BOM . '"#^/nieuws/(\d+)\\""#"' . "\r\n", $csv);
    }

    #[Test]
    public function writeKeepsNonAsciiCharactersIntact(): void
    {
        self::assertStringContainsString('/café-één', $this->subject->write([['/café-één']]));
    }

    #[Test]
    public function writeLeavesPathsAndUrlsUntouched(): void
    {
        $csv = $this->subject->write([['/pad', 'https://www.tilburg.nl/', 't3://page?uid=1']]);

        self::assertSame(self::BOM . "/pad;https://www.tilburg.nl/;t3://page?uid=1\r\n", $csv);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function formulaValuesDataProvider(): array
    {
        return [
            'equals sign' => ['=1+1', "'=1+1"],
            'plus sign' => ['+31', "'+31"],
            'minus sign' => ['-1', "'-1"],
            'at sign' => ['@SUM(A1)', "'@SUM(A1)"],
        ];
    }

    #[Test]
    #[DataProvider('formulaValuesDataProvider')]
    public function writePrefixesValuesThatSpreadsheetsWouldEvaluateAsFormula(string $value, string $expectedCell): void
    {
        self::assertSame(self::BOM . $expectedCell . "\r\n", $this->subject->write([[$value]]));
    }
}
