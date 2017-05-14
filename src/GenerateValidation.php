<?php


namespace Juddos545\Muster;


use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use PhpParser\PrettyPrinter;
use PhpParser\Node;

class GenerateValidation
{
    private $connection;
    /**
     * @var ForeignKeyConstraint[]
     */
    private $foreignKeys;

    public function __construct()
    {
        $config = new \Doctrine\DBAL\Configuration();
        $connectionParams = array(
            'dbname' => getenv('DB_DATABASE'),
            'user' => getenv('DB_USERNAME'),
            'password' => getenv('DB_PASSWORD'),
            'host' => getenv('DB_HOST'),
            'driver' => 'pdo_mysql',
        );
        $this->connection = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
    }

    public function validator($tableName)
    {
        $sm = $this->connection->getSchemaManager();
        $columns = $sm->listTableColumns($tableName);

        $this->foreignKeys = $sm->listTableForeignKeys($tableName);

        $validationColumns = collect($columns)
            ->filter(function (Column $column) {
                $skip = ['id', 'created_at', 'updated_at'];
                return !in_array($column->getName(), $skip, true);
            })
            ->map(function (Column $column) {
                return [
                    'key' => $column->getName(),
                    'type' => $column->getType(),
                    'rules' => $this->validationForColumn($column)
                ];
            });

        return $this->convertToPhp($validationColumns->toArray());
    }

    /**
     * on PHP Parser master there is a BuilderFactory->val() method which can be passed
     * the native array and will construct this expressions for us
     */
    private function convertToPhp(array $validations)
    {
        $items = [];
        foreach ($validations as $validation) {
            $items[] = new Node\Expr\ArrayItem(
                new Node\Scalar\String_($validation['rules']),
                new Node\Scalar\String_($validation['key'])
            );
        }

        $arr = new Node\Expr\Array_($items);
        $prettyPrinter = new PrettyPrinter\Standard(['shortArraySyntax' => true]);
        return $prettyPrinter->prettyPrintExpr($arr);
    }

    private function validationForColumn(Column $column): string
    {
        // make each column required by default
        $rules = ['required'];
        $constraintColumn = false;

        foreach ($this->foreignKeys as $constraint) {
            if (in_array($column->getName(), $constraint->getLocalColumns(), true)) {
                $rules[] = 'exists:' . $constraint->getForeignTableName();
                $constraintColumn = true;
            }
        }

        if (!$constraintColumn) {
            $laravelValidationRule = $this->mysqlTypeToLaravelType($column);
            if ($laravelValidationRule !== null) {
                $rules[] = $laravelValidationRule;
            }
        }

        return implode('|', $rules);
    }

    private function mysqlTypeToLaravelType(Column $column)
    {
        if (str_contains($column->getName(), 'email')) {
            return 'email';
        }

        $columnType = $column->getType()->getName();

        $mappings = [
            'text' => 'string',
            'datetime' => 'date',
            'boolean' => 'boolean',
            'integer' => 'integer'
        ];

        if (array_key_exists($columnType, $mappings)) {
            return $mappings[$columnType];
        }

        return null;
    }
}
