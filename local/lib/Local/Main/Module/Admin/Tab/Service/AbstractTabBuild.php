<?php

namespace Local\Main\Module\Admin\Tab\Service;

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Error;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Result;
use Local\Main\Admin\Tab\Collection\TabCollection;
use Local\Main\Admin\Tab\Entity\Tab;
use Local\Main\Admin\Tab\Form\Entity\Buttons;
use Local\Main\Admin\Tab\Form\Entity\Form;
use Local\Main\Admin\Tab\Option\Entity\Option as TabOption;
use Local\Main\Admin\Tab\Option\Enum\OptionState;

/**
 * Абстрактный класс для построения вкладок административной панели.
 * Предоставляет базовую функциональность для работы с настройками модуля.
 *
 * @package Local\Main\Module\Admin\Tab\Service
 */
abstract class AbstractTabBuild
{
    /** @var HttpRequest|null $request HTTP-запрос */
    protected ?HttpRequest $request;

    /** @var CurrentUser $currentUser Текущий пользователь */
    protected CurrentUser $currentUser;

    /** @var string $documentRoot Корневая директория сайта */
    protected string $documentRoot;

    /**
     * Конструктор класса AbstractTabBuild.
     *
     * @param HttpRequest|null $request HTTP-запрос.
     */
    public function __construct(?HttpRequest $request)
    {
        $this->request = $request;
        $this->currentUser = CurrentUser::get();
        $this->documentRoot = Application::getDocumentRoot();
    }

    /**
     * Метод строит коллекцию вкладок и обрабатывает POST-запросы.
     *
     * @return Result Результат выполнения с коллекцией вкладок и формой.
     * @throws ArgumentOutOfRangeException
     * @throws SqlQueryException
     */
    public function build(): Result
    {
        $result = new Result();

        if (!$this->hasReadAccess()) {
            return $result;
        }

        $tabCollection = $this->getTabCollection();
        $this->setValueOptions($tabCollection);

        if ($this->request->isPost() && $this->request->getValues() && check_bitrix_sessid()) {
            $processedTabCollection = clone $tabCollection;
            $result = $this->handlerRequest($processedTabCollection);

            if ($result->isSuccess()) {
                $tabCollection = $processedTabCollection;
            }
        }

        $result->setData(['TAB_COLLECTION' => $tabCollection, 'FORM' => $this->getForm()]);

        return $result;
    }

    /**
     * Метод проверяет наличие прав на чтение.
     *
     * @return bool Возвращает true если есть права на чтение.
     */
    public function hasReadAccess(): bool
    {
        return $this->getModulePermission() >= 'R';
    }

    /**
     * Метод проверяет наличие прав на запись.
     *
     * @return bool Возвращает true если есть права на запись.
     */
    public function hasWriteAccess(): bool
    {
        return $this->getModulePermission() >= 'W';
    }

    /**
     * Метод обрабатывает POST-запрос для сохранения настроек.
     *
     * @param TabCollection $tabCollection Коллекция вкладок.
     * @return Result Результат обработки запроса.
     * @throws ArgumentOutOfRangeException
     * @throws SqlQueryException
     */
    protected function handlerRequest(TabCollection $tabCollection): Result
    {
        $result = new Result();

        if (!$this->hasWriteAccess()) {
            $result->addError(new Error('Отсутствуют права доступа на изменение'));
            return $result;
        }

        $requestFields = $this->request->toArray();

        $connection = Application::getConnection();
        $connection->startTransaction();

        /** @var Tab $tab */
        foreach ($tabCollection as $tab) {
            /** @var TabOption $option */
            foreach ($tab->getElements()->getOptions() as $option) {
                $processedOptionResult = $this->handlerOption($option, $requestFields);
                $result->addErrors($processedOptionResult->getErrors());
            }
        }

        if (!$result->isSuccess()) {
            $connection->rollbackTransaction();
            return $result;
        }

        $connection->commitTransaction();

        return $result;
    }

