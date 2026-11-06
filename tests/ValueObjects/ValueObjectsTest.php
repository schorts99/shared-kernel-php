<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\Tests\ValueObjects;

use PHPUnit\Framework\TestCase;
use Schorts\SharedKernel\ValueObjects\ArrayValue;
use Schorts\SharedKernel\ValueObjects\BooleanValue;
use Schorts\SharedKernel\ValueObjects\CoordinatesValue;
use Schorts\SharedKernel\ValueObjects\DateValue;
use Schorts\SharedKernel\ValueObjects\EmailValue;
use Schorts\SharedKernel\ValueObjects\EnumValue;
use Schorts\SharedKernel\ValueObjects\FloatValue;
use Schorts\SharedKernel\ValueObjects\HtmlValue;
use Schorts\SharedKernel\ValueObjects\IntegerValue;
use Schorts\SharedKernel\ValueObjects\MarkdownValue;
use Schorts\SharedKernel\ValueObjects\ObjectValue;
use Schorts\SharedKernel\ValueObjects\PathValue;
use Schorts\SharedKernel\ValueObjects\PhoneValue;
use Schorts\SharedKernel\ValueObjects\SlugValue;
use Schorts\SharedKernel\ValueObjects\StringValue;
use Schorts\SharedKernel\ValueObjects\URLValue;
use Schorts\SharedKernel\ValueObjects\UUIDValue;

final class ValueObjectsTest extends TestCase
{
  public function testStringValue(): void
  {
    $vo = new class('Hello World') extends StringValue {
      public function getAttributeName(): string { return 'title'; }
    };

    $this->assertTrue($vo->isValid());
    $this->assertSame('Hello World', $vo->getValue());
    $this->assertSame('Hello World', (string) $vo);
    $this->assertSame('"Hello World"', json_encode($vo));
  }

  public function testEmailValue(): void
  {
    $valid = new class('test@example.com') extends EmailValue {
      public function getAttributeName(): string { return 'email'; }
    };
    $invalid = new class('not-an-email') extends EmailValue {
      public function getAttributeName(): string { return 'email'; }
    };

    $this->assertTrue($valid->isValid());
    $this->assertFalse($invalid->isValid());
  }

  public function testUUIDValue(): void
  {
    $valid = new class('a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d') extends UUIDValue {
      public function getAttributeName(): string { return 'uuid'; }
    };
    $invalid = new class('invalid-uuid') extends UUIDValue {
      public function getAttributeName(): string { return 'uuid'; }
    };

    $this->assertTrue($valid->isValid());
    $this->assertFalse($invalid->isValid());
  }

  public function testHtmlValue(): void
  {
    $valid = new class('<p>Hello <strong>World</strong></p>') extends HtmlValue {
      public function getAttributeName(): string { return 'content'; }
    };
    $invalid = new class('Just plain text without tags') extends HtmlValue {
      public function getAttributeName(): string { return 'content'; }
    };

    $this->assertTrue($valid->isValid());
    $this->assertFalse($invalid->isValid());
  }

  public function testMarkdownValue(): void
  {
    $valid = new class('# Heading 1 with **bold**') extends MarkdownValue {
      public function getAttributeName(): string { return 'markdown'; }
    };
    $invalid = new class('Plain text without markdown syntax') extends MarkdownValue {
      public function getAttributeName(): string { return 'markdown'; }
    };

    $this->assertTrue($valid->isValid());
    $this->assertFalse($invalid->isValid());
  }

  public function testPathValue(): void
  {
    $valid = new class('docs/guide.md', ['docs']) extends PathValue {
      public function getAttributeName(): string { return 'path'; }
    };
    $traversal = new class('docs/../../etc/passwd', ['docs']) extends PathValue {
      public function getAttributeName(): string { return 'path'; }
    };

    $this->assertTrue($valid->isValid());
    $this->assertFalse($traversal->isValid());
  }

  public function testIntegerValue(): void
  {
    $vo = new class(42, 10, 50) extends IntegerValue {
      public function getAttributeName(): string { return 'age'; }
    };

    $this->assertTrue($vo->isValid());
    $this->assertSame(42, $vo->getValue());
  }

  public function testBooleanValue(): void
  {
    $vo = new class(true) extends BooleanValue {
      public function getAttributeName(): string { return 'is_active'; }
    };

    $this->assertTrue($vo->isValid());
    $this->assertTrue($vo->getValue());
  }

  public function testCoordinatesValue(): void
  {
    $valid = new class(['latitude' => 40.7128, 'longitude' => -74.0060]) extends CoordinatesValue {
      public function getAttributeName(): string { return 'coords'; }
    };
    $invalid = new class(['latitude' => 120.0, 'longitude' => -74.0060]) extends CoordinatesValue {
      public function getAttributeName(): string { return 'coords'; }
    };

    $this->assertTrue($valid->isValid());
    $this->assertFalse($invalid->isValid());
  }

  public function testArrayValueValidatesRequiredFieldsInMissingNestedObjects(): void
  {
    $value = new class([['id' => 1]], ['profile' => ['name' => [['required' => true]]]]) extends ArrayValue {
      public function getAttributeName(): string { return 'users'; }
      public function isPrimitive(): bool { return false; }
    };

    $this->assertFalse($value->isValid());
  }

  public function testArrayValueAllowsMissingOptionalNestedObjects(): void
  {
    $value = new class([['id' => 1]], ['profile' => ['nickname' => [['type' => 'string']]]]) extends ArrayValue {
      public function getAttributeName(): string { return 'users'; }
      public function isPrimitive(): bool { return false; }
    };

    $this->assertTrue($value->isValid());
  }

  public function testObjectValueValidatesRequiredFieldsInMissingNestedObjects(): void
  {
    $schema = ['profile' => ['name' => [['required' => true]]]];
    $value = new class(['profile' => []], $schema) extends ObjectValue {
      public function getAttributeName(): string { return 'user'; }
    };
    $missingParent = new class([], $schema) extends ObjectValue {
      public function getAttributeName(): string { return 'user'; }
    };

    $this->assertFalse($value->isValid());
    $this->assertFalse($missingParent->isValid());
  }
}
