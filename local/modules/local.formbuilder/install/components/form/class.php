<?php

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Engine\Response\Converter;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Data\AddResult;
use Bitrix\Main\Result;
use Bitrix\Main\SystemException;
use Local\FormBuilder\Form\Enum\Status;
use Local\FormBuilder\Form\Field\ORM\FormFieldEnumTable;
use Local\FormBuilder\Form\Field\ORM\FormFieldTable;
use Local\FormBuilder\Form\Field\Type\Entity\Boolean as BooleanField;
use Local\FormBuilder\Form\Field\Type\Entity\File as FileField;
use Local\FormBuilder\Form\Field\Type\Entity\TextBlock as TextBlockField;
use Local\FormBuilder\Form\Field\Type\Factory\TypeFactory;
use Local\FormBuilder\Form\Field\Validator\Factory\ValidatorFactory;
use Local\FormBuilder\Form\Field\Validator\ORM\FormFieldValidatorTable;
use Local\FormBuilder\Form\ORM\FormTable;
use Local\FormBuilder\Form\Result\ORM\FormResultTable;
use Local\FormBuilder\Form\Result\ORM\FormResultValueTable;
use Local\FormBuilder\Main\Engine\Response\OutputCapture;
use Local\Helper\CommonHelper;
use Local\Exception\Argument\InvalidArgumentException;

Loader::includeModule('local.formbuilder');

/**
 * Компонент для построения форм
 */
class FormComponent extends CBitrixComponent implements Controllerable, Errorable
{
    /** @var ErrorCollection $errorCollection Коллекция ошибок компонента */
    protected ErrorCollection $errorCollection;

    /** @var CurrentUser $currentUser Текущий авторизованный пользователь */
    private CurrentUser $currentUser;

    /**
     * Конструктор класса.
     *
     * @param CBitrixComponent|null $component Родительский компонент.
     */
    public function __construct(CBitrixComponent $component = null)
    {
        parent::__construct($component);

        $this->currentUser = CurrentUser::get();
    }

    /**
     * Метод подготавливает параметры компонента.
     *
     * @param array $arParams Параметры компонента.
     * @return array Обработанные параметры компонента.
     */
    public function onPrepareComponentParams($arParams): array
    {
        $arParams['FORM_ID'] = (int)$arParams['FORM_ID'];

        $this->errorCollection = new ErrorCollection();

        return $arParams;
    }

    /**
     * Метод выполняет основную логику компонента.
     *
     * @return void
     */
    public function executeComponent(): void
    {
        $this->IncludeComponentTemplate();
    }

    /**
     * Метод возвращает HTML-код модального окна формы в формате JSON.
     *
     * @return array Массив с результатом выполнения и HTML-кодом модального окна.
     */
    public function getModalAction(): array
    {
        $converter = (new Converter(Converter::OUTPUT_JSON_FORMAT));

        try {
            if (!$this->arParams['FORM_ID']) {
                $this->errorCollection->add([new Error('Не указан идентификатор формы')]);
                return
                    $converter->process(
                        [
                            'RESULT' => 'Ошибка при получении модального окна',
                        ]
                    );
            }

            $form = $this->getForm($this->arParams['FORM_ID']);

            if (!$form) {
                $this->errorCollection->add([new Error('Форма не найдена')]);
                return
                    $converter->process(
                        [
                            'RESULT' => 'Ошибка при получении модального окна',
                        ]
                    );
            }

            $this->arResult['FORM'] = $form;

            $capture = new OutputCapture();

            $capture->startCapture();
            $this->includeComponentTemplate('modal');
            $result = $capture->endCapture();

            array_walk_recursive($result, function(&$value) {
                if (is_string($value)) {
                    $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                }
            });

            return $converter->process($result);
        } catch (Throwable $e) {
            $this->errorCollection->add([new Error($e->getMessage())]);
            return $converter->process(['RESULT' => 'Ошибка при получении модального окна']);
        }
    }

