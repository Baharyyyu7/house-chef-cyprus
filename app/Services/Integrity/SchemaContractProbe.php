<?php

declare(strict_types=1);

namespace App\Services\Integrity;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use PhpParser\Node;
use PhpParser\NodeFinder;
use PhpParser\Parser;
use PhpParser\ParserFactory;

/** Migration column contracts and application write sites, without mutating data. */
final class SchemaContractProbe
{
    private Parser $parser;

    public function __construct()
    {
        $this->parser = (new ParserFactory)->createForNewestSupportedVersion();
    }

    /**
     * @param  list<string>  $sourcePaths
     * @param  list<string>  $migrationPaths
     * @param  list<array{table:string,column:string,type:string,max_length:?int}>  $databaseSnapshot
     * @return array<string,mixed>
     */
    public function inspect(array $sourcePaths, array $migrationPaths, array $databaseSnapshot = []): array
    {
        $schema = $this->migrationSchema($migrationPaths);
        $narrow = collect($schema)
            ->filter(fn (array $column): bool => $column['max_length'] !== null && $column['max_length'] <= 64)
            ->values()
            ->all();
        $writerScan = $this->writers($sourcePaths, $narrow);
        $writers = $writerScan['resolved'];
        $skippedWriters = $writerScan['skipped'];
        $findings = collect($writers)
            ->filter(fn (array $writer): bool => $writer['reason'] !== null)
            ->map(fn (array $writer): array => array_filter([
                'reason' => $writer['reason'],
                'table' => $writer['table'],
                'column' => $writer['column'],
                'max_length' => $writer['max_length'],
                'observed_length' => $writer['observed_length'],
                'file' => $writer['file'],
                'line' => $writer['line'],
            ], fn (mixed $value): bool => $value !== null))
            ->values()
            ->all();
        $schemaDrift = $databaseSnapshot === [] ? [] : $this->compare($schema, $databaseSnapshot);
        $lengthVariants = collect($schema)->filter(fn (array $column): bool => $column['max_length'] !== null)
            ->groupBy('column')
            ->map(function ($columns, string $name): ?array {
                $lengths = $columns->pluck('max_length')->unique()->sort()->values()->all();

                return count($lengths) > 1 ? [
                    'column' => $name,
                    'lengths' => $lengths,
                    'contracts' => $columns->map(fn (array $column): array => ['table' => $column['table'], 'max_length' => $column['max_length']])->values()->all(),
                ] : null;
            })->filter()->values()->all();

        return [
            'status' => $findings === [] && $schemaDrift === [] ? 'ok' : 'issues_found',
            'checked_at' => now()->toIso8601String(),
            'narrow_columns' => count($narrow),
            'writer_count' => count($writers),
            'skipped_writer_count' => count($skippedWriters),
            'finding_count' => count($findings),
            'schema_drift_count' => count($schemaDrift),
            'columns' => $narrow,
            'writers' => $writers,
            'skipped_writers' => $skippedWriters,
            'findings' => $findings,
            'schema_drift' => $schemaDrift,
            'column_length_variants' => $lengthVariants,
        ];
    }

