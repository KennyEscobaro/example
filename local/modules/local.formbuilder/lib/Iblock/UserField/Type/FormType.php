<?php

namespace Local\FormBuilder\Iblock\UserField\Type;

use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Local\FormBuilder\Form\Enum\Status;
use Local\FormBuilder\Form\ORM\FormTable;
use Local\Iblock\UserField\Type\SelectType;

class FormType extends SelectType
{
    /**
     * @inheritDoc
     */
    public static function getUserTypeDescription(): array
    {
        return
            [
                'USER_TYPE' => self::getUserType(),
                'DESCRIPTION' => 'Привязка к форме',
                'PROPERTY_TYPE' => PropertyTable::TYPE_NUMBER,
                'GetPropertyFieldHtml' => [__CLASS__, 'getPropertyFieldHtml'],
                'GetPropertyFieldHtmlMulty' => [__CLASS__, 'getPropertyFieldHtmlMulty'],
                'GetAdminListViewHTML' => [__CLASS__, 'getAdminListViewHTML'],
                'GetUIFilterProperty' => [__CLASS__, 'getUIFilterProperty'],
            ];
    }

    /**
     * @inheritDoc
     */
    public static function getUserType(): string
    {
        return 'lfb_form';
    }

    /**
     * Метод возвращает массив значения выпадающего списка.
     *
     * @return array массив значения выпадающего списка.
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected static function getValueOptions(): array
    {
        $formQuery = FormTable::query()
            ->setOrder(['NAME' => 'ASC', 'DATE_CREATE' => 'DESC'])
            ->setSelect(['ID', 'NAME', 'DATE_CREATE', 'STATUS'])
            ->exec();
        $forms = [];

        while ($form = $formQuery->fetch()) {
            $status = Status::tryFrom($form['STATUS']);
            $statusName = 'Неизвестный';

            if (isset($status)) {
                $statusName = Status::getStatusName($status);
            }

            $forms[$form['ID']] =
                [
                    'ID' => $form['ID'],
                    'VALUE' => '[' . $statusName .'] ' . $form['NAME'] . ' от ' . $form['DATE_CREATE']->format('d.m.Y H:i:s'),
                ];
        }

        return $forms;
    }
}
