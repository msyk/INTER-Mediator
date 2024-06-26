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
                array('field' => 'postdt', 'direction' => 'descend'),
            ),
            'default-values' => array(
                array('field' => 'postdt', 'value' => date("m/d/Y H:i:s")),
            ),
            'authentication' => array(
                'all' => array( // load, update, new, delete
                    'target' => 'field-user',
                    'field' => 'user',
                ),
            ),
        ),
    ),
    array(
        'authentication' => array( // table only, for all operations
            'user' => array('user1'), // Itemize permitted users
            'group' => array('group2'), // Itemize permitted groups
            'user-table' => 'authuser', // Default value
            'group-table' => 'authgroup',
            'corresponding-table' => 'authcor',
            'challenge-table' => 'issuedhash',
            'authexpired' => '300', // Set as seconds.
            'storing' => 'credential',
            'is-required-2FA' => false,
        ),
    ),
    array('db-class' => 'FileMaker_FX'),
    false
);