    /**
     * Метод создает результат заполнения формы и возвращает статус операции.
     *
     * @param array $fields Массив полей формы с их значениями.
     * @return array Массив с результатом выполнения операции.
     */
    public function createResultAction(array $fields = []): array
    {
        $converter = (new Converter(Converter::OUTPUT_JSON_FORMAT));

        try {
            if (!$this->arParams['FORM_ID']) {
                $this->errorCollection->add([new Error('Не указан идентификатор формы')]);
                return $converter->process(['RESULT' => 'Ошибка при отправке заявки']);
            }

            $form = $this->getForm($this->arParams['FORM_ID']);

            if (!$form) {
                $this->errorCollection->add([new Error('Форма не найдена')]);
                return $converter->process(['RESULT' => 'Ошибка при отправке заявки']);
            }

            $fields = CommonHelper::removeEmptyValues($fields);
            $preparedFields = $this->prepareForDatabase($form, $fields);

            $checkRequiredFieldsResult = $this->checkRequiredFields($form, $preparedFields);

            if (!$checkRequiredFieldsResult->isSuccess()) {
                $this->errorCollection->add($checkRequiredFieldsResult->getErrors());
                return $converter->process(
                    [
                        'RESULT' => 'Ошибка при отправке заявки',
                        'INVALID_FIELDS' => $checkRequiredFieldsResult->getData()['INVALID_FIELDS'],
                    ]
                );
            }

            $validateFieldsResult = $this->validateFields($form, $preparedFields);

            if (!$validateFieldsResult->isSuccess()) {
                $this->errorCollection->add($validateFieldsResult->getErrors());
                return $converter->process(['RESULT' => 'Ошибка при отправке заявки']);
            }

            $connection = Application::getConnection();
            $connection->startTransaction();

            $createResult = $this->createResult($form['ID']);

            if (!$createResult->isSuccess()) {
                $connection->rollbackTransaction();
                $this->errorCollection->add($createResult->getErrors());
                return $converter->process(['RESULT' => 'Ошибка при отправке заявки']);
            }

            $createValuesResult = $this->createResultValues($createResult->getId(), $form, $preparedFields);

            if (!$createValuesResult->isSuccess()) {
                $connection->rollbackTransaction();
                $this->errorCollection->add($createValuesResult->getErrors());
                return $converter->process(['RESULT' => 'Ошибка при отправке заявки']);
            }

            $connection->commitTransaction();

            ob_start();
            $this->includeComponentTemplate('modal-success');
            $html = ob_get_clean();

            return $converter->process(['RESULT' => 'Заявка успешно отправлена', 'HTML' => $html]);
        } catch (Throwable $exception) {
            $this->errorCollection->add([new Error($exception->getMessage())]);
            return $converter->process(['RESULT' => 'Ошибка при отправке заявки']);
        }
    }

    /**
     * Метод конфигурирует доступные действия компонента.
     *
     * @return array Массив с конфигурацией действий.
     */
    public function configureActions(): array
    {
        return
            [
                'getModal' => [
                    'prefilters' => [],
                ],
                'createResult' => [
                    'prefilters' => [],
                ],
            ];
    }

    /**
     * Метод возвращает массив ошибок компонента.
     *
     * @return array|Error[] Массив ошибок.
     */
    public function getErrors(): array
    {
        return $this->errorCollection->toArray();
    }

    /**
     * Метод возвращает ошибку по ее коду.
     *
     * @param string $code Код ошибки.
     * @return Error Объект ошибки.
     */
    public function getErrorByCode($code): Error
    {
        return $this->errorCollection->getErrorByCode($code);
    }

    /**
     * Метод возвращает список параметров, которые должны быть подписаны.
     *
     * @return string[] Массив имен параметров.
     */
    protected function listKeysSignedParameters(): array
    {
        return ['FORM_ID'];
    }

    /**
     * Метод создает значения результатов для полей формы.
     *
     * @param int $resultId ID созданного результата.
     * @param array $form Массив данных формы.
     * @param array $fields Массив значений полей.
     * @return Result Объект результата операции.
     * @throws Exception
     */
    private function createResultValues(int $resultId, array $form, array $fields): Result
    {
        $result = new Result();
        $fieldsMap = array_column($form['FIELDS'], 'ID', 'CODE');

        foreach ($fields as $name => $value) {
            $field = $form['FIELDS'][$fieldsMap[$name]];

            $addValueResult = FormResultValueTable::add([
                'RESULT_ID' => $resultId,
                'FIELD_ID' => $field['ID'],
                'VALUE' => $value,
            ]);

            $result->addErrors($addValueResult->getErrors());
        }

        return $result;
    }

