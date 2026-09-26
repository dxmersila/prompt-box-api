<?php

namespace Tests\Unit;

use App\Support\SlugGenerator;
use Tests\TestCase;

class SlugGeneratorTest extends TestCase
{
    public function test_it_generates_persian_slug_and_preserves_zwnj(): void
    {
        $this->assertSame(
            "راهنمای-ساخت-پرامپت-حرفه‌ای",
            SlugGenerator::generate("راهنمای ساخت پرامپت حرفه‌ای")
        );
    }

    public function test_it_generates_english_slug_with_numbers(): void
    {
        $this->assertSame(
            'prompt-engineering-2',
            SlugGenerator::generate('Prompt Engineering 2!')
        );
    }

    public function test_it_converts_underscores_to_hyphens(): void
    {
        $this->assertSame(
            'راهنمای-ساخت-پرامپت',
            SlugGenerator::generate('راهنمای_ساخت_پرامپت')
        );
    }

    public function test_it_collapses_whitespace_and_trims_hyphens(): void
    {
        $this->assertSame(
            'تست-فارسی',
            SlugGenerator::generate('  -- تست   فارسی -- ')
        );
    }

    public function test_it_removes_punctuation(): void
    {
        $this->assertSame(
            'سلام-دنیا-تست',
            SlugGenerator::generate('سلام، دنیا! #تست')
        );
    }

    public function test_it_returns_empty_string_for_empty_value(): void
    {
        $this->assertSame('', SlugGenerator::generate('   '));
    }
}
