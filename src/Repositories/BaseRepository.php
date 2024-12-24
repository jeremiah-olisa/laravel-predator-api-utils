<?php

namespace LaravelPredatorApiUtils\Repositories;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


abstract class BaseRepository
{
    protected Model $model;
    public static array $allowedOperators = ['=', '!=', '>', '<', '>=', '<=', 'like'];
    public static array $allowedTextOperators = ['eq', 'ne', 'gt', 'lt', 'gte', 'lte', 'like'];
    // Map of standard operators to text equivalents
    public static array $operatorMapping = [
        '=' => 'eq',
        '!=' => 'ne',
        '>' => 'gt',
        '<' => 'lt',
        '>=' => 'gte',
        '<=' => 'lte',
        'like' => 'like',
    ];

    // Reverse mapping for text to standard operators
    public static array $reverseOperatorMapping = [
        'eq' => '=',
        'ne' => '!=',
        'gt' => '>',
        'lt' => '<',
        'gte' => '>=',
        'lte' => '<=',
        'like' => 'like',
    ];

    // Constructor to bind model to the repository
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    // Create a new record
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    // Find a record by its ID
    public function find($id)
    {
        return $this->model->find($id);
    }

    public function findBy(string $col, $value, $relationship = [], $operator = '=')
    {
        return $this->model->newQuery()->where($col, $operator, $value)->with($relationship)->get();
    }

    public function findOneBy(string $col, $value, array $columns = ['*'], array $relations = [], $operator = '=')
    {
        // Always include the primary key if relationships are being loaded
        if (!in_array('id', $columns) && $columns !== ['*'] && !empty($relations)) {
            $columns[] = 'id';
        }

        return $this->model->with($relations)
            ->select($columns)
            ->where($col, $operator, $value)
            ->first();
    }

    public function findOneByOrThrow(string $col, $value, string|array $relation = [], $select = ['*'], $operator = '=')
    {
        return $this->model->newQuery()->with($relation)->where($col, $operator, $value)->first($select) ?? throw new NotFoundHttpException('Resource not found');
    }

    public function findOneOrThrow($id, array|string $columns = ['*'])
    {
        // Ensure $columns is an array if a single column is passed as a string
        $columns = is_array($columns) ? $columns : [$columns];

        return $this->model->select($columns)->where('id', '=', $id)?->first() ?? throw new NotFoundHttpException();
    }

    // Find a record by specific conditions
    public function findWhere(array $conditions)
    {
        return $this->model->where($conditions)->get();
    }

    /**
     * This PHP function updates a record in the database based on the provided ID and data.
     *
     * @param int id The `id` parameter is an integer that represents the identifier of the record you
     * want to update in the database.
     * @param array data The `` parameter in the `update` function is an array that contains the
     * new values that you want to update in the database for the specified record identified by the
     * ``. This array typically consists of key-value pairs where the keys represent the column
     * names in the database table and the values represent
     * @param string cols The `cols` parameter in the `update` function is used to specify which
     * columns should be updated in the database. By default, if no specific columns are provided, it
     * updates all columns (`['*']`). However, you can also pass an array of column names to update
     * only those specific columns data.
     */
    public function update(int $id, array $data, string|array $cols = ['*'])
    {
        $model = $this->findOneOrThrow($id, $cols);
        if ($model) {
            $model->update($data);
        }

        return $model;
    }

    public function updateBy(string $col, mixed $value, array $data, string|array $cols = ['*'])
    {
        $model = $this->findOneByOrThrow($col, $value, [], $cols);
        if ($model) {
            $model->update($data);
        }

        return $model;
    }

    public function upsert(array $data, array $uniqueBy, array $update = [])
    {
        return $this->model->newQuery()->upsert($data, $uniqueBy, $update);
    }

    // Delete a record by its ID
    public function delete($id)
    {
        $model = $this->findOneOrThrow($id);
        if ($model) {
            $model->delete();
        }

        return $model;
    }

