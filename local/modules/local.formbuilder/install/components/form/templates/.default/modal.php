<?php

use Local\FormBuilder\Form\Field\Type\Factory\TypeFactory;
use Local\Util\Functions;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var array $arResult
 * @var string $templateFolder
 */
?>
<div class="modal-container">
    <div class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Заказать услугу</h2>
                <button class="close-modal-btn">
                    <?= Functions::buildSVG('close-btn-modal', $templateFolder . '/images') ?>
                </button>
            </div>
            <div class="scrolling-modal-content">
                <div class="modal-body">
                    <form class="modal-form">
                        <?php /*
                        <div class="info-block">
                            <span class="warning-icon">
                                <?= Functions::buildSVG('warning-icon', $templateFolder . '/images') ?>
                            </span>
                            <span class="f-14">
                                Пожалуйста, ознакомьтесь с <a href="#">Общим Регламентом</a> и <b>Регламентом оказания необходимой услуги.</b> Заполните <b>форму</b> и приложите её к вашей текущей заявке.
                            </span>
                        </div>
                        */?>
                        <?php foreach ($arResult['FORM']['FIELDS'] as $fieldId => $field) :?>
                            <div class="form-group">
                                <?php if ($field['IS_SHOW_TITLE']) :?>
                                    <label class="form-label">
                                        <?= $field['NAME'] ?>
                                        <?php if ($field['REQUIRED'] === 'Y') :?>
                                            <span class="req-symbol">*</span>
                                        <?php endif;?>
                                    </label>
                                <?php endif;?>
                                <?php
                                $type = TypeFactory::createType($field['TYPE']);
                                $type::showPublicForm($field['CODE'], $field['SETTINGS'], $fieldId);
                                ?>
                                <?php if (!empty($field['SETTINGS']['HINT'])) :?>
                                    <span class="tooltip-block f-12"><?= nl2br($field['SETTINGS']['HINT'])?></span>
                                <?php endif;?>
                            </div>
                        <?php endforeach;?>
                    </form>
                </div>
                <div class="modal-footer">
                    <div class="buttons-block">
                        <button class="btn btn-secondary">Отмена</button>
                        <button class="btn btn-primary">Отправить</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-overlay"></div>
</div>