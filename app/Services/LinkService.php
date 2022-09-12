<?php

namespace App\Services;

use App\Models\Link;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class LinkService
{
    private function tagQuery(array $tagNames): Builder
    {
        return Tag::query()->whereIn('name', $tagNames);
    }

    private function formatLinkData(array $rawLinkData): array {
        return [
            ...Arr::except($rawLinkData, ['tags']),
            'shortened_uri' => hash('crc32b', $rawLinkData['long_url'])
        ];
    }

    public function syncTags(array $linkDatas): Collection {
        $allTags = [];
        foreach ($linkDatas as $link) {
            $allTags = [...$allTags, ...$link['tags']];
        }
        $allTags = array_values(array_unique($allTags));
        $existingTags = $this->tagQuery($allTags)->get();

        if ($existingTags->count() < count($allTags)) {
            Tag::query()->insert(array_map(
                static fn($item) => ['name' => $item],
                array_diff(
                    $allTags,
                    $existingTags->pluck('name')->toArray()
                )
            ));
        }
        return $this->tagQuery($allTags)->get()->pluck('id', 'name');
    }

    public function createLink(array $linkData, Collection $allTags): Link
    {
        /** @var Link $linkInstance */
        $linkInstance = Link::query()->create($this->formatLinkData($linkData));
        $linkTagIds = $allTags->filter(
            fn($id, $name) => in_array($name, $linkData['tags'], true)
        );
        $linkInstance->tags()->sync($linkTagIds);
        $linkInstance->refresh()->load('tags');
        return $linkInstance;
    }

    public function updateLink(Link $link, array $linkData): Link {
        $link->update($this->formatLinkData($linkData));
        $tagIds = $this->syncTags([$linkData]);
        $link->tags()->sync($tagIds);
        $link->refresh();
        return $link;
    }
}
