<?php


namespace Net\Dontdrinkandroot\Symfony\ExtensionBundle\Repository;


use Doctrine\DBAL\Connection;
use Net\Dontdrinkandroot\Symfony\ExtensionBundle\Exception\TooManyResultsException;
use Net\Dontdrinkandroot\Symfony\ExtensionBundle\Model\PaginatedResult;

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

    public function findById($id)
    {
        $sql = 'SELECT * FROM `' . $this->tableName . '` WHERE `' . $this->primaryKey . '` = :id';
        $results = $this->connection->fetchAll($sql, array(":id" => $id));

        if (0 == count($results)) {
            return null;
        }

        /* This exception is only thrown if the primary key was not specified correctly */
        if (1 < count($results)) {
            throw new TooManyResultsException(
                'Found ' . count($results) .
                ' results but only max one was expected'
            );
        }

        return $results[0];
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
            $and = $queryBuilder->expr()->andX();
            foreach ($properties as $column => $value) {
                $and->add($queryBuilder->expr()->eq('t.' . $column, ':' . $column));
                $queryBuilder->setParameter(':' . $column, $value);
            }
            $queryBuilder->where($and);
        }

        if (null !== $firstResult) {
            $queryBuilder->setFirstResult($firstResult);
        }

        if (null !== $maxResults) {
            $queryBuilder->setMaxResults($maxResults);
        }

        /* @var \Doctrine\DBAL\Statement $stmt */
        $stmt = $queryBuilder->execute();

        $this->findCount($properties);

        return $stmt->fetchAll();
    }

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
        $results = $stmt->fetchAll();

        return $results[0]['c'];
    }

    public function delete($id)
    {
        return $this->connection->delete($this->tableName, array($this->primaryKey => $id));
    }

    public function findAll()
    {
        $sql = 'SELECT * FROM `' . $this->tableName . '`';
        return $this->connection->fetchAll($sql);
    }

    public function insert(array $row)
    {
        return $this->connection->insert($this->tableName, $row);
    }

    public function update(array $values, array $where)
    {
        return $this->connection->update($this->tableName, $values, $where);
    }

} 