    /**
     * Метод создает запись результата заполнения формы.
     *
     * @param int $formId ID формы.
     * @return AddResult Результат добавления записи.
     * @throws Exception
     */
    private function createResult(int $formId): AddResult
    {
        return FormResultTable::add([
            'FORM_ID' => $formId,
            'USER_ID' => (int)$this->currentUser->getId(),
            'USER_AUTH' => $this->currentUser->getId() ? 'Y' : 'N',
            'STAT_GUEST_ID' => $_SESSION['SESS_GUEST_ID'] ? (int)$_SESSION['SESS_GUEST_ID'] : null,
            'STAT_SESSION_ID' => $_SESSION['SESS_SESSION_ID'] ? (int)$_SESSION['SESS_SESSION_ID'] : null,
        ]);
    }

    /**
     * Метод подготавливает значения полей для сохранения в БД.
     *
     * @param array $form Массив данных формы.
     * @param array $fields Массив значений полей.
     * @return array Массив подготовленных значений.
     * @throws InvalidArgumentException
     */
    private function prepareForDatabase(array $form, array $fields): array
    {
        $fieldsMap = array_column($form['FIELDS'], 'ID', 'CODE');
        $preparedFields = [];

        foreach ($fields as $name => $value) {
            $field = $form['FIELDS'][$fieldsMap[$name]];

            if (!$field) {
                continue;
            }

            $type = TypeFactory::createType($field['TYPE']);

            $preparedFields[$name] = $type::normalizeFromRequest($value);
        }

        return $preparedFields;
    }

    /**
     * Метод проверяет заполнение обязательных полей формы.
     *
     * @param array $form Массив данных формы.
     * @param array $fields Массив значений полей.
     * @return Result Объект результата проверки.
     */
    private function checkRequiredFields(array $form, array $fields): Result
    {
        $result = new Result();
        $invalidFields = [];
        $fieldsMap = array_column($form['FIELDS'], 'CODE', 'ID');

        foreach ($form['FIELDS'] as $field) {
            if ($field['REQUIRED'] !== 'Y') {
                continue;
            }

            $value = $fields[$fieldsMap[$field['ID']]];

            if (!CommonHelper::isReallyEmpty($value)) {
                continue;
            }

            $invalidFields[] = $fieldsMap[$field['ID']];
        }

        if ($invalidFields) {
            $result->addError(new Error('Не заполнены обязательные поля', 'USER_ERROR'));
            $result->setData(['INVALID_FIELDS' => $invalidFields]);
        }

        return $result;
    }

