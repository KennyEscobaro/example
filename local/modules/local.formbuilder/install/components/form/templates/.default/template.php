<?php

use Bitrix\Main\UI\Extension;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var array $arResult
 */

Extension::load(['local.global']);
?>
<button class="right-service-detail-button order-service-btn">Заказать услугу</button>
<script type="text/javascript">
    BX.ready(function () {
        const form = new BX.Form(
            {
                componentName: '<?= $this->getComponent()->getName() ?>',
                signedParameters: <?=CUtil::PhpToJSObject($this->getComponent()->getSignedParameters()) ?>,
                actions: {
                    getModal: 'getModal',
                    createResult: 'createResult'
                }
            }
        );

        form.init();
    });
</script>