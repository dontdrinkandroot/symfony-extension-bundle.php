<?php


namespace Dontdrinkandroot\Symfony\ExtensionBundle\Repository;


use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Dontdrinkandroot\Pagination\PaginatedResult;
use Dontdrinkandroot\Pagination\Pagination;
use Dontdrinkandroot\Symfony\ExtensionBundle\Exception\NoResultFoundException;
use Dontdrinkandroot\Symfony\ExtensionBundle\Exception\TooManyResultsException;

class DoctrineRepository
{

    /**
     * @var string
     */
    protected $tableName;

    /**
     * @var string
     */
    protected $primaryKey;

    /**
     * @var Connection
     */
    protected $connection;

    public function __construct(Connection $connection, $tableName, $primaryKey)
    {
        $this->connection = $connection;
        $this->tableName = (string)$tableName;
        $this->primaryKey = (string)$primaryKey;
    }

    /**
     * Get the underlying Doctrine Database Connection.
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param mixed $id
     * @return array|null
     * @throws TooManyResultsException
     */
    public function findById($id)
    {
        $sql = 'SELECT * FROM `' . $this->tableName . '` WHERE `' . $this->primaryKey . '` = :id';
        $rows = $this->connection->fetchAll($sql, array(":id" => $id));

        return $this->getSingleRowOrNull($rows);
    }

    /**
     * @param mixed $id
     * @return array
     * @throws NoResultFoundException
     * @throws TooManyResultsException
     */
    public function getById($id)
    {
        $sql = 'SELECT * FROM `' . $this->tableName . '` WHERE `' . $this->primaryKey . '` = :id';
        $rows = $this->connection->fetchAll($sql, array(":id" => $id));

        return $this->getSingleRow($rows);
    }

    /**
     * @param array $columns
     * @param array $properties
     * @param int $page
     * @param int $perPage
     * @return PaginatedResult
     */
    public function findPaginatedResult(
        array $columns = array(),
        array $properties = array(),
        $page,
        $perPage
    ) {
        $count = $this->findCount($properties);
        $results = $this->find($columns, $properties, ($page - 1) * $perPage, $perPage);
        $pagination = new Pagination($page, $perPage, $count);

        return new PaginatedResult($pagination, $results);
    }

    /**
     * @param array $columns
     * @param array $properties
     * @param int|null $firstResult
     * @param int|null $maxResults
     * @return array
     */
    public function find(array $columns = array(), array $properties = array(), $firstResult = null, $maxResults = null)
    {
        /* @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->from($this->tableName, 't');

        if (!empty($columns)) {
            $queryBuilder->select($columns);
        } else {
            $queryBuilder->select('*');
        }

        if (!empty($properties)) {
            foreach ($properties as $column => $value) {
                $queryBuilder->andWhere($queryBuilder->expr()->eq('t.' . $column, ':' . $column));
                $queryBuilder->setParameter($column, $value);
            }
        }

        if (null !== $firstResult) {
            $queryBuilder->setFirstResult($firstResult);
        }

        if (null !== $maxResults) {
            $queryBuilder->setMaxResults($maxResults);
        }

        /* @var \Doctrine\DBAL\Statement $stmt */
        $stmt = $queryBuilder->execute();

        return $stmt->fetchAll();
    }

    /**
     * @param array $properties
     * @return int
     */
    public function findCount(array $properties = array())
    {
        /* @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->select('count(*) AS c');
        $queryBuilder->from($this->tableName, 't');

        if (!empty($properties)) {
            $and = $queryBuilder->expr()->andX();
            foreach ($properties as $column => $value) {
                $and->add($queryBuilder->expr()->eq('t.' . $column, ':' . $column));
                $queryBuilder->setParameter(':' . $column, $value);
            }
            $queryBuilder->where($and);
        }

        /* @var \Doctrine\DBAL\Statement $stmt */
        $stmt = $queryBuilder->execute();
        $rows = $stmt->fetchAll();

        return (int) $this->getSingleRow($rows)['c'];
    }

    /**
     * @param mixed $id
     * @return int
     */
    public function delete($id)
    {
        return $this->connection->delete($this->tableName, array($this->primaryKey => $id));
    }

    /**
     * @return array
     */
    public function findAll()
    {
        $sql = 'SELECT * FROM `' . $this->tableName . '`';
        return $this->connection->fetchAll($sql);
    }

    /**
     * @param array $row
     * @return int
     */
    public function insert(array $row)
    {
        return $this->connection->insert($this->tableName, $row);
    }

    /**
     * @param array $values
     * @param array $where
     * @return int
     */
    public function update(array $values, array $where)
    {
        return $this->connection->update($this->tableName, $values, $where);
    }

    public function beginTransaction()
    {
        $this->connection->beginTransaction();
    }

    public function commitTransaction()
    {
        $this->connection->commit();
    }

    public function rollbackTransaction()
    {
        $this->connection->rollBack();
    }

    /**
     * @param array $rows
     * @return array|null
     * @throws TooManyResultsException
     */
    public function getSingleRowOrNull(array $rows)
    {
        if (empty($rows)) {
            return null;
        }
        $this->assertSingleRow($rows);

        return $rows[0];
    }

    /**
     * @param array $rows
     * @return array
     * @throws TooManyResultsException
     * @throws NoResultFoundException
     */
    public function getSingleRow(array $rows)
    {
        $this->assertRowsNotEmpty($rows);
        $this->assertSingleRow($rows);

        return $rows[0];
    }

    /**
     * @param array $rows
     * @throws NoResultFoundException
     */
    private function assertRowsNotEmpty(array $rows)
    {
        if (empty($rows)) {
            throw new NoResultFoundException();
        }
    }

    /**
     * @param array $rows
     * @throws TooManyResultsException
     */
    private function assertSingleRow(array $rows)
    {
        if (1 < count($rows)) {
            throw new TooManyResultsException(
                'Found ' . count($rows) .
                ' results but only max one was expected'
            );
        }
    }

}