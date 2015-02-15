<?php
/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 *
 *   Copyright (c) 2010-2015 INTER-Mediator Directive Committee, All rights reserved.
 *
 *   This project started at the end of 2009 by Masayuki Nii  msyk@msyk.net.
 *   INTER-Mediator is supplied under MIT License.
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
            // Three definitions below will NOT be specified simultaneously. Here is a demo. Try with commented any lines.
            'post-reconstruct' => true,
            'post-dismiss-message' => '送信したんだもーん',
        //    'post-move-url' => 'http://inter-mediator.org/',
            'extending-class' => "MailSending",
        ),
    ),
    array(
        'formatter' => array(
            array('field' => 'chat@postdt', 'converter-class' => 'MySQLDateTime'),
        ),
    ),
    array('db-class' => 'PDO'),
    false
);
