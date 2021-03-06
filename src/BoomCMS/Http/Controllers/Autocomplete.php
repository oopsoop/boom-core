<?php

namespace BoomCMS\Http\Controllers;

use BoomCMS\Database\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Autocomplete extends Controller
{
    /**
     * The number of results to be returned. Default is 10.
     *
     * @var int
     */
    public $count;

    /**
     * The text to search for.
     *
     * @var string
     */
    public $text;

    public function __construct(Request $request)
    {
        $this->count = ($request->input('count') > 0) ? $request->input('count') : 10;
        $this->text = $request->input('text');
        $this->request = $request;
    }

    public function getAssets()
    {
        return DB::table('assets')
            ->select('title')
            ->where('title', 'like', "%$this->text%")
            ->orderBy(DB::raw('length(title)'), 'asc')
            ->distinct(true)
            ->limit($this->count)
            ->pluck('title')
            ->all();
    }

    public function getPageTitles()
    {
        $results = [];
        $pages = Page::autocompleteTitle($this->request->input('text'), $this->count)->get();

        foreach ($pages as $p) {
            $primaryUri = substr($p->primary_uri, 0, 1) === '/' ? $p->primary_uri : '/'.$p->primary_uri;

            $results[] = [
                'label' => $p->title.' ('.$primaryUri.')',
                'value' => $primaryUri,
            ];
        }

        return $results;
    }
}
