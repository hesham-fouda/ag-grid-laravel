<?php

use HeshamFouda\AgGrid\AgGridQueryBuilder;
use HeshamFouda\AgGrid\Tests\TestClasses\Models\Flamingo;
use HeshamFouda\AgGrid\Tests\TestClasses\Models\Keeper;
use Illuminate\Http\Request;
use Illuminate\Testing\TestResponse;

beforeEach(function () {
    $this->keeper = Keeper::factory()->createOne();
    $this->flamingos = Flamingo::factory()->count(2)->for($this->keeper)->create();
});

it('handles excel exports correctly', function () {
    $queryBuilder = new AgGridQueryBuilder(
        [
            'exportFormat' => 'excel',
        ],
        Flamingo::class,
    );

    $response = TestResponse::fromBaseResponse($queryBuilder->toResponse(new Request));

    $response->assertDownload('export.xlsx');
});

it('handles csv exports correctly', function () {
    $queryBuilder = new AgGridQueryBuilder(
        [
            'exportFormat' => 'csv',
        ],
        Flamingo::class,
    );

    $response = TestResponse::fromBaseResponse($queryBuilder->toResponse(new Request));

    $response->assertDownload('export.csv');
});

it('handles tsv exports correctly', function () {
    $queryBuilder = new AgGridQueryBuilder(
        [
            'exportFormat' => 'tsv',
        ],
        Flamingo::class,
    );

    $response = TestResponse::fromBaseResponse($queryBuilder->toResponse(new Request));

    $response->assertDownload('export.csv');
});

it('only exports selected columns', function () {
    $queryBuilder = new AgGridQueryBuilder(
        [
            'exportFormat' => 'csv',
            'exportColumns' => ['id'],
        ],
        Flamingo::class,
    );

    $response = TestResponse::fromBaseResponse($queryBuilder->toResponse(new Request));

    $response->assertDownload('export.csv');
});

it('does not crash when a non-existing column is requested', function () {
    $queryBuilder = new AgGridQueryBuilder(
        [
            'exportFormat' => 'csv',
            'exportColumns' => ['id', 'does_not_exist'],
        ],
        Flamingo::class,
    );

    $response = TestResponse::fromBaseResponse($queryBuilder->toResponse(new Request));

    $response->assertDownload('export.csv');
});
