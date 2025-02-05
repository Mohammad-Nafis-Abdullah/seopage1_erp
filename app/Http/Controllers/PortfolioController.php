<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectCms;
use App\Models\ProjectNiche;
use App\Models\ProjectPortfolio;
use App\Models\ProjectTimeLog;
use App\Models\ProjectWebsitePlugin;
use App\Models\ProjectWebsiteTheme;
use App\Models\ProjectWebsiteType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use function Google\Auth\Cache\get;
use function Symfony\Component\HttpClient\Response\select;

class PortfolioController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'Portfolio';
        $this->activeSettingMenu = 'portfolio';
        
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->project_cmss = ProjectCms::all();
        $this->website_types = ProjectWebsiteType::all();
        $this->website_categories = ProjectNiche::whereNull('parent_category_id')->get();
        $this->website_themes = ProjectWebsiteTheme::all();
        $this->website_plugins = ProjectWebsitePlugin::whereNotNull('plugin_name')->get();

//        $this->portfolios = DB::table('project_portfolios')
//            ->join('projects', 'project_portfolios.project_id', '=', 'projects.id')
//            ->join('users', 'projects.client_id', '=', 'users.id')
//            ->join('project_submissions', 'project_portfolios.project_id', '=', 'project_submissions.project_id')
//            ->select('project_portfolios.*', 'users.user_name', 'projects.project_name', 'projects.project_budget', 'project_submissions.actual_link')
//            ->get();

