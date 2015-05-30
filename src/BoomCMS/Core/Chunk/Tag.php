<?php

namespace BoomCMS\Core\Chunk;

use BoomCMS\Core\Page\Page as Page;
use \Kohana as Kohana;
use \View as View;

class Tag extends \Boom\Chunk
{
    protected $_default_template = 'gallery';
    protected $_tag;
    protected $_type = 'tag';

    public function __construct(Page $page, $chunk, $editable = true)
    {
        parent::__construct($page, $chunk, $editable);

        $this->_tag = $this->_chunk->tag;
    }

    protected function _show()
    {
        if ( ! $this->_template || ! Kohana::find_file("views", $this->viewPrefix."tag/$this->_template")) {
            $this->_template = $this->_default_template;
        }

        return View::factory($this->viewPrefix."tag/$this->_template", [
            'tag' => $this->_tag,
        ]);
    }

    protected function _show_default()
    {
        return new View($this->viewPrefix."default/tag/$this->_template");
    }

    public function attributes()
    {
        return [
            $this->attributePrefix.'tag' => $this->getTag(),
        ];
    }

    public function getTag()
    {
        return $this->_tag;
    }

    public function hasContent()
    {
        return $this->_chunk->loaded() && $this->getTag();
    }
}