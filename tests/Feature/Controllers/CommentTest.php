<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\Comment;
use App\Models\Tweet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    /**
     * test storing comments in db with valid input
     * there should not be any validation errors
     * comment should be created in db with type Blog
     * @return void
     */
    public function testCommentStoredWithCommentableTypeBlog(): void
    {
        //create a random post
        $post = Blog::factory()->create(["user_id" => $this->user->id]);

        //prepare data for the json post
        $data = [
            "comment" => "this is a nice comment",
            "commentable_id" => $post->id,
            "commentable_type" => "blogs",
            "user_id" => $this->user->id
        ];

        //fake a post request to the route
        $response = $this->postJson(route("comments.store"), $data);

        //response should be 200
        $response->assertStatus(200);


        //there shouldnt be any validation errors
        $response->assertJsonMissingValidationErrors(
            [
                "comment",
                "commentable_id",
                "commentable_type",
                "user_id"
            ],
            "errors");

        //comment should be created in the db
        $this->assertDatabaseHas("comments", [
            "comment" => $data["comment"],
            "commentable_id" => $post->id,
            "commentable_type" => Blog::class,
            "user_id" => $this->user->id
        ]);
    }


    /**
     * test storing comments in db with valid input
     * there should not be any validation errors
     * comment should be created in db with type Tweet
     * @return void
     */
    public function testCommentStoredWithCommentableTypeTweet(): void
    {
        //create a random tweet
        $tweet = Tweet::factory()->create(["user_id" => $this->user->id]);

        //prepare data for the json post
        $data = [
            "comment" => "this is a tweet comment",
            "commentable_id" => $tweet->id,
            "commentable_type" => "tweets",
            "user_id" => $this->user->id
        ];

        //fake a post request to the route
        $response = $this->postJson(route("comments.store"), $data);

        //response should be 200
        $response->assertStatus(200);

        //there should not be any validation errors
        $response->assertJsonMissingValidationErrors(
            [
                "comment",
                "commentable_id",
                "commentable_type",
                "user_id"
            ],
            "errors");

        //comment should be created in the db
        $this->assertDatabaseHas("comments", [
            "comment" => $data["comment"],
            "commentable_id" => $tweet->id,
            "commentable_type" => Tweet::class,
            "user_id" => $this->user->id
        ]);
    }


    /**
     * pass the invalid commentable_type
     * expecting validation errors
     * @return void
     */
    public function testCommentStoreWithCommentableTypeInvalid(): void
    {

        //create a post
        $post = Blog::factory()->create();

        //prepare testing data
        $data = [
            "comment" => "some nice comment",
            "commentable_id" => $post->id,
            "commentable_type" => "invalid",
            "user_id" => $this->user->id
        ];

        //make the json request
        $response = $this->postJson(route("comments.store"), $data);
        //there should be validation errors
        $response->assertJsonValidationErrors(["commentable_type"], "errors");
        $this->assertDatabaseMissing(Comment::class, $data);


        //create a tweet
        $tweet = Tweet::factory()->create();

        $data = [
            "comment" => "this is tweet comment",
            "commentable_id" => $tweet->id,
            "commentable_type" => "something",
            "user_id" => $this->user->id
        ];

        $response = $this->postJson(route("comments.store"), $data);
        $response->assertJsonValidationErrors(["commentable_type"], "errors");
        $this->assertDatabaseMissing(Comment::class, $data);
    }


    /**
     * pass the invalid data
     * expecting validation errors
     * @return void
     */
    public function testCommentStoreWithInvalidData(): void
    {
        $response = $this->postJson(route("comments.store"), []);
        $response->assertJsonValidationErrors(["comment", "commentable_type", "commentable_id"]);

        $data = [
            "comment" => "a",
            "commentable_id" => "invalid",
            "commentable_type" => "blogs"
        ];
        $response = $this->postJson(route("comments.store"), $data);
        $response->assertJsonValidationErrors(["comment", "commentable_id"]);
        $this->assertDatabaseMissing(Comment::class, $data);
    }

    /**
     * check for valid json response
     * @return void
     */
    public function testCommentStoreReturnsProperJsonResponse(): void
    {
        $tweet = Tweet::factory()->create();

        $data = [
            "comment" => "I am a comment",
            "commentable_id" => $tweet->id,
            "commentable_type" => "tweets",
            "user_id" => $this->user->id
        ];

        $response = $this->postJson(route("comments.store"), $data);

        $response->assertJsonMissingValidationErrors(["comment", "commentable_id", "commentable_type", "user_id"]);
        $response->assertJsonFragment([
            "comment" => "I am a comment",
            "commentable_id" => $tweet->id,
            "commentable_type" => Tweet::class,
            "user_id" => $this->user->id
        ]);
    }


    /**
     * test with valid data
     * @return void
     */
    public function testGetCommentsForResourceReturnsValidJsonResponse(): void
    {
        //create a post
        $post = Blog::factory()->create();
        $postId = $post->id;
        $type = "blogs";

        //create some comments for the post
        $comments = Comment::factory()->count(3)->create([
            "commentable_id" => $postId,
            "commentable_type" => Blog::class,
            "user_id" => $this->user->id
        ]);

        //make a get request to get the comments
        $response = $this->getJson(route("resource.single.comments", [
            "post_id" => $postId,
            "type" => $type
        ]));

        //there should not be any errors in the response
        $response->assertJsonMissingValidationErrors(["commentable_id", "commentable_type", "comment", "post_id", "type"]);

        //assert returned json has valid data
        $response->assertJsonFragment([
            "email" => $this->user->email,
            "name" => $this->user->name,
            "username" => $this->user->username,
            "commentable_id" => $postId,
            "commentable_type" => Blog::class,
            "comment" => $comments->first()->comment
        ]);
    }


    /**
     * test with invalid data
     * @return void
     */
    public function testGetCommentsForResourceWithInvalidData(): void
    {
        $data = [
            "post_id" => "invalid",
            "type" => "invalid"
        ];

        $response = $this->getJson(route("resource.single.comments", $data));

        //assert json has validation errors
        $response->assertJsonValidationErrors(["post_id", "type"]);
    }
}
