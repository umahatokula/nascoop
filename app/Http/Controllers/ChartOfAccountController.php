<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ChartOfAccount;

class ChartOfAccountController extends Controller
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


    public function index() {
        $coas = ChartOfAccount::all();

        $data['assets'] = $this->getChildren($coas, 1);
        $data['liability'] = $this->getChildren($coas, 30);
        $data['income'] = $this->getChildren($coas, 52);
        $data['directCost'] = $this->getChildren($coas, 60);
        $data['expenses'] = $this->getChildren($coas, 67);

        return view('coa.index', $data);
    }


    public function getChildren1($coas, $id) {
        
        $result = [];
        $assets = $coas->where('id', $id);

        foreach($assets as $asset) {
            $result[] = $asset;
            $assets = $coas->where('parent_id', $asset->id);
            dd($assets);

            if ($assets->count() > 0) {
                $this->getChildren1($coas, $asset->id);
            }
            

        }

        return $result;
    }

    public function getChildren($coas, $id) {
        
        $result = [];
        $assets = $coas->where('id', $id);

        foreach($assets as $asset) {
            $result[] = $asset;
            $assets = $coas->where('parent_id', $asset->id);

            foreach ($assets as $asset) {
                $result[] = $asset;
                $assets = $coas->where('parent_id', $asset->id);

                foreach ($assets as $asset) {
                    $result[] = $asset;
                    $assets = $coas->where('parent_id', $asset->id);

                    foreach ($assets as $asset) {
                        $result[] = $asset;
                        $assets = $coas->where('parent_id', $asset->id);

                        foreach ($assets as $asset) {
                            $result[] = $asset;
                            $assets = $coas->where('parent_id', $asset->id);

                            foreach ($assets as $asset) {
                                $result[] = $asset;
                                $assets = $coas->where('parent_id', $asset->id);
                            }
                        }
                    }
                }
            }

        }

        return $result;
    }


    public function coaNewaccount() {
        $coas = ChartOfAccount::all();
        $signs = [
            ['label' => 'plus', 'value' => 'Plus',],
            ['label' => 'minus', 'value' => 'Minus',],
        ];

        if(request()->ajax()) {
            return [
                'coas' => $coas,
                'signs' => $signs,
            ];
        }

        $data['coas'] = $coas;

        return view('coa.newaccount', $data);
    }


    public function postCoaNewaccount(Request $request) {
        dd($request);
    }

}
