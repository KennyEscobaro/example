<?php

namespace Local\DataMapper;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use Countable;
use IteratorAggregate;
use ArrayIterator;

/**
 * Класс, реализующий коллекцию сущностей с базовыми методами для работы с набором объектов
 *
 * @package Local\DataMapper
 */
abstract class Collection implements Countable, IteratorAggregate
{
    /** @var Entity[] $elements Массив сущностей */
    protected array $elements = [];

    /** @var array $elementsMapByPrimary Соотношение сущностей по их идентификаторам */
    protected array $elementsMapByPrimary = [];

    /** @var string $entityClass Класс сущности */
    protected string $entityClass;

    /**
     * @param Entity[] $elements Массив сущностей для инициализации коллекции.
     * @throws ArgumentException Выбрасывается если переданные элементы не соответствуют ожидаемому классу.
     */
    public function __construct(array $elements = [])
    {
        $this->entityClass = static::getEntityClass();

        foreach ($elements as $element) {
            $this->add($element);
        }
    }

    /**
     * Метод добавляет сущность в коллекцию
     *
     * @param Entity $element Добавляемая сущность.
     * @return $this Коллекция с добавленным элементом.
     * @throws ArgumentException Выбрасывается если переданная сущность не соответствует ожидаемому классу.
     */
    public function add(Entity $element): self
    {
        if (!($element instanceof $this->entityClass)) {
            throw new ArgumentException(
                sprintf(
                    Loc::getMessage('ILLEGAL_CLASS_COLLECTION'),
                    get_class($element),
                    get_class($this),
                    $this->entityClass,
                )
            );
        }

        $element->setCollection($this);
        $this->elements[] = $element;
        $this->elementsMapByPrimary[$element->getPrimary()] = &$this->elements[array_key_last($this->elements)];
        return $this;
    }

    /**
     * Метод возвращает итератор для коллекции
     *
     * @return ArrayIterator Итератор по элементам коллекции.
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->elements);
    }

    /**
     * Метод возвращает количество объектов в коллекции
     *
     * @return int Количество элементов в коллекции.
     */
    public function count(): int
    {
        return count($this->elements);
    }

    /**
     * Метод проверяет коллекцию на пустоту
     *
     * @return bool Возвращает true если коллекция пуста, false если содержит элементы.
     */
    public function isEmpty(): bool
    {
        return $this->count() <= 0;
    }

    /**
     * Метод возвращает первый объект в коллекции
     *
     * @return Entity|null Первый элемент коллекции или null если коллекция пуста.
     */
    public function first(): ?Entity
    {
        $elements = $this->elements;
        return $this->count() > 0 ? array_shift($elements) : null;
    }

    /**
     * Метод возвращает последний объект в коллекции
     *
     * @return Entity|null Последний элемент коллекции или null если коллекция пуста.
     */
    public function last(): ?Entity
    {
        $elements = $this->elements;
        return $this->count() > 0 ? array_pop($elements) : null;
    }

    /**
     * Метод возвращает все элементы коллекции в виде массива
     *
     * @return Entity[] Массив всех элементов коллекции.
     */
    public function getAll(): array
    {
        return $this->elements;
    }

    /**
     * Метод проверяет наличие конкретного объекта в коллекции
     *
     * @param Entity $element Проверяемая сущность.
     * @return bool Возвращает true если сущность найдена в коллекции.
     * @throws ArgumentException Выбрасывается если переданная сущность не соответствует ожидаемому классу.
     */
    public function has(Entity $element): bool
    {
        if (!($element instanceof $this->entityClass)) {
            throw new ArgumentException(
                sprintf(
                    Loc::getMessage('ILLEGAL_CLASS_COLLECTION'),
                    get_class($element),
                    get_class($this),
                    $this->entityClass,
                )
            );
        }

        return in_array($element, $this->elements, true);
    }

    /**
     * Метод удаляет объект из коллекции
     *
     * @param Entity $element Удаляемая сущность.
     * @return $this Коллекция с удаленным элементом.
     * @throws ArgumentException Выбрасывается если переданная сущность не соответствует ожидаемому классу
     */
    public function remove(Entity $element): self
    {
        if (!($element instanceof $this->entityClass)) {
            throw new ArgumentException(
                sprintf(
                    Loc::getMessage('ILLEGAL_CLASS_COLLECTION'),
                    get_class($element),
                    get_class($this),
                    $this->entityClass,
                )
            );
        }

        $key = array_search($element, $this->elements, true);
        if ($key !== false) {
            unset($this->elements[$key]);
            unset($this->elementsMapByPrimary[$element->getPrimary()]);
        }
        return $this;
    }

    /**
     * Метод очищает коллекцию
     *
     * @return $this Коллекция с удаленными элементами.
     */
    public function clear(): self
    {
        $this->elements = [];
        $this->elementsMapByPrimary = [];
        return $this;
    }

    /**
     * Метод ищет сущность по первичному ключу
     *
     * @param string $primary Первичный ключ искомой сущности.
     * @return Entity|null Найденная сущность или null если не найдена.
     */
    public function getByPrimary(string $primary): ?Entity
    {
        return $this->elementsMapByPrimary[$primary];
    }

    /**
     * Метод возвращает все первичные ключи сущностей в коллекции
     *
     * @return array Массив первичных ключей.
     */
    public function getPrimaries(): array
    {
        return array_keys($this->elementsMapByPrimary);
    }

    /**
     * Метод проверяет наличие сущности с указанным первичным ключом в коллекции
     *
     * @param string $primary Первичный ключ для проверки.
     * @return bool Возвращает true если сущность с таким ключом найдена.
     * @throws SystemException Выбрасывается при возникновении системной ошибки.
     */
    public function hasByPrimary(string $primary): bool
    {
        $element = $this->elementsMapByPrimary[$primary];

        if ($element === null) {
            return false;
        }

        return $this->has($element);
    }

    /**
     * Метод удаляет сущность с указанным первичным ключом из коллекции
     *
     * @param string $primary Первичный ключ удаляемой сущности.
     * @return $this Коллекция с удаленным элементом.
     * @throws SystemException Выбрасывается при возникновении системной ошибки.
     */
    public function removeByPrimary(string $primary): self
    {
        $element = $this->elementsMapByPrimary[$primary];

        if ($element !== null) {
            $this->remove($element);
        }

        return $this;
    }

    /**
     * Абстрактный метод, возвращающий класс сущности для коллекции
     *
     * @return string Полное имя класса сущности.
     */
    abstract public static function getEntityClass(): string;
}
