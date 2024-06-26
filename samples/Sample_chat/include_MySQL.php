<?php
/**
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 *
 * @copyright     Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * @link          https://inter-mediator.com/
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

require_once(dirname(__FILE__) . '/../../INTER-Mediator.php');

IM_Entry(
    array(
        array(
            'records' => 100000000,
            'name' => 'chat',
            'key' => 'id',
            'sort' => array(
                array('field' => 'postdt', 'direction' => 'desc'),
            ),
            'default-values' => array(
                array('field' => 'postdt', 'value' => date("Y-m-d H:i:s")),
            ),
            'authentication' => array(
                'all' => array( // load, update, new, delete
                    'target' => 'field-user',
                    'field' => 'user',
                ),
            ),
//            'messaging' => [
//                'driver' => 'mail',
//                'create' => [
//                    'from-constant' => 'msyk@msyk.net',
//                    'to-constant' => 'msyk@msyk.net',
//                    'subject-constant' => 'Mail From INTER-Mediator',
//                    'body-constant' => 'INTER-Mediator Sample.',
//                ]
//            ],
//            'messaging' => [
//                'driver' => 'mail',
//                'read' => [
//                    'from' => 'msyk@msyk.net',
//                    'to' => 'message',
//                    'cc' => 'msyk@msyk.net',
//                    'subject' => 'Mail From INTER-Mediator',
//                    'body' => 'INTER-Mediator Sample.',
//                ]
//            ],
//            'messaging' => [
//                'driver' => 'slack',
//                'create' => [
//                    'subject-constant' => 'message-posting-test',
//                    'body' => 'message',
//                ]
//            ]
        ),
        array(
            'records' => 100000000,
            'name' => 'chat-send',
            'view' => 'chat',
            'key' => 'id',
            'sort' => array(
                array('field' => 'postdt', 'direction' => 'desc'),
            ),
            'default-values' => array(
                array('field' => 'postdt', 'value' => date("Y-m-d H:i:s")),
            ),
            'authentication' => array(
                'all' => array( // load, update, new, delete
                    'target' => 'field-user',
                    'field' => 'user',
                ),
            ),
            'messaging' => [
                'driver' => 'mail',
                'read' => [
                    'from' => 'msyk@msyk.net',
                    'to' => '@@message@@',
                    'cc' => 'msyk@msyk.net',
                    'subject' => 'Mail From INTER-Mediator',
                    'body' => 'INTER-Mediator Sample.',
                ]
            ],
        ),
    ),
    array(
        'authentication' => array( // table only, for all operations
            'user' => array('user1', 'mig2'), // Itemize permitted users
            'group' => array('group2'), // Itemize permitted groups
            'user-table' => 'authuser', // Default value
            'group-table' => 'authgroup',
            'corresponding-table' => 'authcor',
            'challenge-table' => 'issuedhash',
            'authexpired' => '1000', // Set as seconds.
            'storing' => 'credential', // session-storage, 'cookie'(default), 'cookie-domainwide', 'none'
            'is-required-2FA' => false,
        ),
    ),
    array('db-class' => 'PDO'),
    2
);
