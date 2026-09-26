<?php

namespace App\Services;

use App\Models\Tag;
use App\Support\SlugGenerator;

class TagResolver
{
    /**
     * Resolve tag names to reusable Tag IDs.
     *
     * Existing tags are reused and missing tags are created.
     *
     * @param array<int, string> $names
     * @return array<int, int>
     */
    public function resolve(array $names): array
    {
        $tagIds = [];

        foreach ($names as $name) {
            $name = trim($name);

            if ($name === '') {
                continue;
            }

            $tag = Tag::query()
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
                ->first();

            if (! $tag) {
                $tag = $this->createTag($name);
            }

            $tagIds[] = $tag->id;
        }

        return array_values(array_unique($tagIds));
    }

    private function createTag(string $name): Tag
    {
        $baseSlug = SlugGenerator::generate($name);

        if ($baseSlug === '') {
            $baseSlug = 'tag';
        }

        $slug = $baseSlug;
        $suffix = 2;

        while (
            Tag::query()
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        $tag = new Tag();
        $tag->name = $name;
        $tag->slug = $slug;
        $tag->save();

        return $tag;
    }
}
