<?php

use Bitrix\Main\Loader;

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php');

/**
 * @global CMain $APPLICATION
 */

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/sale/prolog.php');
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php');

if (Loader::includeModule('local.formbuilder')) {
    $APPLICATION->IncludeComponent('local:form.edit', '');
} else {
    ShowError('Модуль local.formbuilder не установлен');
}

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php');
