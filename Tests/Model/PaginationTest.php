<?php


namespace Net\Dontdrinkandroot\Symfony\ExtensionBundle\Tests\Model;


use Net\Dontdrinkandroot\Symfony\ExtensionBundle\Model\Pagination;

class PaginationTest extends \PHPUnit_Framework_TestCase
{

    public function testInvalid()
    {
        try {
            new Pagination(-1, 0, 0);
            $this->fail("Exception expected");
        } catch (\InvalidArgumentException $e) {
            /* Expected */
        }

        try {
            new Pagination(1, 0, 0);
            $this->fail("Exception expected");
        } catch (\InvalidArgumentException $e) {
            /* Expected */
        }

        try {
            new Pagination(1, 1, -1);
            $this->fail("Exception expected");
        } catch (\InvalidArgumentException $e) {
            /* Expected */
        }
    }

    public function testGetTotalPages()
    {
        $pagination = new Pagination(1, 1, 0);
        $this->assertEquals(0, $pagination->getTotalPages());

        $pagination = new Pagination(1, 1, 1);
        $this->assertEquals(1, $pagination->getTotalPages());

        $pagination = new Pagination(1, 1, 2);
        $this->assertEquals(2, $pagination->getTotalPages());

        $pagination = new Pagination(1, 2, 2);
        $this->assertEquals(1, $pagination->getTotalPages());
    }

} 