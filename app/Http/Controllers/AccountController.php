<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Ledger_Internal;
use App\AccountType;
use Illuminate\Support\Arr;

class AccountController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function listAccounts() {

        $accounts = Ledger_Internal::where('usage', 'header')->get()->toArray();

        $tree = $this->buildTree($accounts);

        $this->buildViewArray($tree);

    }

    public function buildViewArray(array $elements) {
        $view = array();
        $flattened = Arr::flatten($elements);
        dd($flattened);

        foreach ($elements as $element) {

            $temp_element = array_merge(array(), $element);
            $view[] = array_diff_key($temp_element, array_flip((array) ['children']));

            if(isset($element['children'])) {
                if (count($element['children']) > 0) {
                $this->buildViewArray($element['children']);
            }
            }

            
        }
        dd($view);

        return $view;
    }

    public function buildTree(array $elements, $parentId = 0) {
        $branch = array();

        foreach ($elements as $element) {
            if ((string)$element['parent_id']  === (string)$parentId) {
                $children = $this->buildTree($elements, $element['id']);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[] = $element;
            }
        }

        return $branch;
    }

}
