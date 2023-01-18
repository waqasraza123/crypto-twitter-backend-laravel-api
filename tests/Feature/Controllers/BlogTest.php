<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * Pass the correct data
     * and see if the post gets created in the db
     * @return void
     */
    public function testStorePostWorksWithValidData(): void
    {
        //generate data to test with
        $data = [
            "title" => "Random Title",
            "content" => "Post Content"
        ];

        //make a post request to the route
        $response = $this->actingAs($this->user)
            ->json("POST", route("posts.store"), $data);

        //response should be 200
        $response->assertStatus(200);
        $response->assertJson([
            'message' => "Post created",
        ]);

        // Assert that no validation errors are present...
        $response->assertValid();

        // Assert that the given keys do not have validation errors...
        $response->assertValid(['title', 'content']);

        //check if the post is created in the db
        $this->assertDatabaseHas("blogs", [
            'title' => $data['title'],
            'content' => $data['content'],
            'user_id' => $this->user->id,
        ]);
    }


    /**
     * test with invalid data
     * there must be validation errors
     * post should not be created
     * @return void
     */
    public function testStorePostWithEmptyStrings(): void
    {
        //create test data
        $data = [
            "title" => "",
            "content" => ""
        ];

        //make a post request with the data
        $response = $this->actingAs($this->user)
            ->json("POST", route("posts.store"), $data);

        //check if there are validation errors
        $response->assertJsonValidationErrors([
            "title" => "The title field is required",
            "content" => "The content field is required."
        ], "errors");

        //post should not be created in the db
        $this->assertDatabaseMissing("blogs", [
            "title" => "",
            "content" => "",
            "user_id" => ""
        ]);
    }

    /**
     * test with null data
     * there must be validation errors
     * post should not be created
     * @return void
     */
    public function testStorePostWithNullData(): void
    {
        //create test data
        $data = [
            "title" => null,
            "content" => null
        ];

        //make a post request with the data
        $response = $this->actingAs($this->user)
            ->json("POST", route("posts.store"), $data);

        //check if there are validation errors
        $response->assertJsonValidationErrors([
            "title" => "The title field is required",
            "content" => "The content field is required."
        ], "errors");

        //post should not be created in the db
        $this->assertDatabaseMissing("blogs", [
            "title" => null,
            "content" => null,
            "user_id" => null
        ]);
    }

    /**
     * test for invalid title
     * there must be validation errors
     * @return void
     */
    public function testStorePostWithInvalidTitle(): void
    {
        $data = [
            "title" => "a",
            "content" => "some content here"
        ];

        $response = $this->actingAs($this->user)
            ->json("POST", route("posts.store"), $data);


        //check for validation error for title
        $response->assertStatus(422)
            ->assertJsonValidationErrors(["title"], "errors");


        $data = [
            "title" => "This is going to be a very very very
            long title that exceeds the 100 characters limit.
            that exceeds the 100 characters limit.",
            "content" => "some content here."
        ];

        $response = $this->actingAs($this->user)
            ->json("POST", route("posts.store"), $data);

        //check for validation error for title
        //"The title must not be greater than 100 characters."
        $response->assertStatus(422)
            ->assertJsonValidationErrors(["title" => "The title must not be greater than 100 characters."], "errors");
    }

    /**
     * test for invalid title
     * there must be validation errors
     * @return void
     */
    public function testStorePostWithInvalidContent(): void
    {
        $data = [
            "title" => "Hello Post",
            "content" => "s"
        ];

        $response = $this->actingAs($this->user)
            ->json("POST", route("posts.store"), $data);


        //check for validation error for title
        $response->assertStatus(422)
            ->assertJsonValidationErrors(["content"], "errors");

        //create a string with 1001 characters
        $data = [
            "title" => "Hello Title",
            "content" => str_repeat('ad 1', 1001)
        ];

        $response = $this->actingAs($this->user)
            ->json("POST", route("posts.store"), $data);

        //check for validation error for title
        //"The title must not be greater than 100 characters."
        $response->assertStatus(422)
            ->assertJsonValidationErrors(["content" => "The content must not be greater than 1000 characters."], "errors");
    }


    /**
     * test for the single post
     * @return void
     */
    public function testSinglePostReturned(): void
    {
        //create a post
        $post = Blog::factory()->create();

        //make get request to fetch that post
        $response = $this->actingAs($this->user)
            ->json('GET', route("posts.single", ["id" => $post->id]));

        //post should be returned
        //status should be 200
        $response->assertStatus(200)
            ->assertJsonFragment(["id" => $post->id]);

        //assert that database has the above post
        $this->assertDatabaseHas("blogs", [
            "id" => $post->id
        ]);
    }


    /**
     * pass an invalid id that does not exist
     * @return void
     */
    public function testSinglePostValidationError(): void
    {
        $response = $this->actingAs($this->user)
            ->json('GET', route("posts.single", ["id" => -10000 ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(["id" => "The selected id is invalid."]);
    }


    /**
     * single post should load its user, comments and comments' user
     * @return void
     */
    public function testSinglePostIncludesAuthorAndComments(): void
    {
        $post = Blog::factory()->create(["user_id" => $this->user->id]);

        Comment::factory()->count(3)->create([
            "commentable_id" => $post->id,
            "commentable_type" => Blog::class,
            "user_id" => $this->user->id
        ]);

        $response = $this->actingAs($this->user)
            ->json('GET', route("posts.single", ["id" => $post->id]));

        //expected response is 200
        $response->assertStatus(200);

        //get the json from the response object
        $jsonResponse = $response->json();

        //the response json should have post id that
        //matches the id of the post we created above
        //and there should be a user_id that matches the post user id

        $response->assertJsonFragment(
            [
                "id" => $post->id, //correct post is returned
                "user_id" => $post->user->id, //post belongs to the correct user
                "user" => $jsonResponse["post"]["user"], //post has user object
                "email" => $jsonResponse["post"]["user"]["email"] //post has correct user object
            ]
        );

        //post is attached to the correct user
        $this->assertEquals($post->user->id, $this->user->id);

        //check if correct number of comments are returned
        $this->assertCount(3, $post->comments);

        //author of the comment must be the correct user
        $this->assertEquals(
            $post->comments[0]->user->id,
            //get post -> comments -> first comment -> its user -> user id
            $jsonResponse["post"]["comments"][0]["user"]["id"]);


        //check the database for correct entries
        //check for comments
        $this->assertDatabaseHas("comments", [
            "commentable_id" => $post->id,
            "commentable_type" => Blog::class,
            "user_id" => $this->user->id
        ]);

        //check for post
        $this->assertDatabaseHas("blogs", [
            "id" => $post->id,
            "user_id" => $this->user->id
        ]);
    }

}
