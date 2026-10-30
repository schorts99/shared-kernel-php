<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\Tests\DAO;

use PHPUnit\Framework\TestCase;
use Schorts\SharedKernel\Criteria\Criteria;
use Schorts\SharedKernel\Criteria\Direction;
use Schorts\SharedKernel\DAO\DeleteMode;
use Schorts\SharedKernel\DAO\InMemoryDAO;
use Schorts\SharedKernel\Entity\Entity;
use Schorts\SharedKernel\ValueObjects\StringValue;

class ProductId extends StringValue {
  public function getAttributeName(): string { return 'id'; }
}

class ProductEntity extends Entity {
  public function __construct(
    ProductId $id,
    public string $name,
    public float $price,
    public string $category,
  ) {
    parent::__construct($id);
  }

  public function toPrimitives(): array {
    return [
      'id' => (string) $this->id,
      'name' => $this->name,
      'price' => $this->price,
      'category' => $this->category,
    ];
  }
}

final class InMemoryDAOTest extends TestCase
{
  public function testCRUDOperations(): void
  {
    $dao = new InMemoryDAO();
    $p1 = new ProductEntity(new ProductId('p-1'), 'Keyboard', 49.99, 'Electronics');
    $p2 = new ProductEntity(new ProductId('p-2'), 'Mouse', 29.99, 'Electronics');

    $dao->create($p1);
    $dao->create($p2);

    $this->assertSame(2, $dao->count());
    $this->assertNotNull($dao->findByID('p-1'));
    $this->assertSame('Keyboard', $dao->findByID('p-1')->name);

    $dao->deleteByID('p-1');
    $this->assertSame(1, $dao->count());
    $this->assertNull($dao->findByID('p-1'));
  }

  public function testCriteriaSearchAndPagination(): void
  {
    $dao = new InMemoryDAO();
    $dao->create(new ProductEntity(new ProductId('1'), 'Product A', 10.0, 'Books'));
    $dao->create(new ProductEntity(new ProductId('2'), 'Product B', 30.0, 'Electronics'));
    $dao->create(new ProductEntity(new ProductId('3'), 'Product C', 20.0, 'Books'));
    $dao->create(new ProductEntity(new ProductId('4'), 'Product D', 50.0, 'Electronics'));

    $criteria = Criteria::none()
      ->whereEqual('category', 'Books')
      ->orderBy('price', Direction::ASC);

    $results = $dao->search($criteria);

    $this->assertCount(2, $results);
    $this->assertSame('Product A', $results[0]->name);
    $this->assertSame('Product C', $results[1]->name);

    // Limit and offset
    $pagedCriteria = Criteria::none()
      ->orderBy('price', Direction::DESC)
      ->limitResults(2)
      ->offsetResults(1);

    $paged = $dao->search($pagedCriteria);
    $this->assertCount(2, $paged);
    $this->assertSame('Product B', $paged[0]->name); // 50 (D), 30 (B), 20 (C), 10 (A) -> offset 1 gives B and C
    $this->assertSame('Product C', $paged[1]->name);
  }

  public function testSoftDeleteAndRestore(): void
  {
    $dao = new InMemoryDAO(DeleteMode::SOFT);
    $p = new ProductEntity(new ProductId('p-10'), 'Monitor', 199.99, 'Electronics');
    $dao->create($p);

    $this->assertSame(1, $dao->count());
    $dao->delete($p);

    $this->assertSame(0, $dao->count());
    $this->assertSame(1, $dao->count(includeDeleted: true));

    $dao->restore($p);
    $this->assertSame(1, $dao->count());
  }
}
