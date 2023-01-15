<?php

namespace App\Http\Controllers;

use App\Models\Tweet;
use App\Models\Blog;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Collection;

class GlobalSearch extends Controller
{

    /**
     * Search for all the entities by search term
     *
     * @param string|null $searchTerm
     * @return JsonResponse
     */
    public function search(string $searchTerm = null): JsonResponse
    {

        if(empty($searchTerm) || is_null($searchTerm)) {
            return response()->json([
                'message' => 'Search term is required'
            ], 400);
        }

        $tweets = $this->getTweetsResults($searchTerm);
        $blogPosts = $this->getBlogPostsResults($searchTerm);

        return response()->json([
            'tweets' => $tweets,
            'blog_posts' => $blogPosts,
        ], 200);
    }

    /**
     * Search for tweets that match the search term
     *
     * @param string|null $searchTerm
     * @return Collection
     */
    public function getTweetsResults(string $searchTerm = null): Collection
    {
        return Tweet::search($searchTerm)->get();
    }

    /**
     * Search for blog posts that match the search term
     *
     * @param string|null $searchTerm
     * @return Collection
     */
    public function getBlogPostsResults(string $searchTerm = null): Collection
    {
        return Blog::search($searchTerm)->get();
    }
}