    /**
     * Метод валидирует значения полей формы.
     *
     * @param array $form Массив данных формы.
     * @param array $fields Массив значений полей.
     * @return Result Объект результата валидации.
     * @throws ArgumentException
     * @throws InvalidArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    private function validateFields(array $form, array $fields): Result
    {
        $result = new Result();
        $invalidFields = [];
        $fieldsMap = array_column($form['FIELDS'], 'ID', 'CODE');
        $fieldsValidators = $this->getFormFieldValidators(array_keys($fields));

        foreach ($fields as $name => $value) {
            if (!CommonHelper::isReallyEmpty($value)) {
                continue;
            }

            $field = $form['FIELDS'][$fieldsMap[$name]];
            $fieldValidators = $fieldsValidators[$field['ID']];

            if (!$fieldValidators) {
                continue;
            }

            $validateFieldResult = $this->validateField($fieldValidators, $form, $field, $value);

            if (!$validateFieldResult->isSuccess()) {
                $invalidFields[] = $name;
                $this->errorCollection->add($validateFieldResult->getErrors());

                if ($validateFieldResult->getErrorCollection()->getErrorByCode('')) {
                    break;
                }
            }
        }

        $result->setData(['INVALID_FIELDS' => $invalidFields]);

        return $result;
    }

    /**
     * Метод валидирует значение одного поля формы.
     *
     * @param array $fieldValidators Массив валидаторов поля.
     * @param array $form Массив данных формы.
     * @param array $field Массив данных поля.
     * @param mixed $value Значение поля.
     * @return Result Объект результата валидации.
     * @throws ArgumentException
     * @throws InvalidArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    private function validateField(array $fieldValidators, array $form, array $field, mixed $value): Result
    {
        $result = new Result();

        foreach ($fieldValidators as $validator) {
            try {
                $validatorClass = ValidatorFactory::getValidator($validator['NAME']);
            } catch (InvalidArgumentException $exception) {
                $result->addError(new Error('Неизвестный валидатор ' . $validator['NAME']));
                break;
            }

            $validatorSettings = $validator['SETTINGS'] ? unserialize($validator['SETTINGS']) : [];
            $validator = $validatorClass::createFromArray($validatorSettings);

            $values = ($field['SETTINGS']['IS_MULTIPLE'] === 'Y') ? $value : [$value];

            $validateResult = $validator->validate($form['ID'], $field['ID'], $values);

            foreach ($validateResult->getErrors() as $error) {
                $result->addError(new Error($error->getMessage(), 'USER_ERROR'));
            }
        }

        return $result;
    }

    /**
     * Метод возвращает данные формы по ее ID.
     *
     * @param int $formId ID формы.
     * @return array Массив данных формы.
     * @throws ArgumentException
     * @throws InvalidArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    private function getForm(int $formId): array
    {
        $form = FormTable::query()
            ->setFilter(['ID' => $formId, 'ACTIVE' => 'Y', 'STATUS' => Status::PUBLISHED->value])
            ->setSelect(['*'])
            ->exec()
            ->fetch();

        if (!$form) {
            return [];
        }

        $form['FIELDS'] = $this->getFormFields($formId);

        return $form;
    }

    /**
     * Метод возвращает поля формы по ID формы.
     *
     * @param int $formId ID формы.
     * @return array Массив полей формы.
     * @throws ArgumentException
     * @throws InvalidArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    private function getFormFields(int $formId): array
    {
        $fieldQuery = FormFieldTable::query()
            ->setOrder(['SORT' => 'ASC', 'ID' => 'ASC'])
            ->setFilter(['FORM_ID' => $formId, 'ACTIVE' => 'Y'])
            ->setSelect(['*'])
            ->exec();

        $fields = [];
        $fieldsIdSupportEnum = [];

        while ($field = $fieldQuery->fetch()) {
            $type = TypeFactory::createType($field['TYPE']);

            if ($type::isSupportEnum()) {
                $fieldsIdSupportEnum[] = $field['ID'];
            }

            $field['IS_SHOW_TITLE'] = match ($field['TYPE']) {
                BooleanField::getType(), FileField::getType(), TextBlockField::getType() => false,
                default => true
            };
            $field['SETTINGS'] = unserialize($field['SETTINGS']);

            $fields[$field['ID']] = $field;
        }

        $fieldsEnumValues = $this->getFormFieldEnumValues($fieldsIdSupportEnum);

        foreach ($fieldsEnumValues as $fieldId => $fieldEnumValues) {
            $fields[$fieldId]['ENUM_VALUES'] = $fieldEnumValues;
        }

        return $fields;
    }

    /**
     * Метод возвращает значения перечислений для полей формы.
     *
     * @param int|array $fieldId ID поля или массив ID полей.
     * @return array Массив значений перечислений.
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    private function getFormFieldEnumValues(int|array $fieldId): array
    {
        $enumQuery = FormFieldEnumTable::query()
            ->setOrder(['SORT' => 'ASC'])
            ->setFilter(['FIELD_ID' => $fieldId])
            ->setSelect(['*'])
            ->exec();

        $enumValues = [];

        while ($enum = $enumQuery->fetch()) {
            if (is_array($fieldId)) {
                $enumValues[$enum['FIELD_ID']][$enum['ID']] = $enum;
            } else {
                $enumValues[$enum['ID']] = $enum;
            }
        }

        return $enumValues;
    }

    /**
     * Метод возвращает валидаторы для полей формы.
     *
     * @param int|array $fieldId ID поля или массив ID полей.
     * @return array Массив валидаторов.
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    private function getFormFieldValidators(int|array $fieldId): array
    {
        $validatorQuery = FormFieldValidatorTable::query()
            ->setOrder(['ID' => 'ASC'])
            ->setFilter(['FIELD_ID' => $fieldId])
            ->setSelect(['*'])
            ->exec();

        $validators = [];

        while ($validator = $validatorQuery->fetch()) {
            $validator['SETTINGS'] = unserialize($validator['SETTINGS']);

            if (is_array($fieldId)) {
                $validators[$validator['FIELD_ID']][$validator['ID']] = $validator;
            } else {
                $validators[$validator['ID']] = $validator;
            }
        }

        return $validators;
    }
}
