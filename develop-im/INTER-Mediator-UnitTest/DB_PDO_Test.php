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
require_once('../INTER-Mediator/DB_PDO.php');

class DB_PDO_Test extends PHPUnit_Framework_TestCase
{
    function setUp()
    {
        mb_internal_encoding('UTF-8');
        date_default_timezone_set('Asia/Tokyo');

        $this->db_pdo = new DB_PDO();
        $this->db_pdo->setDbSpecDSN('mysql:unix_socket=/tmp/mysql.sock;dbname=test_db;');
        $this->db_pdo->setDbSpecUser('web');
        $this->db_pdo->setDbSpecPassword('password');
    }

    public function testAuthUser()
    {
        $expiredDT = new DateTime('2012-02-13 11:32:40');
        $currentDate = new DateTime('2012-02-14 11:32:51');
        //    $expiredDT = new DateTime('2012-02-13 00:00:00');
        //    $currentDate = new DateTime('2013-04-13 01:02:03');
        $intervalDT = $expiredDT->diff($currentDate, true);
        var_export($intervalDT);
        $calc = (( $intervalDT->days * 24 + $intervalDT->h ) * 60 + $intervalDT->i ) * 60 + $intervalDT->s;
        echo $calc;
        $this->assertTrue ( $calc === (11+3600*24) );

        $username = 'user1';
        $expectedPasswd = 'd83eefa0a9bd7190c94e7911688503737a99db0154455354';

        $retrievedPasswd = $this->db_pdo->authSupportRetrieveHashedPassword($username);
        $this->assertEquals($expectedPasswd, $retrievedPasswd);

        $retrievedSalt = $this->db_pdo->authSupportGetSalt($username);
        $this->assertEquals('54455354', $retrievedSalt);

        $challenge = $this->db_pdo->generateChallenge();
        $this->db_pdo->authSupportStoreChallenge($username, $challenge, "TEST");
        $this->assertEquals($challenge, $this->db_pdo->authSupportRetrieveChallenge($username, "TEST"), "TEST");

        $username = "testuser";
        $password = "testuser";
        //    $this->assertTrue($this->db_pdo->addUser( $username, $password ));

        $retrievedHexSalt = $this->db_pdo->authSupportGetSalt($username);
        $retrievedSalt = pack( 'N', hexdec( $retrievedHexSalt ));

        $clientId = "TEST";
        $challenge = $this->db_pdo->generateChallenge();
        $this->db_pdo->saveChallenge( $username, $challenge, $clientId );

        $hashedvalue = sha1( $password . $retrievedSalt) . bin2hex( $retrievedSalt );
        echo $hashedvalue;

        $this->assertTrue($this->db_pdo->checkChallenge( $username, sha1($challenge . $hashedvalue), $clientId ));
    }

    function testUserGroup()    {
        $groupArray = $this->db_pdo->getGroupsOfUser('user1');
        echo var_export($groupArray);
        $this->assertTrue(count($groupArray)>0);
    }
}