    /** @param list<string> $paths @return list<array<string,mixed>> */
    private function migrationSchema(array $paths): array
    {
        $schema = [];

        foreach ($this->phpFiles($paths) as $file) {
            $nodes = $this->parse($file);
            if ($nodes === []) {
                continue;
            }

            $finder = new NodeFinder;
            $constants = [];
            foreach ($finder->findInstanceOf($nodes, Node\Stmt\ClassConst::class) as $declaration) {
                foreach ($declaration->consts as $constant) {
                    if ($constant->value instanceof Node\Scalar\String_) {
                        $constants[$constant->name->toString()] = $constant->value->value;
                    }
                }
            }
            $upMethods = array_values(array_filter(
                $finder->findInstanceOf($nodes, Node\Stmt\ClassMethod::class),
                fn (Node\Stmt\ClassMethod $method): bool => $method->name->toString() === 'up',
            ));
            $scope = $upMethods === []
                ? $nodes
                : array_merge(...array_map(fn (Node\Stmt\ClassMethod $method): array => $method->stmts ?? [], $upMethods));

            foreach ($finder->findInstanceOf($scope, Node\Expr\StaticCall::class) as $call) {
                if (! $call->class instanceof Node\Name || $call->class->getLast() !== 'Schema' || ! $call->name instanceof Node\Identifier) {
                    continue;
                }

                $operation = $call->name->toString();
                $table = $this->stringArgument($call->args[0] ?? null, $constants);
                if ($table === null) {
                    continue;
                }

                $this->applySchemaCall($schema, $operation, $table, $call, $file);
            }

            foreach ($finder->findInstanceOf($scope, Node\Stmt\Foreach_::class) as $foreach) {
                if (! $foreach->expr instanceof Node\Expr\Array_ || ! $foreach->valueVar instanceof Node\Expr\Variable || ! is_string($foreach->valueVar->name)) {
                    continue;
                }
                $tables = collect($foreach->expr->items)
                    ->filter(fn (?Node\ArrayItem $item): bool => $item?->value instanceof Node\Scalar\String_)
                    ->map(fn (Node\ArrayItem $item): string => $item->value->value)
                    ->values()
                    ->all();
                foreach ($finder->findInstanceOf($foreach->stmts, Node\Expr\StaticCall::class) as $call) {
                    $argument = $call->args[0]->value ?? null;
                    if (! $argument instanceof Node\Expr\Variable || $argument->name !== $foreach->valueVar->name || ! $call->name instanceof Node\Identifier) {
                        continue;
                    }
                    foreach ($tables as $table) {
                        $this->applySchemaCall($schema, $call->name->toString(), $table, $call, $file);
                    }
                }
            }
        }

        ksort($schema);

        return array_values($schema);
    }

    /** @param array<string,array<string,mixed>> $schema */
    private function applySchemaCall(array &$schema, string $operation, string $table, Node\Expr\StaticCall $call, string $file): void
    {
        if (in_array($operation, ['drop', 'dropIfExists'], true)) {
            foreach (array_keys($schema) as $key) {
                if (str_starts_with($key, $table.'.')) {
                    unset($schema[$key]);
                }
            }

            return;
        }

        $closure = $call->args[1]->value ?? null;
        if (! in_array($operation, ['create', 'table'], true) || ! $closure instanceof Node\Expr\Closure) {
            return;
        }

        if ($operation === 'create') {
            foreach (array_keys($schema) as $key) {
                if (str_starts_with($key, $table.'.')) {
                    unset($schema[$key]);
                }
            }
        }

        $this->applyBlueprint($schema, $table, $closure, $file);
    }

    /** @param array<string,array<string,mixed>> $schema */
    private function applyBlueprint(array &$schema, string $table, Node\Expr\Closure $closure, string $file): void
    {
        $calls = (new NodeFinder)->findInstanceOf($closure->stmts, Node\Expr\MethodCall::class);

        foreach ($calls as $call) {
            if (! $call->name instanceof Node\Identifier) {
                continue;
            }

            $method = $call->name->toString();
            if ($method === 'dropColumn') {
                foreach ($call->args as $argument) {
                    $column = $this->stringArgument($argument);
                    if ($column !== null) {
                        unset($schema[$table.'.'.$column]);
                    }
                }

                continue;
            }

            if ($method === 'timestamps') {
                foreach (['created_at', 'updated_at'] as $column) {
                    $schema[$table.'.'.$column] = $this->column($table, $column, 'timestamp', null, $file, $call->getStartLine());
                }

                continue;
            }

            if (in_array($method, ['morphs', 'nullableMorphs'], true)) {
                $name = $this->stringArgument($call->args[0] ?? null);
                if ($name !== null) {
                    $schema[$table.'.'.$name.'_type'] = $this->column($table, $name.'_type', 'varchar', 255, $file, $call->getStartLine());
                    $schema[$table.'.'.$name.'_id'] = $this->column($table, $name.'_id', 'bigint', null, $file, $call->getStartLine());
                }

                continue;
            }

            if (in_array($method, ['softDeletes', 'softDeletesTz'], true)) {
                $schema[$table.'.deleted_at'] = $this->column($table, 'deleted_at', 'timestamp', null, $file, $call->getStartLine());

                continue;
            }

            if ($method === 'rememberToken') {
                $schema[$table.'.remember_token'] = $this->column($table, 'remember_token', 'varchar', 100, $file, $call->getStartLine());

                continue;
            }

            $column = $this->stringArgument($call->args[0] ?? null);
            if ($method === 'id' && $column === null) {
                $column = 'id';
            }
            if ($column === null) {
                continue;
            }

            [$type, $length] = $this->blueprintType($method, $call);
            if ($type === null) {
                continue;
            }

            $schema[$table.'.'.$column] = $this->column($table, $column, $type, $length, $file, $call->getStartLine());
        }
    }

