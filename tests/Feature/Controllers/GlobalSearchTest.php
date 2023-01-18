<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tweet;
use App\Models\Blog;
use App\Models\User;
use App\Http\Controllers\GlobalSearch;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GlobalSearchTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * test search function with random text Lorem
     * @return void
     */
    public function testSearchWithSearchTermLorem(): void
    {
        $this->actingAs($this->user);

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


    /**
     * test search function with random text Ipsum
     * @return void
     */
    public function testSearchWithSearchTermIpsum(): void
    {
        $this->actingAs($this->user);

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

    /**
     * test search function with empty search term
     * @return void
     */
    public function testSearchWithEmptySearchTerm(): void
    {
        $this->actingAs($this->user);

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


    /**
     * * test search function with random text
     * @return void
     */
    public function testSearchWithSearchTermRandomText(): void
    {
        $this->actingAs($this->user);

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


    /**
     * test search function with Null
     * @return void
     */
    public function testSearchWithNullSearchTerm(): void
    {
        $this->actingAs($this->user);

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

    /**
     * test the tweets function
     * @return void
     */
    public function testGetTweetsResults():void
    {
        $globalSearch = new GlobalSearch();

        //create some tweets in the database
        Tweet::factory()->count(5)->create(['tweet' => 'lorem']);

        //test search with search term "lorem"
        $tweets = $globalSearch->getTweetsResults("lorem");
        $this->assertCount(5, $tweets);
        $this->assertContains("lorem", $tweets->pluck("tweet")->toArray());
    }


    /**
     * test blog posts results
     * @return void
     */
    public function testGetBlogPostsResults(): void
    {
        $globalSearch = new GlobalSearch();

        //create some blog posts in the database
        Blog::factory()->count(5)->create(['title' => 'lorem']);

        //test search with search term "lorem"
        $blogPosts = $globalSearch->getBlogPostsResults("lorem");
        $this->assertCount(5, $blogPosts);
        $this->assertContains("lorem", $blogPosts->pluck("title")->toArray());
    }

    /**
     * test blog search results with empty value
     * @return void
     */
    public function testGetBlogPostsResultsWithEmptySearchTerm(): void
    {
        $globalSearch = new GlobalSearch();

        //test search with empty search term
        $blogPosts = $globalSearch->getBlogPostsResults("");
        $this->assertEmpty($blogPosts);
    }

    /**
     * test blog posts results with Null value
     * @return void
     */
    public function testGetBlogPostsResultsWithNullSearchTerm(): void
    {
        $globalSearch = new GlobalSearch();

        //test search with null search term
        $blogPosts = $globalSearch->getBlogPostsResults(null);
        $this->assertEmpty($blogPosts);
    }

    /**
     * test tweets result function with empty value
     * @return void
     */
    public function testGetTweetsResultsWithEmptySearchTerm(): void
    {
        $globalSearch = new GlobalSearch();

        //test search with empty search term
        $tweets = $globalSearch->getTweetsResults("");
        $this->assertEmpty($tweets);
    }


    /**
     * test tweets results with Null value
     * @return void
     */
    public function testGetTweetsResultsWithNullSearchTerm(): void
    {
        $globalSearch = new GlobalSearch();

        //test search with null search term
        $tweets = $globalSearch->getTweetsResults(null);
        $this->assertEmpty($tweets);
    }

}



