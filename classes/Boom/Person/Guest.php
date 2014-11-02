<?php

namespace Boom\Person;

class Guest extends \Boom\Person
{
    public function __construct()
    {
        $this->model = new \Model_Person();
    }

    /**
     *
     * @return \Boom\Person\Guest
     */
    public function save()
    {
        return $this;
    }
}