    /** @return array{0:?string,1:?int} */
    private function blueprintType(string $method, Node\Expr\MethodCall $call): array
    {
        return match ($method) {
            'string' => ['varchar', $this->integerArgument($call->args[1] ?? null) ?? 255],
            'char' => ['char', $this->integerArgument($call->args[1] ?? null) ?? 255],
            'id', 'bigInteger', 'unsignedBigInteger', 'foreignId' => ['bigint', null],
            'integer', 'unsignedInteger' => ['integer', null],
            'smallInteger', 'unsignedSmallInteger' => ['smallint', null],
            'tinyInteger', 'unsignedTinyInteger', 'boolean' => ['tinyint', null],
            'text', 'mediumText', 'longText' => [$method, null],
            'json', 'jsonb' => ['json', null],
            'timestamp', 'dateTime', 'date', 'time' => [strtolower($method), null],
            'decimal', 'float', 'double' => [$method, null],
            'uuid', 'ulid' => ['char', $method === 'uuid' ? 36 : 26],
            default => [null, null],
        };
    }

    /** @param list<string> $paths @param list<array<string,mixed>> $narrow @return array{resolved:list<array<string,mixed>>,skipped:list<array<string,mixed>>} */
    private function writers(array $paths, array $narrow): array
    {
        $byContract = collect($narrow)->keyBy(fn (array $column): string => $column['table'].'.'.$column['column']);
        $writers = [];
        $skipped = [];

        foreach ($this->phpFiles($paths) as $file) {
            $nodes = $this->parse($file);
            $finder = new NodeFinder;
            $imports = $this->imports($nodes);
            $typedVariables = $this->typedVariables($nodes, $imports);
            $typedProperties = $this->typedProperties($nodes, $imports);
            $calls = array_merge(
                $finder->findInstanceOf($nodes, Node\Expr\MethodCall::class),
                $finder->findInstanceOf($nodes, Node\Expr\StaticCall::class),
            );
            foreach ($calls as $call) {
                $method = $call->name instanceof Node\Identifier ? $call->name->toString() : null;
                if (! in_array($method, ['create', 'update', 'insert', 'upsert', 'fill', 'forceFill', 'firstOrCreate', 'updateOrCreate'], true)) {
                    continue;
                }

                $columns = $this->writtenColumns($call);
                if ($columns === []) {
                    continue;
                }

                $table = $this->writeTable($call, $imports, $typedVariables, $typedProperties);
                if ($table === null) {
                    $skipped[] = [
                        'method' => $method,
                        'pattern' => $this->receiverPattern($call),
                        'columns' => $columns,
                        'file' => $this->relativePath($file),
                        'line' => $call->getStartLine(),
                    ];

                    continue;
                }

                foreach ($call->getArgs() as $argument) {
                    if (! $argument->value instanceof Node\Expr\Array_) {
                        continue;
                    }

                    foreach ($argument->value->items as $item) {
                        if (! $item instanceof Node\ArrayItem || ! $item->key instanceof Node\Scalar\String_) {
                            continue;
                        }

                        $column = $item->key->value;
                        $contract = $byContract->get($table.'.'.$column);
                        if ($contract === null) {
                            continue;
                        }

                        $max = (int) $contract['max_length'];
                        $literal = $item->value instanceof Node\Scalar\String_ ? $item->value->value : null;
                        $observed = $literal === null ? null : mb_strlen($literal);
                        $writers[] = [
                            'table' => $table,
                            'column' => $column,
                            'max_length' => $max,
                            'value_kind' => $literal === null ? 'dynamic' : 'literal',
                            'observed_length' => $observed,
                            // Dinamik değer hakkında uzunluk kanıtımız yoktur. Bu
                            // yüzden bağlamda görünür ama bulgu/manşet sayılmaz.
                            'reason' => $literal !== null && $observed > $max ? 'literal_overflow' : null,
                            'file' => $this->relativePath($file),
                            'line' => $item->getStartLine(),
                        ];
                    }
                }
            }
        }

        return ['resolved' => $writers, 'skipped' => $skipped];
    }

