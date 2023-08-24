# AG Grid server-side adapter for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/clickbar/ag-grid-laravel.svg?style=flat-square)](https://packagist.org/packages/clickbar/ag-grid-laravel)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/clickbar/ag-grid-laravel/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/clickbar/ag-grid-laravel/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/clickbar/ag-grid-laravel/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/clickbar/ag-grid-laravel/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/clickbar/ag-grid-laravel.svg?style=flat-square)](https://packagist.org/packages/clickbar/ag-grid-laravel)

This package implements a server-side adapter for [AG Grid](https://www.ag-grid.com/) with support for filtering, sorting, exporting and server-side selection.

## Installation

You can install the package via composer:

```bash
composer require clickbar/ag-grid-laravel
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="ag-grid-laravel-config"
```

This is the contents of the published config file:

```php
return [
    /*
     * The class that contains the provider for determining the timezone 
     * to use for DateTime formatting in exports.
     */
    'export_timezone_provider' => \Clickbar\AgGrid\AgGridDefaultExportTimezoneProvider::class
];
```

## Usage

### Querying a resource

Simply accept an `AgGridGetRowsRequest` in your controller and return an instance of `AgGridQueryBuilder` for the model that you want to query. 
Filtering, sorting and exporting is handled automatically for you. You may also pass a JSON resource to the query builder to wrap your models with.

```php
class FlamingoGridController extends Controller
{
    public function __invoke(AgGridGetRowsRequest $request): AgGridQueryBuilder
    {
        $query = Flamingo::query()
            ->with(['keeper'])
            ->orderByDesc('id');

        return AgGridQueryBuilder::forRequest($request, $query)
            ->resource(FlamingoResource::class);
    }
}
```

### Server-side select

When using AG Grid with the `serverSide` row model, you can't just pass the selected IDs to the server when performing a batch operation.
In this case, you may pass the current selection state of the grid to the server and resolve the selection there.

To do so, add the following to your request:

```php
class FeedFlamingosRequest  extends Controller
{
    public function rules(): array
    {
        return [
            'selection' => ['required', new AgGridSelection()],
            'food_type' => ['required', 'string'],
        ];
    }
}
```

In your controller, use the `AgGridQueryBuilder` to resolve the selection:

```php
class FeedFlamingosController extends FormRequest
{
    public function __invoke(FeedFlamingsRequest $request): AgGridQueryBuilder
    {
        $flamingos = AgGridQueryBuilder::forSelection($request->validated('selection'))->get();

        foreach($flamingos as $flamingo){
            $flamingo->feed($request->validated('food_type'));
        }
        
        return $flamingos;
    }
}
```

### Exports

To enable server-side exports for your models, you must implement the `AgGridExportable` interface. 
After that, you can just pass `exportFormat` as part of your request to the grid controller and the library handles transforming your models into Excel, CSV, or TSV files.

```php
class Flamingo extends Model implements AgGridExportable {

    // ... your model definitions
    
    public static function getAgGridColumnDefinitions(): array
    {
        return [
            new AgGridColumnDefinition(
                'id',
                __('ID'),
            ),
            new AgGridColumnDefinition(
                'name',
                __('Name'),
            ),
            new AgGridColumnDefinition(
                'keeper_id',
                __('Keeper'),
                null,
                fn ($data) => $data->keeper->name,
            ),
            new AgGridColumnDefinition(
                'created_at',
                __('Created At'),
                new AgGridDateFormatter(),
            ),
        ];
    }

}
```

### Custom Filters

Sometimes you may need to add custom filter scopes or other constraints to the query, 
which are not covered by the standard AG Grid filters. In this case, you may populate the `customFilters` object of the request with your own data.
On the backend side, your model must implement the `AgGridCustomFilterable` interface as shown below:

```php
class Flamingo extends Model implements AgGridCustomFilterable {

    use SoftDeletes;

    // ... your model definitions
    
    public function applyAgGridCustomFilters(Builder $query, array $params): void
    {
        $query->when($params['showTrashed'] ?? false, function ($query) {
            return $query->withTrashed();
        });
    }
}
```

## Type definitions

You may use the following Typescript type definitions as a reference 
for implementing the requests on the frontend:

```typescript
interface AgGridSelection {
    rowModel: 'serverSide' | 'clientSide'
    filterModel: any
    selectAll: boolean
    toggledNodes: (string | number)[]
    customFilters?: any
}

interface AgGridGetRowsRequest extends IServerSideGetRowsRequest {
    exportFormat?: 'excel' | 'csv' | 'tsv'
    exportCols?: string[]
    customFilters?: any
}
```

## Limitations

- Only works with PostgreSQL as a storage backend due to some special SQL operators being used in set and json queries.
- Does not support multiple conditions per filter (AND, OR)
- Does not support server-side grouping for AG Grid's pivot mode
- Filtering for values in relations is only supported one level deep. E.g you can filter for `relation.value` but not `relation.otherRelation.value` 

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [clickbar. GmbH](https://github.com/clickbar)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
