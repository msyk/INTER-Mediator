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

namespace INTERMediator;

class GenerateJSCode
{
    public function __construct()
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        header('Content-Type: text/javascript;charset="UTF-8"');
        header('Cache-Control: no-store,no-cache,must-revalidate,post-check=0,pre-check=0');
        header('Expires: 0');
        $util = new IMUtil();
        $util->outputSecurityHeaders();
    }

    public function generateAssignJS($variable, $value1, $value2 = '', $value3 = '', $value4 = '', $value5 = '')
    {
        echo "{$variable}={$value1}{$value2}{$value3}{$value4}{$value5};\n";
    }

    public function generateDebugMessageJS($message)
    {
        $q = '"';
        echo "INTERMediatorLog.setDebugMessage({$q}"
            . str_replace("\n", " ", addslashes($message) ?? "") . "{$q});\n";
    }

    public function generateErrorMessageJS($message)
    {
        $q = '"';
        echo "INTERMediatorLog.setErrorMessage({$q}"
            . str_replace("\n", " ", addslashes($message) ?? "") . "{$q});";
    }

    public function generateInitialJSCode($datasource, $options, $dbspecification, $debug)
    {
        $q = '"';
        $ds = DIRECTORY_SEPARATOR;

        $browserCompatibility = Params::getParameterValue("browserCompatibility", null);
        $callURL = Params::getParameterValue("callURL", null);
        $scriptPathPrefix = Params::getParameterValue("scriptPathPrefix", null);
        $scriptPathSuffix = Params::getParameterValue("scriptPathSuffix", null);
        $oAuthProvider = Params::getParameterValue("oAuthProvider", null);
        $oAuthClientID = Params::getParameterValue("oAuthClientID", null);
        $oAuthRedirect = Params::getParameterValue("oAuthRedirect", null);
        $passwordPolicy = Params::getParameterValue("passwordPolicy", null);
        $dbClass = Params::getParameterValue("dbClass", null);
        $dbDSN = Params::getParameterValue("dbDSN", '');
        $nonSupportMessageId = Params::getParameterValue("nonSupportMessageId", null);
        $valuesForLocalContext = Params::getParameterValue("valuesForLocalContext", null);
        $themeName = Params::getParameterValue("themeName", 'default');
        $appLocale = Params::getParameterValue("appLocale", 'ja_JP');
        $appCurrency = Params::getParameterValue("appCurrency", 'JP');
        $resetPage = Params::getParameterValue("resetPage", null);
        $enrollPage = Params::getParameterValue("enrollPage", null);
        $serviceServerPort = Params::getParameterValue("serviceServerPort", "11478");
        $serviceServerHost = Params::getParameterValue("serviceServerHost", null);
        $serviceServerProtocol = Params::getParameterValue("serviceServerProtocol", 'ws');
        $notUseServiceServer = Params::getParameterValue("notUseServiceServer", null);
        $activateClientService = Params::getParameterValue("activateClientService", null);
        $followingTimezones = Params::getParameterValue("followingTimezones", null);
        $passwordHash = Params::getParameterValue("passwordHash", 1);
        $alwaysGenSHA2 = Params::getParameterValue("alwaysGenSHA2", null);
        $isSAML = Params::getParameterValue("isSAML", null);
        $samlWithBuiltInAuth = Params::getParameterValue("samlWithBuiltInAuth", null);
        $credentialCookieDomain = Params::getParameterValue('credentialCookieDomain', NULL);

        $resetPage = $options['authentication']['reset-page'] ?? $resetPage ?? null;
        $enrollPage = $options['authentication']['enroll-page'] ?? $enrollPage ?? null;
        $serviceServerHost = $serviceServerHost ?? $_SERVER['SERVER_ADDR'] ?? false;
        $serviceServerHost = $serviceServerHost ?? parse_url($_SERVER['HTTP_HOST'], PHP_URL_HOST) ?? false;
        $serviceServerHost = $serviceServerHost ?? 'localhost';
        $passwordHash = ($passwordHash === '2m') ? 1.5 : floatval($passwordHash);
        $isSAML = $options['authentication']['is-saml'] ?? $isSAML ?? false;
        $samlWithBuiltInAuth = $options['authentication']['saml-builtin-auth'] ?? $samlWithBuiltInAuth ?? false;

        $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? 'Not_on_web_server';

        $hasSyncControl = false;
        foreach ($datasource as $contextDef) {
            if (isset($contextDef['sync-control'])) {
                $hasSyncControl = true;
                break;
            }
        }

        $pathToIM = IMUtil::pathToINTERMediator();
        /*
              * Read the JS programs regarding by the developing or deployed.
              */
        $currentDir = "{$pathToIM}{$ds}src{$ds}js{$ds}";
        if (!file_exists($currentDir . 'INTER-Mediator.min.js')) {
            echo $this->combineScripts($activateClientService && $hasSyncControl);
        } else {
            readfile($currentDir . 'INTER-Mediator.min.js');
        }

        /*
         * Generate the link to the definition file editor
         */
        $relativeToDefFile = '';
        $editorPath = realpath($pathToIM . $ds . 'editors');
        if ($editorPath) { // In case of core only build.
            $defFilePath = realpath($documentRoot . $_SERVER['SCRIPT_NAME']);
            while (strpos($defFilePath, $editorPath) !== 0 && strlen($editorPath) > 1) {
                $editorPath = dirname($editorPath);
                $relativeToDefFile .= '..' . $ds;
            }
            $relativeToDefFile .= substr($defFilePath, strlen($editorPath) + 1);
            $editorPath = $pathToIM . $ds . 'editors' . $ds . 'defedit.html';
        } else {
            $editorPath = "Editors don't exist.";
        }
        if (file_exists($editorPath)) {
            $relativeToEditor = substr($editorPath, strlen($_SERVER['DOCUMENT_ROOT']));
            $this->generateAssignJS("INTERMediatorOnPage.getEditorPath",
                "function(){return {$q}{$relativeToEditor}?target=$relativeToDefFile{$q};}");
        } else {
            $this->generateAssignJS("INTERMediatorOnPage.getEditorPath",
                "function(){return '';}");
        }
        $relativeToIM = substr($pathToIM, strlen($_SERVER['DOCUMENT_ROOT']));
        $this->generateAssignJS("INTERMediatorOnPage.getPathToIMRoot",
            "function(){return {$q}{$relativeToIM}{$q};}");
        /*
         * from db-class, determine the default key field string
         */
        $defaultKey = null;
        $classBaseName = $dbspecification['db-class'] ?? $dbClass ?? '';
        $dbClassName = 'INTERMediator\\DB\\' . $classBaseName;
        $dbInstance = new $dbClassName();
        $dbInstance->setupHandlers($dbDSN);
        if ($dbInstance != null && $dbInstance->specHandler != null) {
            $defaultKey = $dbInstance->specHandler->getDefaultKey();
        }
        if ($defaultKey !== null) {
            $items = array();
            foreach ($datasource as $context) {
                if (!array_key_exists('key', $context)) {
                    $context['key'] = $defaultKey;
                }
                $items[] = $context;
            }
            $datasource = $items;
        }

        /*
         * Determine the uri of myself
         */
        if (isset($callURL)) {
            $pathToMySelf = $callURL;
        } else if (isset($scriptPathPrefix) || isset($scriptPathSuffix)) {
            $pathToMySelf = ($scriptPathPrefix ?? '')
                . ($_SERVER['SCRIPT_NAME'] ?? null) . (isset($scriptPathSufix) ? $scriptPathSuffix : '');
        } else {
            $pathToMySelf = IMUtil::relativePath(
                parse_url($_SERVER['HTTP_REFERER'] ?? null, PHP_URL_PATH), $_SERVER['SCRIPT_NAME'] ?? null);
        }
        $qStr = isset($_SERVER['QUERY_STRING']) ? "?{$_SERVER['QUERY_STRING']}" : '';

        if($qStr == '?') {
            $qStr = '';
        }

        $this->generateAssignJS(
            "INTERMediatorOnPage.getEntryPath", "function(){return {$q}{$pathToMySelf}{$qStr}{$q};}");
        $this->generateAssignJS(
            "INTERMediatorOnPage.getTheme", "function(){return {$q}",
            $options['theme'] ?? $themeName, "{$q};}");
        $this->generateAssignJS(
            "INTERMediatorOnPage.getDataSources", "function(){return ",
            IMUtil::arrayToJSExcluding($datasource, '', array('password')), ";}");
        $this->generateAssignJS(
            "INTERMediatorOnPage.getOptionsAliases",
            "function(){return ", IMUtil::arrayToJS($options['aliases'] ?? array()), ";}");
        $this->generateAssignJS(
            "INTERMediatorOnPage.getOptionsTransaction",
            "function(){return ", IMUtil::arrayToJS($options['transaction'] ?? ''), ";}");
        $this->generateAssignJS("INTERMediatorOnPage.dbClassName", "{$q}{$dbClassName}{$q}");
        $this->generateAssignJS("INTERMediatorOnPage.defaultKeyName", "{$q}{$defaultKey}{$q}");

        $isEmailAsUsernae = isset($options['authentication'])
            && isset($options['authentication']['email-as-username'])
            && $options['authentication']['email-as-username'] === true;
        $this->generateAssignJS(
            "INTERMediatorOnPage.isEmailAsUsername", $isEmailAsUsernae ? "true" : "false");

        $messageClass = IMUtil::getMessageClassInstance();
        $this->generateAssignJS(
            "INTERMediatorOnPage.getMessages",
            "function(){return ", IMUtil::arrayToJS($messageClass->getMessages()), ";}");
        $terms = $messageClass->getTerms($options);
        $this->generateAssignJS(
            "INTERMediatorOnPage.getTerms",
            "function(){return ", (count($terms) > 0) ? IMUtil::arrayToJS($terms) : "null", ";}");

        if (isset($options['browser-compatibility'])) {
            $browserCompatibility = $options['browser-compatibility'];
        }
        foreach ($browserCompatibility as $browser => $browserInfo) {
            if (strtolower($browser) !== $browser) {
                $browserCompatibility[strtolower($browser)] = $browserInfo;
                unset($browserCompatibility[$browser]);
            }
        }
        $this->generateAssignJS(
            "INTERMediatorOnPage.browserCompatibility",
            "function(){return ", IMUtil::arrayToJS($browserCompatibility), ";}");

        $remoteAddr = $_SERVER['REMOTE_ADDR'];
        if (is_null($remoteAddr) || $remoteAddr === FALSE) {
            $remoteAddr = '0.0.0.0';
        }
        $clientIdSeed = time() . $remoteAddr . mt_rand();
        $randomSecret = mt_rand();
        $clientId = hash_hmac('sha256', $clientIdSeed, $randomSecret);

        $this->generateAssignJS(
            "INTERMediatorOnPage.clientNotificationIdentifier",
            "function(){return ", IMUtil::arrayToJS($clientId), ";}");

        if ($nonSupportMessageId != "") {
            $this->generateAssignJS(
                "INTERMediatorOnPage.nonSupportMessageId",
                "{$q}{$nonSupportMessageId}{$q}");
        }
        $metadata = json_decode(file_get_contents($pathToIM . $ds . "composer.json"));
        $this->generateAssignJS("INTERMediatorOnPage.metadata",
            "{version:{$q}{$metadata->version}{$q},releasedate:{$q}{$metadata->time}{$q}}");

        if (isset($prohibitDebugMode) && $prohibitDebugMode) {
            $this->generateAssignJS("INTERMediatorLog.debugMode", "false");
        } else {
            $this->generateAssignJS(
                "INTERMediatorLog.debugMode", ($debug === false) ? "false" : $debug);
        }

        if (!is_null($appLocale)) {
            $this->generateAssignJS("INTERMediatorOnPage.appLocale", "{$q}{$appLocale}{$q}");
            $this->generateAssignJS("INTERMediatorLocale",
                "JSON.parse('" . json_encode(Locale\IMLocaleFormatTable::getCurrentLocaleFormat()) . "')");
        }
        if (!is_null($appCurrency)) {
            $this->generateAssignJS("INTERMediatorOnPage.appCurrency", "{$q}{$appCurrency}{$q}");
        }

        // Check Authentication
        $boolValue = "false";
        $requireAuthenticationContext = array();
        if (isset($options['authentication'])) {
            $boolValue = "true";
        }
        foreach ($datasource as $aContext) {
            if (isset($aContext['authentication'])) {
                $boolValue = "true";
                $requireAuthenticationContext[] = $aContext['name'];
            }
        }
        $this->generateAssignJS(
            "INTERMediatorOnPage.requireAuthentication", $boolValue);
        $this->generateAssignJS(
            "INTERMediatorOnPage.credentialCookieDomain", $q, ($credentialCookieDomain ?? ''), $q);
        $this->generateAssignJS(
            "INTERMediatorOnPage.authRequiredContext", IMUtil::arrayToJS($requireAuthenticationContext));
        if (!is_null($enrollPage)) {
            $this->generateAssignJS("INTERMediatorOnPage.enrollPageURL", $q, $enrollPage, $q);
        }
        if (!is_null($resetPage)) {
            $this->generateAssignJS("INTERMediatorOnPage.resetPageURL", $q, $resetPage, $q);
        }

        $this->generateAssignJS(
            "INTERMediatorOnPage.isOAuthAvailable", isset($oAuthProvider) ? "true" : "false");
        $authObj = new OAuthAuth();
        if ($authObj->isActive) {
            $this->generateAssignJS("INTERMediatorOnPage.oAuthClientID",
                $q, $oAuthClientID, $q);
            $this->generateAssignJS("INTERMediatorOnPage.oAuthBaseURL",
                $q, $authObj->oAuthBaseURL(), $q);
            $this->generateAssignJS("INTERMediatorOnPage.oAuthRedirect",
                $q, $oAuthRedirect, $q);
            $this->generateAssignJS("INTERMediatorOnPage.oAuthScope",
                $q, implode(' ', $authObj->infoScope()), $q);
        }
        $this->generateAssignJS(
            "INTERMediatorOnPage.authStoring",
            $q, (isset($options['authentication']) && isset($options['authentication']['storing'])) ?
            $options['authentication']['storing'] : 'cookie', $q);
        $this->generateAssignJS(
            "INTERMediatorOnPage.authExpired",
            (isset($options['authentication']) && isset($options['authentication']['authexpired'])) ?
                $options['authentication']['authexpired'] : '3600');
        $this->generateAssignJS(
            "INTERMediatorOnPage.realm", $q,
            (isset($options['authentication']) && isset($options['authentication']['realm'])) ?
                $options['authentication']['realm'] : '', $q);
        if (isset($passwordPolicy)) {
            $this->generateAssignJS(
                "INTERMediatorOnPage.passwordPolicy", $q, $passwordPolicy, $q);
        } else if (isset($options["authentication"])
            && isset($options["authentication"]["password-policy"])
        ) {
            $this->generateAssignJS(
                "INTERMediatorOnPage.passwordPolicy", $q, $options["authentication"]["password-policy"], $q);
        }
        if (isset($options['credit-including'])) {
            $this->generateAssignJS(
                "INTERMediatorOnPage.creditIncluding", $q, $options['credit-including'], $q);
        }
        $this->generateAssignJS(
            "INTERMediatorOnPage.isSAML", $isSAML ? 'true' : 'false');
        $this->generateAssignJS(
            "INTERMediatorOnPage.samlWithBuiltInAuth", $samlWithBuiltInAuth ? 'true' : 'false');

        // Initial values for local context
        if (!isset($valuesForLocalContext)) {
            $valuesForLocalContext = array();
        }
        if (isset($options['local-context'])) {
            foreach ($options['local-context'] as $item) {
                $valuesForLocalContext[$item['key']] = $item['value'];
            }
        }
        if (isset($valuesForLocalContext) && is_array($valuesForLocalContext) && count($valuesForLocalContext) > 0) {
            $this->generateAssignJS("INTERMediatorOnPage.initLocalContext", IMUtil::arrayToJS($valuesForLocalContext));
        }
        $sss = ServiceServerProxy::instance()->isActive();
        $this->generateAssignJS("INTERMediatorOnPage.serviceServerStatus", $sss ? "true" : "false");

        $this->generateAssignJS("INTERMediatorOnPage.activateClientService",
            ($activateClientService && $hasSyncControl && !$notUseServiceServer) ? "true" : "false");
        $this->generateAssignJS("INTERMediatorOnPage.useServiceServer",
            !$notUseServiceServer ? "true" : "false");
        $this->generateAssignJS("INTERMediatorOnPage.serviceServerURL",
            "{$q}{$serviceServerProtocol}://{$serviceServerHost}:{$serviceServerPort}{$q}");
        $this->generateAssignJS("INTERMediatorOnPage.serverDefaultTimezone", $q, date_default_timezone_get(), $q);
        $this->generateAssignJS("INTERMediatorOnPage.isFollowingTimezone", $followingTimezones ? "true" : "false");
        $this->generateAssignJS("INTERMediatorOnPage.passwordHash", $passwordHash);
        $this->generateAssignJS("INTERMediatorOnPage.alwaysGenSHA2", $alwaysGenSHA2 ? "true" : "false");
    }

    private function combineScripts($isSocketIO): string
    {
        $imPath = IMUtil::pathToINTERMediator();
        $jsCodeDir = $imPath . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR;
        $nodeModuleDir = $imPath . DIRECTORY_SEPARATOR . 'node_modules' . DIRECTORY_SEPARATOR;
        $content = '';
        // $content .= $this->readJSSource($nodeModuleDir . 'jsencrypt/bin/jsencrypt.js');
        $content .= $this->readJSSource($nodeModuleDir . 'jssha/dist/sha.js');
        if ($isSocketIO) {
            $content .= $this->readJSSource($nodeModuleDir . 'socket.io-client/dist/socket.io.js');
        }
        $content .= "\n";
        $content .= $this->readJSSource($nodeModuleDir . 'inter-mediator-formatter/index.js');
        //$content .= $this->readJSSource($nodeModuleDir . 'inter-mediator-locale/index.js');
        $content .= $this->readJSSource($nodeModuleDir . 'inter-mediator-queue/index.js');
        $content .= $this->readJSSource($nodeModuleDir . 'inter-mediator-nodegraph/index.js');
        $content .= $this->readJSSource($nodeModuleDir . 'inter-mediator-expressionparser/index.js');
        $content .= $this->readJSSource($jsCodeDir . 'INTER-Mediator.js');
        $content .= $this->readJSSource($jsCodeDir . 'INTER-Mediator-Page.js');
        $content .= $this->readJSSource($jsCodeDir . 'INTER-Mediator-ContextPool.js');
        $content .= $this->readJSSource($jsCodeDir . 'INTER-Mediator-Context.js');
        $content .= $this->readJSSource($jsCodeDir . 'INTER-Mediator-LocalContext.js');
        $content .= $this->readJSSource($jsCodeDir . 'INTER-Mediator-Lib.js');
        $content .= $this->readJSSource($jsCodeDir . 'INTER-Mediator-Element.js');
        $content .= $this->readJSSource($jsCodeDir . 'INTER-Mediator-Calc.js');
        $content .= $this->readJSSource($jsCodeDir . 'Adapter_DBServer.js');
        $content .= $this->readJSSource($jsCodeDir . 'INTER-Mediator-Navi.js');
        $content .= $this->readJSSource($jsCodeDir . 'INTER-Mediator-UI.js');
        $content .= $this->readJSSource($jsCodeDir . 'INTER-Mediator-Log.js');
        $content .= $this->readJSSource($jsCodeDir . 'INTER-Mediator-Events.js');
        $content .= $this->readJSSource($jsCodeDir . 'INTER-Mediator-DoOnStart.js');

        return $content;
    }

    private function readJSSource($filename)
    {
        $content = file_get_contents($filename);
        $pos = strpos($content, "@@IM@@IgnoringRestOfFile");
        if ($pos !== false) {
            $content = substr($content, 0, $pos) . "\n";
        }
        while (($pos = strpos($content, "@@IM@@IgnoringNextLine")) !== false) {
            $prePos = $pos;
            for ($i = $pos; $i > 0; $i--) {
                if (substr($content, $i, 1) === "\n") {
                    $prePos = $i;
                    break;
                }
            }
            $postPos = strpos($content, "\n", $pos);
            $postPos = strpos($content, "\n", $postPos + 1);
            if ($i >= 0) {
                $content = substr($content, 0, $prePos + 1) . substr($content, $postPos + 1);
            }
        }
        return $content;
    }
}
