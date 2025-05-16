<?php

namespace Local\DataMapper;

/**
 * Класс, представляющий базовую сущность с первичным ключом и связью с коллекцией
 *
 * @package Local\DataMapper
 */
abstract class Entity
{
    /** @var string $primary Первичный ключ сущности */
    protected string $primary;

    /** @var Collection|null $collection Коллекция, к которой принадлежит сущность */
    private ?Collection $collection;

    /**
     * Конструктор сущности
     *
     * @param string $primary Первичный ключ сущности.
     */
    public function __construct(string $primary)
    {
        $this->primary = $primary;
    }

    /**
     * Метод возвращает первичный ключ сущности
     *
     * @return string Первичный ключ сущности.
     */
    public function getPrimary(): string
    {
        return $this->primary;
    }

    /**
     * Метод возвращает коллекцию, к которой принадлежит сущность
     *
     * @return Collection|null Коллекция сущностей или null, если сущность не принадлежит коллекции.
     */
    public function getCollection(): ?Collection
    {
        return $this->collection;
    }

    /**
     * Метод устанавливает связь сущности с коллекцией
     *
     * @param Collection|null $collection Коллекция сущностей или null для удаления связи.
     * @return void
     */
    public function setCollection(?Collection $collection): void
    {
        $this->collection = $collection;
    }
}
