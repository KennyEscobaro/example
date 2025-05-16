<?php

use Local\Util\Functions;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var string $templateFolder
 */
?>
<div class="modal-container">
    <div class="modal-success-block">
        <div class="modal-success-top">
            <span class="f-20 f-bold">Заявка принята</span>
            <button class="modal-success-close-btn">
                <?= Functions::buildSVG('close-btn-modal', $templateFolder . '/images') ?>
            </button>
        </div>
        <div class="modal-success-content f-16">
            <?= Functions::buildSVG('success-icon', $templateFolder . '/images') ?>
            Ваша заявка на оказание услуги успешно обработана. В ближайшее время наши специалисты свяжутся с вами.
        </div>
        <button class="modal-success-close-btn">Хорошо</button>
    </div>
    <div class="modal-overlay"></div>
</div>