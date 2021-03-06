<?php

namespace BoomCMS\Tests\Jobs;

use BoomCMS\Database\Models\Page;
use BoomCMS\Events\PageWasCreated;
use BoomCMS\Jobs\CreatePage;
use BoomCMS\Support\Facades\Page as PageFacade;
use BoomCMS\Tests\AbstractTestCase;
use Mockery as m;

class CreatePageTest extends AbstractTestCase
{
    /**
     * @var Page
     */
    protected $newPage;

    /**
     * @var Page
     */
    protected $parent;

    public function setUp()
    {
        parent::setUp();

        $this->newPage = m::mock(Page::class.'[addVersion]');

        $this->parent = m::mock(Page::class)->makePartial();
        $this->parent->{Page::ATTR_CHILD_TEMPLATE} = 1;

        $this->parent
            ->shouldReceive('getAclGroupIds')
            ->andReturn(collect([]));

        $this->expectsEvents(PageWasCreated::class);
    }

    public function testParentIdIsSet()
    {
        $this->newPage->shouldReceive('addVersion');

        $this->parent->fill([
            Page::ATTR_CHILDREN_VISIBLE_IN_NAV     => true,
            Page::ATTR_CHILDREN_VISIBLE_IN_NAV_CMS => true,
            Page::ATTR_CHILD_TEMPLATE              => 1,
        ]);

        PageFacade::shouldReceive('create')
            ->once()
            ->with(m::subset([
                Page::ATTR_PARENT => $this->parent->getId(),
            ]))
            ->andReturn($this->newPage);

        $job = new CreatePage($this->parent);
        $job->handle();
    }

    public function testNewPageShouldHaveVisibleFromSet()
    {
        $this->newPage->shouldReceive('addVersion');

        $this->parent->fill([
            Page::ATTR_CHILDREN_VISIBLE_IN_NAV     => true,
            Page::ATTR_CHILDREN_VISIBLE_IN_NAV_CMS => true,
            Page::ATTR_CHILD_TEMPLATE              => 1,
        ]);

        PageFacade::shouldReceive('create')
            ->once()
            ->with(m::on(function (array $attrs) {
                return isset($attrs['visible_from']);
            }))
            ->andReturn($this->newPage);

        $job = new CreatePage($this->parent);
        $job->handle();
    }

    public function testSetTitleAllowsSettingPageTitle()
    {
        $title = 'new page title';

        $this->newPage
            ->shouldReceive('addVersion')
            ->once()
            ->with(m::on(function (array $attrs) use ($title) {
                return $title === $attrs['title'];
            }));

        PageFacade::shouldReceive('create')
            ->once()
            ->andReturn($this->newPage);

        $job = new CreatePage($this->parent);
        $job->setTitle($title);
        $job->handle();
    }
}
