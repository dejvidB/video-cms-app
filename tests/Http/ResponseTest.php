<?php

declare(strict_types=1);

namespace Tests\Http;

use App\Http\Response;
use App\Models\Model;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    public function testReturnsRawArray(): void
    {
        $data = ['hello' => 'world'];

        $response = new Response($data);

        $this->assertEquals($data, $response->getData());
        $this->assertEquals(200, $response->getStatus());
    }

    public function testTransformsSingleModel(): void
    {
        $model = $this->createMock(Model::class);
        $model->method('toArray')->willReturn(
            [
                'id' => 1,
                'title' => 'Test Model'
            ]
        );

        $response = new Response($model);

        $this->assertEquals(
            [
                'id' => 1,
                'title' => 'Test Model'
            ],
            $response->getData()
        );
    }

    public function testTransformsArrayOfModels(): void
    {
        $model1 = $this->createMock(Model::class);
        $model2 = $this->createMock(Model::class);

        $model1->method('toArray')->willReturn(['id' => 1]);
        $model2->method('toArray')->willReturn(['id' => 2]);

        $response = new Response([$model1, $model2]);

        $this->assertEquals(
            [
                  ['id' => 1],
                  ['id' => 2]
              ],
            $response->getData()
        );
    }

    public function testTransformsNestedModels(): void
    {
        $model1 = $this->createMock(Model::class);
        $model2 = $this->createMock(Model::class);

        $model1->method('toArray')->willReturn(['id' => 1]);
        $model2->method('toArray')->willReturn(['id' => 2]);

        $nested = [
            'videos' => [$model1, $model2],
            'meta' => ['total' => 2]
        ];

        $response = new Response($nested);

        $this->assertEquals(
            [
                'videos' => [
                    ['id' => 1],
                    ['id' => 2]
                ],
                'meta' => ['total' => 2]
            ],
            $response->getData()
        );
    }
}