    /** @param Node\Expr\MethodCall|Node\Expr\StaticCall $call @return list<string> */
    private function writtenColumns(Node\Expr $call): array
    {
        $columns = [];
        foreach ($call->getArgs() as $argument) {
            if (! $argument->value instanceof Node\Expr\Array_) {
                continue;
            }
            foreach ($argument->value->items as $item) {
                if ($item instanceof Node\ArrayItem && $item->key instanceof Node\Scalar\String_) {
                    $columns[] = $item->key->value;
                }
            }
        }

        return array_values(array_unique($columns));
    }

    /** @param Node\Expr\MethodCall|Node\Expr\StaticCall $call */
    private function receiverPattern(Node\Expr $call): string
    {
        $receiver = $call instanceof Node\Expr\MethodCall ? $call->var : $call->class;

        return match (true) {
            $receiver instanceof Node\Expr\Variable => 'variable_receiver',
            $receiver instanceof Node\Expr\PropertyFetch => 'property_receiver',
            $receiver instanceof Node\Expr\MethodCall => 'method_chain_receiver',
            $receiver instanceof Node\Name => 'static_receiver',
            default => 'other_receiver',
        };
    }

    /** @param list<Node\Stmt> $nodes @return array<string,string> */
    private function imports(array $nodes): array
    {
        $imports = [];
        foreach ((new NodeFinder)->findInstanceOf($nodes, Node\Stmt\Use_::class) as $use) {
            foreach ($use->uses as $item) {
                $imports[$item->getAlias()->toString()] = $item->name->toString();
            }
        }

        return $imports;
    }

    /** @param list<Node\Stmt> $nodes @param array<string,string> $imports @return list<array{variable:string,class:string,start:int,end:int}> */
    private function typedVariables(array $nodes, array $imports): array
    {
        $variables = [];
        foreach ((new NodeFinder)->findInstanceOf($nodes, Node\FunctionLike::class) as $function) {
            foreach ($function->getParams() as $parameter) {
                if (! $parameter->var instanceof Node\Expr\Variable || ! is_string($parameter->var->name) || ! $parameter->type instanceof Node\Name) {
                    continue;
                }
                $variables[] = [
                    'variable' => $parameter->var->name,
                    'class' => $this->resolvedClass($parameter->type, $imports),
                    'start' => $function->getStartLine(),
                    'end' => $function->getEndLine(),
                ];
            }
        }

        foreach ((new NodeFinder)->findInstanceOf($nodes, Node\Expr\Assign::class) as $assignment) {
            if (! $assignment->var instanceof Node\Expr\Variable || ! is_string($assignment->var->name)) {
                continue;
            }
            $class = $this->modelClassFromExpression($assignment->expr, $imports);
            if ($class === null) {
                continue;
            }
            $variables[] = [
                'variable' => $assignment->var->name,
                'class' => $class,
                'start' => $assignment->getStartLine(),
                'end' => PHP_INT_MAX,
            ];
        }

        // Query sonucunu dolaşan değişken koleksiyonun kendisi değil, tek bir
        // model örneğidir. Bu bağı taşımazsak `$records as $record` sonrasında
        // yapılan fill/update çağrıları sessizce kapsam dışında kalır.
        foreach ((new NodeFinder)->findInstanceOf($nodes, Node\Stmt\Foreach_::class) as $foreach) {
            if (! $foreach->valueVar instanceof Node\Expr\Variable || ! is_string($foreach->valueVar->name)) {
                continue;
            }

            $class = $this->modelClassFromExpression($foreach->expr, $imports);
            if ($class === null && $foreach->expr instanceof Node\Expr\Variable && is_string($foreach->expr->name)) {
                $line = $foreach->getStartLine();
                $source = collect($variables)
                    ->filter(fn (array $candidate): bool => $candidate['variable'] === $foreach->expr->name && $candidate['start'] <= $line && $candidate['end'] >= $line)
                    ->sortByDesc('start')
                    ->first();
                $class = is_array($source) ? $source['class'] : null;
            }

            if ($class !== null) {
                $variables[] = [
                    'variable' => $foreach->valueVar->name,
                    'class' => $class,
                    'start' => $foreach->getStartLine(),
                    'end' => $foreach->getEndLine(),
                ];
            }
        }

        return $variables;
    }

