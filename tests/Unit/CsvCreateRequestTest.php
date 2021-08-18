<?php

namespace Unit;

use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CsvCreateRequestTest extends TestCase
{
    /**
     * @return array[]
     */
    public function filesProvider(): array
    {
        return [
            [
                'csv',
                Response::HTTP_OK,
                null,
            ],
            [
                'png',
                Response::HTTP_FOUND,
                [
                    "file" => "The file must be a file of type: csv."
                ],
            ],
            [
                'jpg',
                Response::HTTP_FOUND,
                [
                    "file" => "The file must be a file of type: csv."
                ],
            ],
            [
                'docx',
                Response::HTTP_FOUND,
                [
                    "file" => "The file must be a file of type: csv."
                ],
            ],
        ];
    }

    /**
     * @dataProvider filesProvider
     * @param string $extension
     * @param int $response
     * @param null|array $errors
     */
    public function testItCanHandleCsvFile(string $extension, int $response, ?array $errors): void
    {
        $response = $this->post(route('csv.store'), [
           'file' => UploadedFile::fake()->create("{$this->faker->word}.{$extension}"),
        ])
            ->assertStatus($response);

        if ($errors) {
            $response->assertSessionHasErrors($errors);
        }
    }
}