    public function deleteByOrThrow(string $col, $value)
    {
        $model = $this->findOneByOrThrow($col, $value);

        if ($model) {
            $model->delete();
        }

        return $model;
    }

    // Get all records
    public function all($columns = ['*'], string|array $relationship = [])
    {
        return $this->model->with($relationship)->get($columns);
    }

    /**
     * The `exists` function checks if a record exists in the database based on the specified column,
     * value, and operator.
     *
     * @param Expression column The `column` parameter in the `exists` function can accept an array, an
     * Expression object, or a string. It is used to specify the column on which the condition will be
     * applied in the database query.
     * @param string|null value The `value` parameter in the `exists` function is used to specify the value that
     * the column should be compared against in the database query. This parameter is optional and
     * defaults to `null` if not provided.
     * @param string operator The `` parameter in the `exists` function is used to specify the
     * comparison operator for the query. By default, it is set to the equality operator `=`. This
     * parameter allows you to customize the type of comparison you want to perform in the database
     * query.
     * @param string boolean The `boolean` parameter in the `exists` function determines how the
     * current condition should be combined with the previous conditions in the query. It specifies
     * whether the condition should be added as an "AND" condition or an "OR" condition when building
     * the query.
     *
     * @return bool The `exists` method is being called on the model with the specified conditions, and
     * the result of that method call (a boolean value indicating whether any records match the
     * conditions) is being returned.
     */
    public function exists(
        array|Expression|string $column,
        $value = null,
        $operator = '=',
        string                  $boolean = 'and'
    ): bool {
        return $this->model->where($column, $operator, $value, $boolean)->exists();
    }

    /**
     * The function `advancedPaginate` in PHP applies filtering, sorting, and pagination to a query
     * based on given parameters.
     *
     * @param array|string|null queryParams The `queryParams` parameter in the `advancedPaginate` function is used
     * to pass any filtering or sorting criteria that you want to apply to the query. It can be an
     * array, a string, or null. If provided, the function will use this information to filter and sort
     * the query results before
     * @param int perPage The `` parameter in the `advancedPaginate` function determines the number
     * of items to display per page when paginating the results. By default, it is set to 15, but you
     * can override this value by passing a different number when calling the function.
     *
     * @return LengthAwarePaginator The function `advancedPaginate` is returning a paginated result based on the query and
     * pagination settings. It applies filtering and sorting to the query based on the provided
     * parameters, then uses Laravel's `paginate` method to paginate the results and return them. The
     * number of items per page is determined by the `per_page` parameter in the query parameters,
     * falling back to a default of 15 if
     */
    public function advancedPaginate(array|string|null $queryParams = null, int $perPage = 15)
    {
        $query = $this->model->newQuery();

        // Apply filtering and sorting
        $this->applyFiltersAndSorting($query, $queryParams);

        // Apply Laravel's paginate
        $perPage = max((int)($queryParams['per_page'] ?? $perPage), 1);
        return $query->paginate($perPage);
    }

    /**
     * The function `advancedCursorPaginate` applies filtering, sorting, and Laravel's cursor
     * pagination to retrieve paginated results based on the provided query parameters.
     *
     * @param array|string|null queryParams The `queryParams` parameter in the `advancedCursorPaginate` function is
     * used to pass any additional filtering or sorting criteria that you want to apply to the query.
     * It can be an array, a string, or null. If provided, these parameters will be used to filter and
     * sort the query results
     * @param int perPage The `` parameter in the `advancedCursorPaginate` function determines the
     * number of items to be displayed per page when paginating the results. By default, it is set to
     * 15, but you can override this value by passing a different number as an argument when calling
     * the function.
     *
     * @return CursorPaginator The `advancedCursorPaginate` function returns the result of cursor pagination applied to
     * the query after filtering and sorting based on the provided query parameters or default values.
     */
    public function advancedCursorPaginate(array|string|null $queryParams = null, int $perPage = 15)
    {
        return $this->customQueryPaginator($queryParams, $perPage)->cursorPaginate($perPage);
    }