    /** @param list<Node\Stmt> $nodes @param array<string,string> $imports @return list<array{property:string,class:string,start:int,end:int}> */
    private function typedProperties(array $nodes, array $imports): array
    {
        $properties = [];
        foreach ((new NodeFinder)->findInstanceOf($nodes, Node\Stmt\Class_::class) as $class) {
            foreach ($class->getProperties() as $property) {
                if (! $property->type instanceof Node\Name) {
                    continue;
                }
                foreach ($property->props as $item) {
                    $properties[] = [
                        'property' => $item->name->toString(),
                        'class' => $this->resolvedClass($property->type, $imports),
                        'start' => $class->getStartLine(),
                        'end' => $class->getEndLine(),
                    ];
                }
            }
            foreach ($class->getMethods() as $method) {
                if ($method->name->toString() !== '__construct') {
                    continue;
                }
                foreach ($method->params as $parameter) {
                    if ($parameter->flags === 0 || ! $parameter->var instanceof Node\Expr\Variable || ! is_string($parameter->var->name) || ! $parameter->type instanceof Node\Name) {
                        continue;
                    }
                    $properties[] = [
                        'property' => $parameter->var->name,
                        'class' => $this->resolvedClass($parameter->type, $imports),
                        'start' => $class->getStartLine(),
                        'end' => $class->getEndLine(),
                    ];
                }
            }
        }

        return $properties;
    }

    /** @param array<string,string> $imports */
    private function modelClassFromExpression(Node\Expr $expression, array $imports): ?string
    {
        if ($expression instanceof Node\Expr\StaticCall && $expression->class instanceof Node\Name) {
            $class = $this->resolvedClass($expression->class, $imports);

            return $this->modelTable($class) !== null ? $class : null;
        }

        if ($expression instanceof Node\Expr\MethodCall) {
            return $this->modelClassFromExpression($expression->var, $imports);
        }

        return null;
    }

    /** @param Node\Expr\MethodCall|Node\Expr\StaticCall $call @param array<string,string> $imports @param list<array{variable:string,class:string,start:int,end:int}> $typedVariables @param list<array{property:string,class:string,start:int,end:int}> $typedProperties */
    private function writeTable(Node\Expr $call, array $imports, array $typedVariables, array $typedProperties): ?string
    {
        $receiver = $call instanceof Node\Expr\MethodCall ? $call->var : $call->class;

        if ($receiver instanceof Node\Expr\StaticCall
            && $receiver->class instanceof Node\Name
            && $receiver->class->getLast() === 'DB'
            && $receiver->name instanceof Node\Identifier
            && $receiver->name->toString() === 'table') {
            return $this->stringArgument($receiver->args[0] ?? null);
        }

        if ($receiver instanceof Node\Expr\StaticCall && $receiver->class instanceof Node\Name) {
            return $this->modelTable($this->resolvedClass($receiver->class, $imports));
        }

        // Model::query()->where(...)->update(...) ve
        // $this->model->newQuery()->update(...) gibi zincirlerde tablo en dış
        // çağrıda değil, zincirin kökünde taşınır.
        if ($receiver instanceof Node\Expr\MethodCall) {
            $related = $this->relationshipTable($receiver, $imports, $typedVariables, $typedProperties);
            if ($related !== null) {
                return $related;
            }

            return $this->writeTable($receiver, $imports, $typedVariables, $typedProperties);
        }

        if ($call instanceof Node\Expr\StaticCall && $call->class instanceof Node\Name) {
            return $this->modelTable($this->resolvedClass($call->class, $imports));
        }

        if ($receiver instanceof Node\Expr\Variable && is_string($receiver->name)) {
            $line = $call->getStartLine();
            $match = collect($typedVariables)
                ->filter(fn (array $candidate): bool => $candidate['variable'] === $receiver->name && $candidate['start'] <= $line && $candidate['end'] >= $line)
                ->sortByDesc('start')
                ->first();

            return is_array($match) ? $this->modelTable($match['class']) : null;
        }

        if ($receiver instanceof Node\Expr\PropertyFetch
            && $receiver->var instanceof Node\Expr\Variable
            && $receiver->var->name === 'this'
            && $receiver->name instanceof Node\Identifier) {
            $line = $call->getStartLine();
            $match = collect($typedProperties)->first(fn (array $candidate): bool => $candidate['property'] === $receiver->name->toString()
                && $candidate['start'] <= $line
                && $candidate['end'] >= $line);

            return is_array($match) ? $this->modelTable($match['class']) : null;
        }

        return null;
    }

