<?php

namespace Tests\Feature\Controllers;

use App\Models\Like;
use App\Models\Tweet;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LikeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * setup
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    /**
     * test store like action with a valid tweet
     * @return void
     */
    public function testStoreLikeWithValidData(): void
    {
        //create a tweet to test like with
        $tweet = Tweet::factory()->create();

        $response = $this->postJson(route("likes.store"), ["tweet_id" => $tweet->id]);

        //assert correct tweet was liked
        $response->assertJsonMissingValidationErrors(["tweet_id"]);

        //assert tweet was liked and correct response was returned
        $response->assertJsonFragment([
            "liked" => true,
        ]);

        //assert like table has the data
        $this->assertDatabaseHas(Like::class, [
            "tweet_id" => $tweet->id, //current user
            "user_id" => $this->user->id //current tweet
        ]);

        //make post request with same data to unlike the tweet
        $response = $this->postJson(route("likes.store"), ["tweet_id" => $tweet->id]);

        //assert correct tweet was liked
        $response->assertJsonMissingValidationErrors(["tweet_id"]);

        //assert tweet was liked and correct response was returned
        $response->assertJsonFragment([
            "liked" => false,
        ]);

        //assert like table has no row with this data
        $this->assertDatabaseMissing(Like::class, [
            "tweet_id" => $tweet->id, //current user
            "user_id" => $this->user->id //current tweet
        ]);
    }


    /**
     * invalid id test
     * @return void
     */
    public function testStoreLikeMethodWithInvalidTweetId(): void
    {
        $tweetId = 99999999;

        //make request to like this tweet
        $response = $this->postJson(route("likes.store"), ["tweet_id" => $tweetId]);

        //there should be validation error for invalid tweet id
        $response->assertJsonValidationErrors(["tweet_id" => "The selected tweet id is invalid."]);

        //assert like model does not have this tweet record
        $this->assertDatabaseMissing(Like::class, [
            "tweet_id" => $tweetId,
            "user_id" => $this->user->id
        ]);
    }

    /**
     * test with null as tweet id
     * @return void
     */
    public function testStoreLikeWithNullTweetId(): void
    {
        $tweetId = null;

        //make request to like this tweet
        $response = $this->postJson(route("likes.store"), ["tweet_id" => $tweetId]);

        //there should be validation error for invalid tweet id
        $response->assertJsonValidationErrors(["tweet_id" => "The tweet id field is required."]);

        //assert like model does not have this tweet record
        $this->assertDatabaseMissing(Like::class, [
            "tweet_id" => $tweetId,
            "user_id" => $this->user->id
        ]);

        //make request to the route without any params
        $response = $this->postJson(route("likes.store"));

        //there should be validation error for invalid tweet id
        $response->assertJsonValidationErrors(["tweet_id" => "The tweet id field is required."]);

        //assert like model does not have this record
        $this->assertDatabaseMissing(Like::class, [
            "tweet_id" => $tweetId,
            "user_id" => $this->user->id
        ]);
    }

}
