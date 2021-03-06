<?php

namespace BoomCMS\Contracts\Models;

interface Tag
{
    /**
     * @return string
     */
    public function getGroup();

    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getSlug();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return Site
     */
    public function getSite();

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name);
}