    /** @param array<string,string> $imports @param list<array{variable:string,class:string,start:int,end:int}> $typedVariables @param list<array{property:string,class:string,start:int,end:int}> $typedProperties */
    private function relationshipTable(Node\Expr\MethodCall $call, array $imports, array $typedVariables, array $typedProperties): ?string
    {
        if (! $call->name instanceof Node\Identifier || $call->args !== []) {
            return null;
        }

        $modelClass = $this->receiverModelClass($call->var, $call->getStartLine(), $imports, $typedVariables, $typedProperties);
        $related = $modelClass === null ? null : $this->relatedModelClass($modelClass, $call->name->toString());

        return $related === null ? null : $this->modelTable($related);
    }

    /** @param array<string,string> $imports @param list<array{variable:string,class:string,start:int,end:int}> $typedVariables @param list<array{property:string,class:string,start:int,end:int}> $typedProperties */
    private function receiverModelClass(Node\Expr $receiver, int $line, array $imports, array $typedVariables, array $typedProperties): ?string
    {
        if ($receiver instanceof Node\Expr\StaticCall && $receiver->class instanceof Node\Name) {
            $class = $this->resolvedClass($receiver->class, $imports);

            return $this->modelTable($class) !== null ? $class : null;
        }

        if ($receiver instanceof Node\Expr\Variable && is_string($receiver->name)) {
            $match = collect($typedVariables)
                ->filter(fn (array $candidate): bool => $candidate['variable'] === $receiver->name && $candidate['start'] <= $line && $candidate['end'] >= $line)
                ->sortByDesc('start')->first();

            return is_array($match) && $this->modelTable($match['class']) !== null ? $match['class'] : null;
        }

        if ($receiver instanceof Node\Expr\PropertyFetch && $receiver->name instanceof Node\Identifier) {
            if ($receiver->var instanceof Node\Expr\Variable && $receiver->var->name === 'this') {
                $match = collect($typedProperties)->first(fn (array $candidate): bool => $candidate['property'] === $receiver->name->toString()
                    && $candidate['start'] <= $line && $candidate['end'] >= $line);

                return is_array($match) && $this->modelTable($match['class']) !== null ? $match['class'] : null;
            }

            // Eloquent ilişkisinin property biçimi (`$generation->recipe`)
            // de yöntem biçimiyle aynı modeli taşır. Sadece dönüş tipi açıkça
            // Relation olan sıfır argümanlı metotları çalıştırırız; Stripe SDK
            // gibi keyfi property zincirleri bu yola giremez.
            $parent = $this->receiverModelClass($receiver->var, $line, $imports, $typedVariables, $typedProperties);

            return $parent === null ? null : $this->relatedModelClass($parent, $receiver->name->toString());
        }

        return null;
    }

