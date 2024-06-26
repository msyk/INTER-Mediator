<?php
/*
 * Created by JetBrains PhpStorm.
 * User: msyk
 * Date: 11/12/14
 * Time: 14:21
 * Unit Test by PHPUnit (http://phpunit.de)
 *
 */

namespace deprecated;
require_once('../DB-PDO/DB_PDO_Test_Common.php');

use DB_PDO_Test_Common;
use INTERMediator\DB\Proxy;

class DB_PDO_SQLServer_Test extends DB_PDO_Test_Common
{
    public string $dsn;

    function setUp(): void
    {
        mb_internal_encoding('UTF-8');
        date_default_timezone_set('Asia/Tokyo');

        $this->dsn = 'sqlsrv:server=localhost;database=test_db';
        if (getenv('TRAVIS') === 'true') {
            $this->dsn = 'sqlsrv:database=test_db;server=localhost';
        } else if (file_exists('/etc/alpine-release')) {
            $this->dsn = 'sqlsrv:server=localhost;database=test_db';
        }
    }

    function dbProxySetupForAccess(string $contextName, int $maxRecord, ?string $subContextName = null):void
    {
        $this->schemaName = "";
        $contexts = array(
            array(
                'records' => $maxRecord,
                'name' => $contextName,
                'view' => $contextName,
                'table' => $contextName,
                'key' => 'id',
                'repeat-control' => is_null($subContextName) ? 'copy' : "copy-{$subContextName}",
                'sort' => array(
                    array('field' => 'id', 'direction' => 'asc'),
                ),
            )
        );
        if (!is_null($subContextName)) {
            $contexts[] = array(
                'records' => $maxRecord,
                'name' => $subContextName,
                'view' => $subContextName,
                'table' => $subContextName,
                'key' => 'id',
                'relation' => array(
                    "foreign-key" => "{$contextName}_id",
                    "join-field" => "id",
                    "operator" => "=",
                ),
            );
        }
        $options = null;
        $dbSettings = array(
            'db-class' => 'PDO',
            'dsn' => $this->dsn,
            'user' => 'web',
            'password' => 'password',
        );
        $this->db_proxy = new Proxy(true);
        $this->db_proxy->initialize($contexts, $options, $dbSettings, 2, $contextName);
    }

    function dbProxySetupForAuth():void
    {
        $this->db_proxy = new Proxy(true);
        $this->db_proxy->initialize(array(
            array(
                'records' => 1000,
                'paging' => true,
                'name' => 'person',
                'key' => 'id',
                'query' => array( /* array( 'field'=>'id', 'value'=>'5', 'operator'=>'eq' ),*/),
                'sort' => array(array('field' => 'id', 'direction' => 'asc'),),
                'sequence' => 'im_sample.serial',
            )
        ),
            array(
                'authentication' => array( // table only, for all operations
                    'user' => array('user1'), // Itemize permitted users
                    'group' => array('group2'), // gropu2 contain user4 and user5
                    'user-table' => 'authuser', // Default value
                    'group-table' => 'authgroup',
                    'corresponding-table' => 'authcor',
                    'challenge-table' => 'issuedhash',
                    'authexpired' => '300', // Set as seconds.
                    'storing' => 'credential', // 'cookie'(default), 'cookie-domainwide', 'none'
                    'is-required-2FA' => false,
                ),
            ),
            array(
                'db-class' => 'PDO',
                'dsn' => $this->dsn,
                'user' => 'web',
                'password' => 'password',
            ),
            2
        );
    }

    function dbProxySetupForAggregation():void
    {
        $this->db_proxy = new Proxy(true);
        $this->db_proxy->initialize(
            array(
                array(
                    'name' => 'summary',
                    'view' => 'saleslog',
                    'query' => array(
                        array('field' => 'dt', 'operator' => '>=', 'value' => '2010-01-01',),
                        array('field' => 'dt', 'operator' => '<', 'value' => '2010-02-01',),
                    ),
                    'sort' => array(
                        array('field' => 'total', 'direction' => 'desc'),
                    ),
                    'records' => 10,
                    'aggregation-select' => "item_master.name as item_name,sum(total) as total",
                    'aggregation-from' => "saleslog inner join item_master on saleslog.item_id=item_master.id",
                    'aggregation-group-by' => "item_id, item_master.name",
                ),
            ),
            null,
            array(
                'db-class' => 'PDO',
                'dsn' => $this->dsn,
                'user' => 'web',
                'password' => 'password',
            ),
            2,
            "summary"
        );
    }

    function dbProxySetupForCondition(?array $queryArray):void
    {
        $this->schemaName = "";
        $contextName = 'testtable';
        $contexts = array(
            array(
                'records' => 10000000,
                'name' => $contextName,
                'key' => 'id',
            )
        );
        if (!is_null($queryArray)) {
            $contexts[0]['query'] = $queryArray;
        }
        $options = null;
        $dbSettings = array(
            'db-class' => 'PDO',
            'dsn' => $this->dsn,
            'user' => 'web',
            'password' => 'password',
        );
        $this->db_proxy = new Proxy(true);
        $this->db_proxy->initialize($contexts, $options, $dbSettings, 2, $contextName);
    }

    protected function getSampleComdition()
    {
        return "WHERE id=1001 ORDER BY xdate OFFSET 0 ROWS FETCH NEXT 10 ROWS ONLY";;
    }

    protected string $sqlSETClause1 = "(\"num1\",\"num2\",\"date1\",\"date2\",\"time1\",\"time2\",\"dt1\",\"dt2\",\"vc1\",\"vc2\",\"text1\",\"text2\") "
    . "VALUES(100,200,'2022-04-01','2022-04-01','10:21:31','10:21:31','2022-04-01 10:21:31','2022-04-01 10:21:31','TEST','TEST','TEST','TEST')";
    protected string $sqlSETClause2 = "(\"num1\",\"num2\",\"date1\",\"date2\",\"time1\",\"time2\",\"dt1\",\"dt2\",\"vc1\",\"vc2\",\"text1\",\"text2\") "
    . "VALUES(0,NULL,'',NULL,'',NULL,'',NULL,'',NULL,'',NULL)";
    protected string $sqlSETClause3 = "(\"num1\",\"num2\",\"date1\",\"date2\",\"time1\",\"time2\",\"dt1\",\"dt2\",\"vc1\",\"vc2\",\"text1\",\"text2\") "
    . "VALUES(0,0,'','','','','','','','','','')";

    function dbProxySetupForAccessSetKey(string $contextName, int $maxRecord, string $keyName): void
    {
        // TODO: Implement dbProxySetupForAccessSetKey() method.
    }
}