//                dd($this->website_subcategories);

        return view('portfolio.index', $this->data);
    }

    public function getSubCategory($website_cat_id)
    {
        //        dd($website_cat_id);
        $website_sub_cats = ProjectNiche::find($website_cat_id)->child;
        return response()->json($website_sub_cats);
    }


    public function filterCmsCategories(Request $request)
    {
        $filteredCategories = ProjectPortfolio::query();
        if (!is_null($request->input('category_id'))) {
            $selectedCategoryId = $request->input('category_id');
            $filteredCategories = $filteredCategories->where('cms_category', $selectedCategoryId);
        }

        if (!is_null($request->input('website_type'))) {
            $selectedCategoryId = $request->input('website_type');
            $filteredCategories = $filteredCategories->where('website_type', $selectedCategoryId);
        }

        if (!is_null($request->input('website_category'))) {
            $selectedCategoryId = $request->input('website_category');
            $filteredCategories = $filteredCategories->where('niche', $selectedCategoryId);
        }

        if (!is_null($request->input('website_sub_cat'))) {
            $selectedCategoryId = $request->input('website_sub_cat');
            $filteredCategories = $filteredCategories->where('sub_niche', $selectedCategoryId);
        }

        if (!is_null($request->input('theme_name'))) {
            $selectedCategoryId = $request->input('theme_name');
            $filteredCategories = $filteredCategories->where('theme_name', $selectedCategoryId);
        }

        if (!is_null($request->input('website_plugin'))) {
            $selectedCategoryId = $request->input('website_plugin');
            $filteredCategories = $filteredCategories->where('plugin_name', $selectedCategoryId);
        }

        $filteredCategories = $filteredCategories->where('portfolio_link', '!=', null)->get();
        return response()->json($filteredCategories);
    }

    public function calculateProjectLoggedTime($projectId)
            {
                $project_time_logs_hours = ProjectTimeLog::where('project_id', $projectId)->sum('total_hours');
                $project_time_logs_minutes = ProjectTimeLog::where('project_id', $projectId)->sum('total_minutes');
                
                $project_time_hours = intval($project_time_logs_minutes / 60);
                $project_time_minutes = $project_time_logs_minutes % 60;
                
                $currentTime = Carbon::now();
                
                $totalMinutes = DB::table('project_time_logs')
                    ->where('project_id', $projectId)
                    ->whereNull('end_time')
                    ->select(DB::raw("SUM(TIME_TO_SEC(TIMEDIFF('$currentTime', start_time)))/60 as total_minutes"))
                    ->value('total_minutes');
                
                $active_time_hours = intval(round($totalMinutes, 1) / 60);
                $active_time_minutes = intval(round($totalMinutes, 1) % 60);
                
                $update_hours = $project_time_minutes + $active_time_minutes / 60;
                $update_minutes = $project_time_minutes + $active_time_minutes % 60;
                
                if ($project_time_minutes + $active_time_minutes >= 60) {
                    $add_hours = intval(round(($project_time_minutes + $active_time_minutes) / 60, 1));
                    $add_minutes = ($project_time_minutes + $active_time_minutes) % 60;
                } else {
                    $add_hours = 0;
                    $add_minutes = $project_time_minutes + $active_time_minutes;
                }
                
                $total_hours = intval(round($project_time_hours, 1)) + $active_time_hours + $add_hours + $add_minutes / 60; 

                $total_minutes = $total_hours * 60 + $add_minutes;
                
                return [
                    'total_hours' => $total_hours,
                    'total_minutes' => $total_minutes
                ];
            }


    public function filterDataShow($portfolio_id)
    { 

        $portfolio = DB::table('project_portfolios')
                    ->select(
                        'project_portfolios.*', 
                        'users.user_name as client_name', 
                        'users.image as client_image', 
                        'users.id as client_id',
                        'projects.id as project_id',
                        'projects.project_name', 
                        'projects.project_budget', 
                        'project_submissions.actual_link',
                        'projects.hours_allocated'
                    )
                    ->where('project_portfolios.id', $portfolio_id)
                    ->leftJoin('projects', 'project_portfolios.project_id', '=', 'projects.id')
                    ->leftJoin('users', 'projects.client_id', '=', 'users.id')
                    ->leftJoin('project_submissions', 'project_portfolios.project_id', '=', 'project_submissions.project_id')
                    ->first(); 
        
        if($portfolio->sub_niche){
            $portfolio->sub_niche = DB::table('project_niches')->where('id', $portfolio->sub_niche)->first();
        }

        if($portfolio->niche){
            $portfolio->niche = DB::table('project_niches')->where('id', $portfolio->niche)->first();
        }

        if($portfolio->theme_name && is_numeric($portfolio->theme_name)){
            $theme_id = $portfolio->theme_name; 
            $theme_data = ProjectWebsiteTheme::find($theme_id); 
            $portfolio->theme_name = $theme_data? $theme_data->theme_name : null;   
        }

        $portfolio->estimated_total_minutes =  ($portfolio->hours_allocated) * 60;
        $logged_hours = $this->calculateProjectLoggedTime($portfolio->project_id);

        $portfolio->total_logged_time = $logged_hours["total_minutes"];

        $total_hours = $logged_hours["total_hours"];
        $portfolio->hourly_budget = $total_hours > 0 ? round($portfolio->project_budget / $total_hours, 2) : 0;

        // Average hourly price based on the final logged hours
         
        return response()->json($portfolio, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }



    // get filter data
    public function get_filter_data(){
        $project_cms = ProjectCms::select("id", "cms_name")->get();
        $website_types = ProjectWebsiteType::select("id", "website_type")->get();
        $website_categories = ProjectNiche::select("id", "category_name", "parent_category_id", "sub_category_id")->get();
        $website_themes = ProjectWebsiteTheme::all();
        $website_plugins = ProjectWebsitePlugin::whereNotNull('plugin_name')->get();

        $data = [
            "project_cms"=> $project_cms,
            "website_types" => $website_types,
            "website_categories" => $website_categories,
            "website_themes" => $website_themes,
            "website_plugins" => $website_plugins
        ]; 

        return Response()->json($data, 200);
    }


    // get portfolio data
    public function get_portfolio_data(Request $request){
        $cms = $request->cms ?? null;
        $website_type = $request->website_type ?? null;
        $website_category = $request->website_category ?? null;
        $website_sub_category = $request->website_sub_category ?? null;
        $theme_name = $request->theme_name ?? null;
        $theme_id = $request->theme_id ?? null;
        $plugin_name = $request->plugin_name ?? null;
        $plugin_id = $request->plugin_id ?? null; 
        $page_size = $request->page_size ?? 10;
 
        
        $data = DB::table('project_portfolios')
            ->where('portfolio_link', '!=', null)
            ->whereNotIn('portfolio_link',["n/a", "N/A","null", "na", "NA"])
            ->where(function($query) use ($cms, $website_type, $website_category, $website_sub_category, $theme_name, $theme_id, $plugin_name, $plugin_id) {
                if ($cms) {
                    $query->where('cms_category', $cms);
                }
        
                if ($website_type) {
                    $query->where('website_type', $website_type);
                }
        
                if ($website_category) {
                    $query->where('niche', $website_category);
                }
        
                if ($website_sub_category) {
                    $query->where('sub_niche', $website_sub_category);
                }
        
                if ($theme_name) {
                    $query->where('theme_name', $theme_name)
                        ->orWhere('theme_name', $theme_id);
                }
 
        
                if ($plugin_name) {
                    $query->where('plugin_name', $plugin_name)
                        ->orWhere('plugin_name', $plugin_id);
                }
 
            })->paginate($page_size);
        
        return response()->json($data, 200);
    }
}
    