    private function relatedModelClass(string $modelClass, string $relationship): ?string
    {
        if (! method_exists($modelClass, $relationship)) {
            return null;
        }

        try {
            $method = new \ReflectionMethod($modelClass, $relationship);
            $returnType = $method->getReturnType();
            if ($method->getNumberOfRequiredParameters() !== 0
                || ! $returnType instanceof \ReflectionNamedType
                || ! is_a($returnType->getName(), Relation::class, true)) {
                return null;
            }

            $relation = $method->invoke(new $modelClass);

            return $relation instanceof Relation ? $relation->getRelated()::class : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /** @param array<string,string> $imports */
    private function resolvedClass(Node\Name $name, array $imports): string
    {
        $class = $name->toString();

        return $imports[$name->getFirst()] ?? $class;
    }

    private function modelTable(string $class): ?string
    {
        if (! class_exists($class) || ! is_subclass_of($class, Model::class)) {
            return null;
        }

        return (new $class)->getTable();
    }

    /** @param list<array<string,mixed>> $expected @param list<array<string,mixed>> $actual @return list<array<string,mixed>> */
    private function compare(array $expected, array $actual): array
    {
        $expectedByKey = collect($expected)->keyBy(fn (array $column): string => $column['table'].'.'.$column['column']);
        $actualByKey = collect($actual)->keyBy(fn (array $column): string => $column['table'].'.'.$column['column']);
        $drift = [];

        foreach ($expectedByKey as $key => $column) {
            $database = $actualByKey->get($key);
            if ($database === null) {
                $drift[] = ['reason' => 'missing_in_database', 'table' => $column['table'], 'column' => $column['column']];

                continue;
            }
            if ($this->normalizedType((string) ($database['type'] ?? '')) !== $this->normalizedType((string) $column['type'])) {
                $drift[] = [
                    'reason' => 'type_mismatch', 'table' => $column['table'], 'column' => $column['column'],
                    'migration_type' => $column['type'], 'database_type' => $database['type'] ?? null,
                ];
            }
            if ($column['max_length'] !== null && (int) ($database['max_length'] ?? 0) !== $column['max_length']) {
                $drift[] = [
                    'reason' => 'length_mismatch', 'table' => $column['table'], 'column' => $column['column'],
                    'migration_length' => $column['max_length'], 'database_length' => $database['max_length'] ?? null,
                ];
            }
        }

        foreach ($actualByKey as $key => $column) {
            if (! $expectedByKey->has($key) && $expectedByKey->contains(fn (array $item): bool => $item['table'] === $column['table'])) {
                $drift[] = ['reason' => 'unexpected_in_database', 'table' => $column['table'], 'column' => $column['column']];
            }
        }

        return $drift;
    }

    private function normalizedType(string $type): string
    {
        return match (strtolower($type)) {
            'integer' => 'int',
            // MariaDB exposes Laravel JSON columns as LONGTEXT. JSON validity
            // is enforced separately; this is not schema drift.
            'json' => 'longtext',
            'datetime' => 'datetime',
            default => strtolower($type),
        };
    }

    /** @return list<string> */
    private function phpFiles(array $paths): array
    {
        $files = [];
        foreach ($paths as $path) {
            if (is_file($path) && str_ends_with($path, '.php')) {
                $files[] = $path;
            } elseif (is_dir($path)) {
                foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $item) {
                    if ($item->isFile() && $item->getExtension() === 'php') {
                        $files[] = $item->getPathname();
                    }
                }
            }
        }
        sort($files);

        return $files;
    }

    /** @return list<Node\Stmt> */
    private function parse(string $file): array
    {
        try {
            return $this->parser->parse((string) file_get_contents($file)) ?? [];
        } catch (\Throwable) {
            return [];
        }
    }

    /** @param array<string,string> $constants */
    private function stringArgument(Node\Arg|Node\VariadicPlaceholder|null $argument, array $constants = []): ?string
    {
        if (! $argument instanceof Node\Arg) {
            return null;
        }
        if ($argument->value instanceof Node\Scalar\String_) {
            return $argument->value->value;
        }
        if ($argument->value instanceof Node\Expr\ClassConstFetch && $argument->value->name instanceof Node\Identifier) {
            return $constants[$argument->value->name->toString()] ?? null;
        }

        return null;
    }

    private function integerArgument(Node\Arg|Node\VariadicPlaceholder|null $argument): ?int
    {
        return $argument instanceof Node\Arg && $argument->value instanceof Node\Scalar\Int_ ? $argument->value->value : null;
    }

    /** @return array<string,mixed> */
    private function column(string $table, string $column, string $type, ?int $length, string $file, int $line): array
    {
        return ['table' => $table, 'column' => $column, 'type' => $type, 'max_length' => $length, 'migration' => $this->relativePath($file), 'line' => $line];
    }

    private function relativePath(string $path): string
    {
        $base = rtrim(base_path(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

        return str_starts_with($path, $base) ? substr($path, strlen($base)) : $path;
    }
}
