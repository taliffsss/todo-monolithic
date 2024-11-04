<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tag\TagStoreRequest;
use App\Http\Requests\Tag\TagUpdateRequest;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use App\Services\TagService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TagController extends Controller
{
    public function __construct(protected TagService $tagService) {}

    /**
     * Get all tags
     */
    public function index(): ResourceCollection
    {
        $tags = $this->tagService->list();

        return TagResource::collection($tags);
    }

    /**
     * Create a new tag
     */
    public function store(TagStoreRequest $request): JsonResource
    {
        $tag = $this->tagService->create($request->validated());

        return new TagResource($tag);
    }

    /**
     * Update the specified tag
     */
    public function update(TagUpdateRequest $request, Tag $tag): JsonResource
    {
        $tag = $this->tagService->update($tag, $request->validated());

        return new TagResource($tag);
    }

    /**
     * Delete the specified tag
     */
    public function destroy(Tag $tag): JsonResponse
    {
        if ($this->tagService->delete($tag)) {
            return response()->json(['message' => 'Tag deleted successfully']);
        }

        return response()->json(['message' => 'Tag cannot be deleted'], 422);
    }

    /**
     * Search tags
     */
    public function search(Request $request): ResourceCollection
    {
        $request->validate([
            'query' => 'required|string|min:1',
        ]);

        $tags = $this->tagService->search($request->query);

        return TagResource::collection($tags);
    }
}