    /**
     * @param array|string|null $queryParams
     * @param int $perPage
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function customQueryPaginator(array|string|null $queryParams = null, int $perPage = 15): \Illuminate\Database\Eloquent\Builder
    {
        data_forget($queryParams, 'per_page');

        $query = $this->model->newQuery();

        // Apply filtering and sorting
        $this->applyFiltersAndSorting($query, $queryParams);


        return $query;
    }


    /**
     * The function `applyFiltersAndSorting` processes query parameters to apply filters and sorting to
     * a database query based on model properties and allowed operators.
     *
     * @param \Illuminate\Database\Eloquent\Builder<static> query The `applyFiltersAndSorting` function takes a Builder instance `` and
     * an optional array or string `` as parameters. The function is responsible for
     * applying filters and sorting to the query based on the provided parameters.
     * @param array queryParams The `applyFiltersAndSorting` function you provided is used to apply
     * filters and sorting to a query based on the given parameters. The `queryParams` parameter is
     * expected to be an array or a string representing the query parameters that will be used to
     * filter and sort the query results.
     *
     * @return \Illuminate\Database\Eloquent\Builder<static> The function `applyFiltersAndSorting` returns the modified query builder object after
     * applying filters and sorting based on the provided query parameters.
     */
    protected function applyFiltersAndSorting($query, array|string|null $queryParams = null)
    {
        // Convert queryParams to an array if it's a string or null
        if (is_string($queryParams)) {
            parse_str($queryParams, $queryParams);
        } elseif (is_null($queryParams)) {
            $queryParams = $_GET;
        }

        // Step 1: Extract model properties
        $model = $this->model;
        $table = $model->getTable();
        $fillableFields = $model->getFillable();
        $hiddenFields = $model->getHidden();
        $guardedFields = $model->getGuarded();
        $excludedFilters = property_exists($model, 'excluded_filters') ? $model->excludedFilters : [];

        // Check if $fillableFields is empty, which means all columns are allowed for mass assignment
        if (empty($fillableFields)) {
            // Fetch all columns from the table
            $fillableFields = Schema::getColumnListing($table);
        }


        $allowedFields = array_diff($fillableFields, $guardedFields, $hiddenFields, $excludedFilters);

        // Step 3: Apply filters
        foreach ($queryParams as $key => $value) {
            if (in_array($key, ['sort', 'page', 'per_page', 'cursor'])) {
                continue;
            }
            // Check if the value is an array (meaning an operator like 'like' was used)
            if (is_array($value)) {
                foreach ($value as $operator => $actualValue) {
                    // Normalize the operator (e.g., convert 'like' to '=')
                    $operator = strtolower($operator);

                    // Validate the operator
                    if (!in_array($operator, BaseRepository::$allowedOperators)) {
                        throw new BadRequestHttpException("Invalid operator '{$operator}' for field '{$key}'.");
                    }

                    // Special handling for 'like' operator to add wildcards
                    if ($operator === 'like') {
                        $actualValue = '%' . $actualValue . '%';
                    }

                    // Apply filter if the field is allowed
                    if (in_array($key, $allowedFields)) {
                        $query->where("{$table}.{$key}", $operator, $actualValue);
                    }
                }
            } else {
                // Default to '=' if no operator is specified
                if (in_array($key, $allowedFields)) {
                    $query->where("{$table}.{$key}", '=', $value);
                }
            }
        }


        // Step 4: Apply sorting
        if (isset($queryParams['sort'])) {
            $sortParam = $queryParams['sort'];
            $sortField = ltrim($sortParam, '-');
            $sortDirection = $sortParam[0] === '-' ? 'desc' : 'asc';

            if (in_array($sortField, $allowedFields))
                $query->orderBy($sortField, $sortDirection);
        } else {
            // Default sorting by created_at if no sort is specified
            $query->orderBy('created_at', 'desc');
        }

        return $query;
    }

    public function countBy(string $col, $value): int
    {
        return $this->model->newQuery()->where($col, $value)->count();
    }

    public function count(): int
    {
        return $this->model->newQuery()->count();
    }
}
