<?php
/*
 * Created by JetBrains PhpStorm.
 * User: msyk
 * Date: 11/12/14
 * Time: 14:21
 * Unit Test by PHPUnit (http://phpunit.de)
 *
 */

require_once('PHPUnit/Framework/TestCase.php');
require_once('../INTER-Mediator/DB_FileMaker_FX.php');

class DB_FMS_Test extends PHPUnit_Framework_TestCase
{
    function setUp()
    {
        mb_internal_encoding('UTF-8');
        date_default_timezone_set('Asia/Tokyo');

        $this->db_filemaker_fx = new DB_FileMaker_FX();
        $this->db_filemaker_fx->setDbSpecDSN('mysql:unix_socket=/tmp/mysql.sock;dbname=test_db;');
        $this->db_filemaker_fx->setDbSpecUser('web');
        $this->db_filemaker_fx->setDbSpecPassword('password');
        $this->db_filemaker_fx->setDbSpecPort('80');
        $this->db_filemaker_fx->setDbSpecDataType('FMPro7');
        $this->db_filemaker_fx->setDbSpecServer('127.0.0.1');
        $this->db_filemaker_fx->setDbSpecDatabase('TestDB');
        $this->db_filemaker_fx->setDbSpecProtocol('http');

        $this->db_filemaker_fx->authentication = array(  // table only, for all operations
            'user' => array ('user1'), // Itemize permitted users
            'group' => array('group2'), // Itemize permitted groups
            'privilege' => array(), // Itemize permitted privileges
            'user-table' => 'authuser', // Default values, or "_Native"
            'group-table' => 'authgroup',
            'corresponding-table' => 'authcor',
            'challenge-table' => 'issuedhash',
            'authexpired' => '300',  // Set as seconds.
            'storing' => 'cookie-domainwide',   // 'cookie'(default), 'cookie-domainwide', 'none'
        );
    }

    public function testAuthUser1()
    {
        $testName = "Check time calc feature of PHP";
        $expiredDT = new DateTime('2012-02-13 11:32:40');
        $currentDate = new DateTime('2012-02-14 11:32:51');
        //    $expiredDT = new DateTime('2012-02-13 00:00:00');
        //    $currentDate = new DateTime('2013-04-13 01:02:03');
        $intervalDT = $expiredDT->diff($currentDate, true);
        var_export($intervalDT);
        $calc = (( $intervalDT->days * 24 + $intervalDT->h ) * 60 + $intervalDT->i ) * 60 + $intervalDT->s;
        echo $calc;
        $this->assertTrue ( $calc === (11+3600*24), $testName );
    }
    public function testAuthUser2()
    {
        $testName = "Password Retrieving";
        $username = 'user1';
        $expectedPasswd = 'd83eefa0a9bd7190c94e7911688503737a99db0154455354';

        $retrievedPasswd = $this->db_filemaker_fx->authSupportRetrieveHashedPassword($username);
        echo var_export( $this->db_filemaker_fx->debugMessage, true );
        $this->assertEquals($expectedPasswd, $retrievedPasswd, $testName);

    }
    public function testAuthUser3()
    {
        $testName = "Salt retrieving";
        $username = 'user1';
        $retrievedSalt = $this->db_filemaker_fx->authSupportGetSalt($username);
        $this->assertEquals('54455354', $retrievedSalt, $testName);

    }
    public function testAuthUser4()
    {
        $testName = "Generate Challenge and Retrieve it";
        $username = 'user1';
        $challenge = $this->db_filemaker_fx->generateChallenge();
        $this->db_filemaker_fx->authSupportStoreChallenge($username, $challenge, "TEST");
        $this->assertEquals($challenge, $this->db_filemaker_fx->authSupportRetrieveChallenge($username, "TEST"), $testName);
        $challenge = $this->db_filemaker_fx->generateChallenge();
        $this->db_filemaker_fx->authSupportStoreChallenge($username, $challenge, "TEST");
        $this->assertEquals($challenge, $this->db_filemaker_fx->authSupportRetrieveChallenge($username, "TEST"), $testName);
        $challenge = $this->db_filemaker_fx->generateChallenge();
        $this->db_filemaker_fx->authSupportStoreChallenge($username, $challenge, "TEST");
        $this->assertEquals($challenge, $this->db_filemaker_fx->authSupportRetrieveChallenge($username, "TEST"), $testName);

    }

    public function testAuthUser5()
    {
        $testName = "Simulation of Authentication";
        $username = 'user1';
        $password = 'user1';

        $challenge = $this->db_filemaker_fx->generateChallenge();
        $this->db_filemaker_fx->authSupportStoreChallenge($username, $challenge, "TEST");

    //    $challenge = $this->db_filemaker_fx->authSupportRetrieveChallenge($username, $testName);
        $retrievedHexSalt = $this->db_filemaker_fx->authSupportGetSalt($username);
        $retrievedSalt = pack( 'N', hexdec( $retrievedHexSalt ));

        $hashedvalue = sha1( $password . $retrievedSalt) . bin2hex( $retrievedSalt );
        $calcuratedHash = sha1($challenge . $hashedvalue);
        $this->assertTrue(
            $this->db_filemaker_fx->checkAuthorization( $username, $calcuratedHash, "TEST" ), $testName);
    }

    public function testAuthUser6()
    {
        $testName = "Create New User and Authenticate";
        $username = "testuser";
        $password = "testuser";
    //    $this->assertTrue($this->db_filemaker_fx->addUser( $username, $password ));

        $retrievedHexSalt = $this->db_filemaker_fx->authSupportGetSalt($username);
        $retrievedSalt = pack( 'N', hexdec( $retrievedHexSalt ));

        $clientId = "TEST";
        $challenge = $this->db_filemaker_fx->generateChallenge();
        $this->db_filemaker_fx->saveChallenge( $username, $challenge, $clientId );

        $hashedvalue = sha1( $password . $retrievedSalt) . bin2hex( $retrievedSalt );
        echo $hashedvalue;

        $this->assertTrue(
            $this->db_filemaker_fx->checkAuthorization( $username, sha1($challenge . $hashedvalue), $clientId ),
            $testName);
    }

    function testUserGroup()    {
        $testName = "Resolve containing group";
        $groupArray = $this->db_filemaker_fx->getGroupsOfUser('user1');
        echo var_export($groupArray);
        $this->assertTrue(count($groupArray)>0, $testName);
    }

    public function testNativeUser()
    {
        $testName = "Native User Challenge Check";
        $cliendId = "12345";

        $challenge = $this->db_filemaker_fx->generateChallenge();
        echo "\ngenerated=",$challenge;
        $this->db_filemaker_fx->authSupportStoreChallenge(0, $challenge, $cliendId);

        $this->assertTrue(
            $this->db_filemaker_fx->checkChallenge( $challenge, $cliendId ), $testName);
    }


}