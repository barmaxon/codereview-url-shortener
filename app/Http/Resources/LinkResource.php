<?php

namespace App\Http\Resources;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $long_url
 * @property string $shortened_uri
 * @property string $title
 * @property Tag[]|Collection $tags
 */
class LinkResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'long_url' => $this->long_url,
            'shortened_uri' => $this->shortened_uri,
            'title' => $this->title,
            'tags' => $this->tags->implode('name', ','),
        ];
    }
}
