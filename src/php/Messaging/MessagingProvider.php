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

namespace INTERMediator\Messaging;

use INTERMediator\DB\Logger;
use INTERMediator\DB\Proxy;
use INTERMediator\IMUtil;

/**
 * Interface MessagingProvider
 * @package INTERMediator\Messaging
 */
abstract class MessagingProvider
{
    /**
     * @param $dbProxy Proxy class's instance.
     * @param $contextDef array The context definition array of current context.
     * @param $result array The result of query or other db operations.
     * @return bool for warning messsage, no messege error or succed return null.
     */
    public abstract function processing(Proxy $dbProxy, array $contextDef, array $result): bool;

    protected function setWarningMessage(int $num, string $message): void
    {
        $messageClass = IMUtil::getMessageClassInstance();
        $headMsg = $messageClass->getMessageAs($num);
        $logger = Logger::getInstance();
        $logger->setWarningMessage("{$headMsg} {$message}");
    }

    public function modernTemplating(array $record, ?string $tempStr, bool $ignoreField = false): string
    {
        $bodyStr = $tempStr ?? "";
        if (!$ignoreField && isset($record[$tempStr])) {
            $bodyStr = $record[$tempStr];
        }
        if (strlen($bodyStr) > 5) {
            $startPos = strpos($bodyStr, '@@', 0);
            $endPos = strpos($bodyStr, '@@', $startPos + 2);
            while ($startPos !== false && $endPos !== false) {
                $fieldName = trim(substr($bodyStr, $startPos + 2, $endPos - $startPos - 2));
                $bodyStr = substr($bodyStr, 0, $startPos)
                    . ($record[$fieldName] ?? '') . substr($bodyStr, $endPos + 2);
                $startPos = strpos($bodyStr, '@@');
                if (strlen($bodyStr) <= ($startPos + 2)) {
                    $endPos = false;
                } else {
                    $endPos = strpos($bodyStr, '@@', $startPos + 2);
                }
            }
        }
        return $bodyStr;
    }
}
