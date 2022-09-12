<?php

namespace App\Http\Controllers;

use App\Http\Requests\LinkRequest;
use App\Http\Resources\LinkResource;
use App\Models\Link;
use App\Services\LinkService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LinkController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return LinkResource::collection(Link::query()->with('tags')->simplePaginate(15));
    }

    public function store(LinkRequest $request, LinkService $service): void
    {
        $links = $request->manyLinksProvided ? $request->all() : [$request->all()];

        $tags = $service->syncTags($links);

        foreach ($links as $link) {
            $service->createLink($link, $tags);
        }
    }

    public function show(Link $link): LinkResource
    {
        return new LinkResource($link);
    }

    public function update(LinkRequest $request, Link $link, LinkService $service): LinkResource
    {
        return new LinkResource($service->updateLink($link, $request->all()));
    }

    public function destroy(Link $link): void
    {
        $link->delete();
    }
}
