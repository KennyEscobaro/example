<?php

namespace Local\Main\Admin\Tab\Option\DTO;

use Local\Main\Admin\Tab\Option\Enum\FileUploadedType;

/**
 * Data Transfer Object для настроек загрузки файлов
 *
 * Содержит конфигурационные параметры для создания drag&drop
 * в административном интерфейсе, включая ограничения по количеству,
 * размеру, типам файлов и дополнительные опции.
 *
 * @package Local\Main\Admin\Tab\Option\DTO
 */
class FileSettingsDTO
{
    /** @var int $maxFileCount Максимальное количество загружаемых файлов */
    private int $maxFileCount;

    /** @var bool $isDescription Флаг отображения поля описания для файлов */
    private bool $isDescription;

    /** @var bool $isDisabled Флаг отключения загрузки файлов */
    private bool $isDisabled;

    /** @var FileUploadedType $type Тип загружаемых файлов (изображения/все файлы) */
    private FileUploadedType $type;

    /** @var array $fileExtensions Массив разрешенных расширений файлов */
    private array $fileExtensions;

    /** @var int $maxFileSize Максимальный размер файла в байтах (0 - без ограничений) */
    private int $maxFileSize;

    /**
     * Конструктор DTO настроек файлов
     *
     * @param int|null $maxFileCount Максимальное количество файлов (по умолчанию 1).
     * @param bool $isDescription Показывать поле описания (по умолчанию false).
     * @param bool $isDisabled Отключить загрузку файлов (по умолчанию false).
     * @param FileUploadedType|null $type Тип загружаемых файлов (по умолчанию ALL).
     * @param array $fileExtensions Массив разрешенных расширений (по умолчанию пустой).
     * @param int $maxFileSize Максимальный размер файла в байтах (по умолчанию 0 - без ограничений).
     */
    public function __construct(
        ?int $maxFileCount = 1,
        bool $isDescription = false,
        bool $isDisabled = false,
        ?FileUploadedType $type = null,
        array $fileExtensions = [],
        int $maxFileSize = 0,
    ) {
        $this->isDescription = $isDescription;
        $this->isDisabled = $isDisabled;
        $this->type = $type ?? FileUploadedType::ALL;
        $this->fileExtensions = $fileExtensions;
        $this->maxFileSize = $maxFileSize;
        $this->maxFileCount = $maxFileCount ?? 1;
    }

    /**
     * Получает максимальное количество файлов
     *
     * @return int Максимальное количество файлов для загрузки.
     */
    public function getMaxFileCount(): int
    {
        return $this->maxFileCount;
    }

    /**
     * Проверяет наличие поля описания
     *
     * @return bool true если поле описания активно, false если нет.
     */
    public function isDescription(): bool
    {
        return $this->isDescription;
    }

    /**
     * Проверяет отключение загрузки файлов
     *
     * @return bool true если загрузка отключена, false если разрешена.
     */
    public function isDisabled(): bool
    {
        return $this->isDisabled;
    }

    /**
     * Получает тип загружаемых файлов
     *
     * @return FileUploadedType Тип файлов.
     */
    public function getType(): FileUploadedType
    {
        return $this->type;
    }

    /**
     * Получает разрешенные расширения файлов
     *
     * @return array Массив разрешенных расширений (например, ['jpg', 'png']).
     */
    public function getFileExtensions(): array
    {
        return $this->fileExtensions;
    }

    /**
     * Получает максимальный размер файла
     *
     * @return int Максимальный размер в байтах (0 означает отсутствие ограничений).
     */
    public function getMaxFileSize(): int
    {
        return $this->maxFileSize;
    }

    /**
     * Преобразует настройки в массив для использования в административном интерфейсе
     *
     * @return array Ассоциативный массив с настройками:
     *              - description: bool - наличие поля описания
     *              - upload: bool - разрешена ли загрузка
     *              - allowUpload: bool - разрешен ли определенный тип загрузки
     *              - allowUploadExt: string - разрешенные расширения через запятую
     *              - maxCount: int - максимальное количество файлов
     *              - maxSize: int - максимальный размер файла.
     */
    public function toArray(): array
    {
        return [
            'description' => $this->isDescription,
            'upload' => !$this->isDisabled,
            'allowUpload' => !$this->type->value,
            'allowUploadExt' => ($this->type === FileUploadedType::FILE) ? implode(',', $this->getFileExtensions()) : '',
            'maxCount' => $this->maxFileCount,
            'maxSize' => $this->maxFileSize,
        ];
    }
}
