<?php

namespace Feature;

use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CsvControllerTest extends TestCase
{
    public function testStoreReturnsExpectedData()
    {
        $this->post(route('csv.store'), [
            'file' => UploadedFile::fake()->create("{$this->faker->word}.csv"),
        ])
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('index')
            ->assertViewHas(['columns', 'people']);
    }
}
