<?php
/**
 * DefinitionChecker_Test file
 */

use INTERMediator\DefinitionChecker;
use PHPUnit\Framework\TestCase;

class DefinitionChecker_Test extends TestCase
{
    private DefinitionChecker $checker;

    protected function setUp(): void
    {
        $this->checker = new DefinitionChecker();
    }

    private function dataSourceMessage(array $context): string
    {
        return $this->checker->checkDefinitions([$context], null, null);
    }

    public function testValidDefinitionsProduceNoMessage(): void
    {
        $message = $this->checker->checkDefinitions(
            [[
                'name' => 'person',
                'key' => 'id',
                'records' => 10,
                'paging' => true,
                'repeat-control' => 'insert-confirm',
                'navi-control' => 'master-hide',
                'soft-delete' => 'deleted',
                'port' => '443',
                'query' => [['field' => 'age', 'value' => 20, 'operator' => '>']],
                'file-upload' => [['field' => 'f', 'container' => 'S3']],
                'send-mail' => ['new' => ['to' => 'a@example.com', 'f-option' => true]],
            ]],
            [
                'transaction' => 'automatic',
                'authentication' => ['storing' => 'credential', 'expiring-seconds-2FA' => 300, 'authexpired' => '3600'],
                'formatter' => [['field' => 'f', 'converter-class' => 'Number', 'parameter' => true]],
                'smtp' => ['port' => 587],
            ],
            ['db-class' => 'PDO', 'port' => 5432, 'option' => [], 'external-db' => ['a' => 'b']]
        );
        $this->assertSame('', $message);
    }

    public function testNullDataSourceIsReported(): void
    {
        $message = $this->checker->checkDefinitions(null, null, null);
        $this->assertSame('*** The Data Sources of the Definition must be specified. ***', $message);
    }

    public function testUndefinedKeyword(): void
    {
        $message = $this->dataSourceMessage(['name' => 'a', 'unknown-key' => 'x']);
        $this->assertStringContainsString('[0][unknown-key] includes an undefined keyword.', $message);
    }

    public function testSimpleTypeMismatches(): void
    {
        $message = $this->dataSourceMessage([
            'name' => 1,
            'records' => '10',
            'paging' => 'yes',
            'numeric-fields' => 'a',
            'relation' => 'x',
            'query' => [['value' => []]],
        ]);
        $this->assertStringContainsString('[0][name] should be defined as string.', $message);
        $this->assertStringContainsString('[0][records] should be defined as integer.', $message);
        $this->assertStringContainsString('[0][paging] should be defined as boolean.', $message);
        $this->assertStringContainsString('[0][numeric-fields] should be defined as array.', $message);
        $this->assertStringContainsString('[0][relation] should be defined as array.', $message);
        $this->assertStringContainsString('[0][query][0][value] should be defined as scalar.', $message);
    }

    public function testEnumeratedString(): void
    {
        $this->assertSame('', $this->dataSourceMessage(['repeat-control' => 'delete']));
        $message = $this->dataSourceMessage(['repeat-control' => 'remove']);
        $this->assertStringContainsString('[0][repeat-control] should be defined as string within [insert|', $message);
    }

    public function testEnumeratedStringWithMultipleKeywords(): void
    {
        $this->assertSame('', $this->dataSourceMessage(['repeat-control' => 'delete insert']));
        $this->assertSame('', $this->dataSourceMessage(['repeat-control' => ' confirm-delete  copy-detail ']));
        $this->assertSame('', $this->dataSourceMessage(['sync-control' => 'update create delete']));
        $this->assertNotSame('', $this->dataSourceMessage(['repeat-control' => 'delete remove']));
        $this->assertNotSame('', $this->dataSourceMessage(['repeat-control' => '']));
        $this->assertNotSame('', $this->dataSourceMessage(['repeat-control' => '   ']));
    }

    public function testEnumeratedStringIsStrict(): void
    {
        $message = $this->dataSourceMessage(['repeat-control' => true]);
        $this->assertStringContainsString('[0][repeat-control] should be defined as string within', $message);
    }

    public function testWildcardInEnumeratedString(): void
    {
        $this->assertSame('', $this->dataSourceMessage(['repeat-control' => 'copy-detail']));
        $this->assertNotSame('', $this->dataSourceMessage(['repeat-control' => 'copyx']));
        $this->assertNotSame('', $this->dataSourceMessage(['repeat-control' => ['copy-a']]));
    }

    public function testCompositeTypes(): void
    {
        $this->assertSame('', $this->dataSourceMessage(['soft-delete' => true]));
        $this->assertSame('', $this->dataSourceMessage(['soft-delete' => 'flag']));
        $message = $this->dataSourceMessage(['soft-delete' => 1]);
        $this->assertStringContainsString('[0][soft-delete] should be defined as boolean or string.', $message);
    }

    public function testCompositeWithEnumeratedString(): void
    {
        $this->assertSame('', $this->dataSourceMessage(['file-upload' => [['container' => true]]]));
        $this->assertSame('', $this->dataSourceMessage(['file-upload' => [['container' => 'Dropbox']]]));
        $message = $this->dataSourceMessage(['file-upload' => [['container' => 'FTP']]]);
        $this->assertStringContainsString(
            '[0][file-upload][0][container] should be defined as boolean or string within [FileSystem|', $message);
    }

    public function testMessagesAccumulate(): void
    {
        $message = $this->dataSourceMessage(['repeat-control' => 'bad', 'paging' => 'bad']);
        $this->assertStringContainsString('[0][repeat-control]', $message);
        $this->assertStringContainsString('[0][paging]', $message);
    }

    public function testMessageIsNotCarriedOverToNullSection(): void
    {
        $message = $this->checker->checkDefinitions([['bad-key' => 1]], null, null);
        $this->assertStringContainsString('The Data Sources of the Definition:', $message);
        $this->assertStringNotContainsString('The Options of the Definition:', $message);
        $this->assertStringNotContainsString('The DB Specification of the Definition:', $message);
    }

    public function testSectionsAreLabeled(): void
    {
        $message = $this->checker->checkDefinitions([], ['transaction' => 'manual'], ['port' => []]);
        $this->assertStringContainsString('The Options of the Definition: [transaction]', $message);
        $this->assertStringContainsString('The DB Specification of the Definition: [port] should be defined as string or integer.', $message);
    }

    public function testAllTypeSpecificationsAreKnown(): void
    {
        $reflection = new ReflectionClass($this->checker);
        $match = $reflection->getMethod('matchType');
        $split = $reflection->getMethod('splitTypes');
        foreach (['prohibitKeywordsForDataSource', 'prohibitKeywordsForOption', 'prohibitKeywordsForDBSpec'] as $prop) {
            $specs = [];
            $definitions = $reflection->getProperty($prop)->getValue($this->checker);
            array_walk_recursive($definitions,
                function ($spec) use (&$specs) {
                    $specs[] = $spec;
                });
            foreach ($specs as $spec) {
                foreach ($split->invoke($this->checker, $spec) as $type) {
                    $match->invoke($this->checker, $type, null);
                }
            }
        }
        $this->assertTrue(true);
    }
}