    /**
     * Метод обрабатывает отдельную опцию из запроса.
     *
     * @param TabOption $option Опция для обработки.
     * @param array $requestFields Поля запроса.
     * @return Result Результат обработки опции.
     * @throws ArgumentOutOfRangeException
     */
    protected function handlerOption(TabOption $option, array $requestFields): Result
    {
        $result = new Result();

        $optionValue = $option->normalizeFromRequest($requestFields);

        if ($option->hasValidators()) {
            $result->addErrors($option->validate($optionValue)->getErrors());
        }

        if (!$result->isSuccess()) {
            return $result;
        }

        if ($option->isRequired() && !$optionValue) {
            $fieldName = rtrim($option->getName(), ':');
            $result->addError(new Error('Заполните обязательное поле "' . $fieldName . '"'));
            return $result;
        }

        if ($requestFields['default']) {
            $optionValue = $option->normalizeDefaultValueForDatabase();
            $optionState = $this->setAdminTabOptionValue($option, $optionValue);

            if ($optionState !== OptionState::ACTUAL) {
                $option->setValue($option->getDefaultValue());
            }

            return $result;
        }

        if ($requestFields['save'] || $requestFields['update']) {
            $cloneOption = clone $option;
            $optionValue = $cloneOption->setValue($optionValue)->normalizeValueForDatabase();

            $optionState = $this->setAdminTabOptionValue($option, $optionValue);

            if ($optionState !== OptionState::ACTUAL) {
                $value = $option->normalizeFromDatabase($optionValue);
                $option->setValue($value);
            }
        }

        return $result;
    }

    /**
     * Метод сохраняет значение опции в настройках модуля.
     *
     * @param TabOption $option Опция для сохранения.
     * @param string|array $newValue Новое значение.
     * @return OptionState Состояние опции после сохранения.
     * @throws ArgumentOutOfRangeException
     */
    protected function setAdminTabOptionValue(TabOption $option, string|array $newValue): OptionState
    {
        $optionState = OptionState::NEW;
        $currentValue = $option->normalizeValueForDatabase();

        if ($currentValue === $newValue) {
            return OptionState::ACTUAL;
        }

        if ($option->getValue() !== null) {
            $optionState = OptionState::CHANGED;
        }

        Option::set($this->getModuleId(), $option->getPrimary(), $newValue);

        return $optionState;
    }

    /**
     * Метод устанавливает значения для опций из параметров модуля.
     *
     * @param TabCollection $tabCollection Коллекция вкладок.
     * @return void
     */
    protected function setValueOptions(TabCollection $tabCollection): void
    {
        $moduleParams = $this->getModuleParams();

        /** @var Tab $tab */
        foreach ($tabCollection as $tab) {
            /** @var TabOption $option */
            foreach ($tab->getElements()->getOptions() as $option) {
                if (!isset($moduleParams[$option->getPrimary()])) {
                    continue;
                }

                $value = $option->normalizeFromDatabase($moduleParams[$option->getPrimary()]);
                $option->setValue($value);
            }
        }
    }

    /**
     * Метод создает объект формы для вкладок.
     *
     * @return Form Объект формы.
     */
    protected function getForm(): Form
    {
        global $APPLICATION;

        return new Form(
            $this::getFormId(),
            $this::getFormName(),
            $APPLICATION->GetCurPage() . '?mid=' . $this->getModuleId() . '&lang=' . LANGUAGE_ID,
            new Buttons(
                true,
                false,
                false,
                false,
                [
                    '<input type="submit" name="default" value="По умолчанию" ' . ($this->hasWriteAccess(
                    ) ? '' : 'disabled') . '>',
                ],
                !$this->hasWriteAccess()
            )
        );
    }

    /**
     * Метод возвращает уровень прав доступа к модулю.
     *
     * @return string Уровень прав доступа.
     */
    abstract public function getModulePermission(): string;

    /**
     * Метод возвращает идентификатор модуля.
     *
     * @return string Идентификатор модуля.
     */
    abstract public function getModuleId(): string;

    /**
     * Метод возвращает параметры модуля.
     *
     * @return array Массив параметров модуля.
     */
    abstract public function getModuleParams(): array;

    /**
     * Метод возвращает коллекцию вкладок.
     *
     * @return TabCollection Коллекция вкладок.
     */
    abstract protected function getTabCollection(): TabCollection;

    /**
     * Метод возвращает идентификатор формы.
     *
     * @return string Идентификатор формы.
     */
    abstract public static function getFormId(): string;

    /**
     * Метод возвращает название формы.
     *
     * @return string Название формы.
     */
    abstract public static function getFormName(): string;
}
