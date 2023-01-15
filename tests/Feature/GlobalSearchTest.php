<?php

namespace Tests\Feature;

use App\Http\Controllers\GlobalSearch;
use Tests\TestCase;
use App\Models\Tweet;
use App\Models\Blog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GlobalSearchTest extends TestCase
{
    use RefreshDatabase;

    public function testSearchWithSearchTermLorem()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // create some tweets and blog posts in the database
        Tweet::factory()->count(5)->create();
        Blog::factory()->count(5)->create();

        // test search with search term "lorem"
        $response = $this->json('GET', route('search.global', 'lorem'));
        $response->assertStatus(200);
        $response->assertJson([
            'tweets' => Tweet::search('lorem')->get()->toArray(),
            'blog_posts' => Blog::search('lorem')->get()->toArray(),
        ]);
    }

    public function testSearchWithSearchTermIpsum()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // create some tweets and blog posts in the database
        Tweet::factory()->count(5)->create();
        Blog::factory()->count(5)->create();

        // test search with search term "ipsum"
        $response = $this->json('GET', route('search.global', 'ipsum'));
        $response->assertStatus(200);
        $response->assertJson([
            'tweets' => Tweet::search('ipsum')->get()->toArray(),
            'blog_posts' => Blog::search('ipsum')->get()->toArray(),
        ]);
    }

    public function testSearchWithEmptySearchTerm()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // create some tweets and blog posts in the database
        Tweet::factory()->count(5)->create();
        Blog::factory()->count(5)->create();

        // test search with empty search term
        $response = $this->json('GET', route('search.global', ''));
        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'Search term is required'
        ]);
    }

    public function testSearchWithSearchTermRandomText()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // create some tweets and blog posts in the database
        Tweet::factory()->count(5)->create();
        Blog::factory()->count(5)->create();

        // test search with search term "random text"
        $response = $this->json('GET', route('search.global', 'random text'));
        $response->assertStatus(200);
        $response->assertJson([
            'tweets' => Tweet::search('random text')->get()->toArray(),
            'blog_posts' => Blog::search('random text')->get()->toArray(),
        ]);
    }

    public function testSearchWithNullSearchTerm()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // create some tweets and blog posts in the database
        Tweet::factory()->count(5)->create();
        Blog::factory()->count(5)->create();

        // test search with null search term
        $response = $this->json('GET', route('search.global', ''));
        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'Search term is required'
        ]);
    }

    public function testGetTweetsResults()
    {
        $globalSearch = new GlobalSearch();

        //create some tweets in the database
        Tweet::factory()->count(5)->create(['tweet' => 'lorem']);

        //test search with search term "lorem"
        $tweets = $globalSearch->getTweetsResults("lorem");
        $this->assertCount(5, $tweets);
        $this->assertContains("lorem", $tweets->pluck("tweet")->toArray());
    }

    public function testGetBlogPostsResults()
    {
        $globalSearch = new GlobalSearch();

        //create some blog posts in the database
        Blog::factory()->count(5)->create(['title' => 'lorem']);

        //test search with search term "lorem"
        $blogPosts = $globalSearch->getBlogPostsResults("lorem");
        $this->assertCount(5, $blogPosts);
        $this->assertContains("lorem", $blogPosts->pluck("title")->toArray());
    }

    public function testGetBlogPostsResultsWithEmptySearchTerm(){
        $globalSearch = new GlobalSearch();

        //test search with empty search term
        $blogPosts = $globalSearch->getBlogPostsResults("");
        $this->assertEmpty($blogPosts);
    }

    public function testGetBlogPostsResultsWithNullSearchTerm(){
        $globalSearch = new GlobalSearch();

        //test search with null search term
        $blogPosts = $globalSearch->getBlogPostsResults(null);
        $this->assertEmpty($blogPosts);
    }

    public function testGetTweetsResultsWithEmptySearchTerm()
    {

        $globalSearch = new GlobalSearch();

        //test search with empty search term
        $tweets = $globalSearch->getTweetsResults("");
        $this->assertEmpty($tweets);
    }

    public function testGetTweetsResultsWithNullSearchTerm()
    {

        $globalSearch = new GlobalSearch();

        //test search with null search term
        $tweets = $globalSearch->getTweetsResults(null);
        $this->assertEmpty($tweets);
    }

}



