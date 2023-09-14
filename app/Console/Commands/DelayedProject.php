<?php

namespace App\Console\Commands;

use App\Models\PMProject;
use App\Models\ProjectRequestTimeExtension;
use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Project;
use DateTime;
use Carbon\Carbon;
use Notification;

class DelayedProject extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string    
     */
    protected $signature = 'delayed_project_check:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delayed Projects Check Daily';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $projects= Project::select('projects.*')
        ->join('deals','deals.id','projects.deal_id')
        ->join('p_m_projects','p_m_projects.project_id','projects.id')
        
        ->where('deals.project_type','fixed')
        ->whereIn('projects.status',['in progress', 'finished'])
       
        ->where('p_m_projects.delayed_status',0)->get();
  
          //$daily_bonus= User::where('id',Auth::id())->first();
          //dd($daily_bonus->packages->price);
          //dd($sponsor_bonus['royality_bonus']);
  
          foreach ($projects as $project) {
  
            $find_request= ProjectRequestTimeExtension::where('project_id',$project->id)->where('status','Approved')->orderBy('id','desc')->first();
                 
            $to = new DateTime($project['created_at']);
            if($find_request != null)
            {
              $to = new DateTime($project['created_at']->addDay($find_request->day));
              
            }else 
            {
              $to = new DateTime($project['created_at']);
            }
            $from = new DateTime(); // Using Carbon::now() is unnecessary, DateTime already represents the current time
            
            $interval = $from->diff($to);
            $days = $interval->days;
                 
  
                  if($days >= 15)
                  {
                    $project_id= PMProject::where('project_id',$project->id)->first();
                    $project_id->delayed_status = 1; 
                    $project_id->save();
                 
                  }
                
                 
  
  
          }
  
          $this->info('Project marks as delayed');
    }
}
