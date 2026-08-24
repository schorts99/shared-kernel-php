<?php

namespace Schorts\SharedKernel\Model;

interface Identifiable
{
  public function getId(): string|int;
}

interface Auditable
{
  public function getCreatedAt(): string;
  public function getUpdatedAt(): string;
}

interface SoftDeletable
{
  public function getDeletedAt(): ?string;
}

interface Model extends Identifiable
{
  public function getAttributes(): array;
}

interface AuditableModel extends Model, Auditable {}

interface FullModel extends AuditableModel, SoftDeletable {}

function isIdentifiable(mixed $value): bool
{
  return $value instanceof Identifiable
    && (is_string($value->getId()) || is_int($value->getId()));
}

function getModelAttributes(Model $model): array
{
  $attributes = $model->getAttributes();

  unset($attributes['id'], $attributes['createdAt'], $attributes['updatedAt'], $attributes['deletedAt']);

  return $attributes;
}
