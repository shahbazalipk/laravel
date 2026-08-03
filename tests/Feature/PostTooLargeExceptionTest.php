<?php

namespace Tests\Feature;

use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PostTooLargeExceptionTest extends TestCase
{
    #[Test]
    public function post_too_large_exception_redirects_back_with_friendly_message(): void
    {
        Route::post('/__test/post-too-large', function () {
            throw new PostTooLargeException('The POST data is too large.');
        })->middleware('web');

        $this->from('/online/tech-trip/form')
            ->post('/__test/post-too-large', ['email' => 'test@example.com'])
            ->assertRedirect('/online/tech-trip/form')
            ->assertSessionHas('error');
    }
}
