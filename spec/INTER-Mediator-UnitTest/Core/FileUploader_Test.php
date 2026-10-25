<?php
/**
 * FileUploader_Test file
 */

use INTERMediator\FileUploader;
use PHPUnit\Framework\TestCase;

class FileUploader_Test extends TestCase
{
    private array $savedPost;
    private array $savedFiles;

    protected function setUp(): void
    {
        $this->savedPost = $_POST;
        $this->savedFiles = $_FILES;
    }

    protected function tearDown(): void
    {
        $_POST = $this->savedPost;
        $_FILES = $this->savedFiles;
    }

    public function testDecideMediaClassNameDefault(): void
    {
        $this->assertSame('FileSystem', FileUploader::decideMediaClassName('PDO', null, 'f'));
        $this->assertSame('FileSystem', FileUploader::decideMediaClassName('PDO', [['field' => 'f']], 'f'));
    }

    public function testDecideMediaClassNameByContainer(): void
    {
        $this->assertSame('AWSS3', FileUploader::decideMediaClassName('PDO', [['container' => 'S3']], 'f'));
        $this->assertSame('Dropbox', FileUploader::decideMediaClassName('PDO', [['container' => 'dropbox']], 'f'));
        $this->assertSame('FileURL', FileUploader::decideMediaClassName('PDO', [['container' => 'FileURL']], 'f'));
        $this->assertSame('FileSystem', FileUploader::decideMediaClassName('PDO', [['container' => 'FileSystem']], 'f'));
    }

    public function testDecideMediaClassNameFileMakerContainer(): void
    {
        foreach ([true, 'FileMaker', 'FileMakerContainer'] as $container) {
            $defs = [['container' => $container]];
            $this->assertSame('FileMakerContainer', FileUploader::decideMediaClassName('FileMaker_DataAPI', $defs, 'f'));
            $this->assertSame('FileMakerContainer', FileUploader::decideMediaClassName('FileMaker_FX', $defs, 'f'));
            $this->assertSame('FileSystem', FileUploader::decideMediaClassName('PDO', $defs, 'f'));
        }
    }

    public function testDecideMediaClassNameRespectsTargetField(): void
    {
        $defs = [
            ['field' => 'photo', 'container' => 'S3'],
            ['field' => 'doc', 'container' => 'Dropbox'],
        ];
        $this->assertSame('AWSS3', FileUploader::decideMediaClassName('PDO', $defs, 'photo'));
        $this->assertSame('Dropbox', FileUploader::decideMediaClassName('PDO', $defs, 'doc'));
        $this->assertSame('FileSystem', FileUploader::decideMediaClassName('PDO', $defs, 'other'));
    }

    public function testDecideMediaClassNameWithoutFieldAppliesToAll(): void
    {
        $this->assertSame('AWSS3', FileUploader::decideMediaClassName('PDO', [['container' => 's3']], 'any'));
    }

    public function testProcessingWithoutFilesOutputsSingleJson(): void
    {
        $_POST = ['_im_contextname' => 'testtable', '_im_field' => 'vc1',
            '_im_keyfield' => 'id', '_im_keyvalue' => '1'];
        $_FILES = [];
        $uploader = new FileUploader();
        ob_start();
        try {
            $uploader->processing([['name' => 'testtable', 'key' => 'id']], null,
                ['db-class' => 'PDO', 'dsn' => 'sqlite::memory:'], 0);
        } finally {
            $output = ob_get_clean();
        }
        $json = json_decode($output, true);
        $this->assertIsArray($json, "Output must be a single JSON document: {$output}");
        $this->assertNotEmpty($json['errorMessages'] ?? []);
    }

    public function testGetResultForLogWithoutContextName(): void
    {
        $_POST = [];
        $uploader = new FileUploader();
        $this->assertIsArray($uploader->getResultForLog());
    }
}
