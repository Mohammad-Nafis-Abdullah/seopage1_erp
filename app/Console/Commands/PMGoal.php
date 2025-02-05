<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Task;
use App\Models\Project;
use App\Models\ProjectPmGoal;
use Carbon\Carbon;
use App\Models\ContractSign;
use App\Models\ProjectDeliverable;
use DB;
use App\Models\Payment;
use App\Models\ProjectMilestone;

class PMGoal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pm_goal_check:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pm Goal Check';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
      
        $goals= ProjectPmGoal::where('goal_status',0)->where('description',null)->get();
      //  dd($goals);
        foreach ($goals as $goal) {
            $current_date = Carbon::now();
            if($goal->extended_goal_end_day == null)
            {
                $end_date = $goal->goal_end_date;
            }else 
            {
                $end_date = $goal->extended_goal_end_day;
            }
            if($goal->goal_code == 'DCS' && $current_date > $end_date)
            {
               // DB::beginTransaction();
                $goal_update= ProjectPmGoal::where('id',$goal->id)->first();
                $deliverable_sign= ContractSign::where('project_id',$goal->project_id)->first();
               // dd($deliverable_sign);
                $task_count= Task::where('project_id',$goal->project_id)->count();
                $deliverables_count = ProjectDeliverable::where('project_id',$goal->project_id)->count();
              //  dd($deliverables_count -$task_count);
                // $goal_count= ProjectPmGoal::where('project_id',$goal->project_id)->count();
                // $goal_met_count= ProjectPmGoal::where('project_id',$goal->project_id)->count();
                if($deliverable_sign == null && $deliverables_count > 0 &&  $task_count > 0)
                {
                    $goal_update->description = 'Deliverables is not signed and tasks has been created properly';
                }elseif($deliverable_sign == null && $deliverables_count > 0  && $task_count < 1)
                {
                    $goal_update->description = 'Deliverables is not signed and tasks has not been created properly';

                }elseif($deliverable_sign != null && $deliverables_count > 0 &&  $task_count > 0)
                {
                    $goal_update->description = 'Deliverables is signed and tasks has been created properly';

                }elseif($deliverable_sign != null && $deliverables_count > 0 && $task_count < 1)
                {
                    $goal_update->description = 'Deliverables is signed and tasks has not been created properly';

                }else 
                {
                    $goal_update->description = 'Deliverables is not signed and tasks has not been created properly';
                }
                $goal_update->updated_at = Carbon::now();
                $goal_update->save();
              //  dd($goal_update);


            }elseif($goal->goal_code == 'TSM' && $current_date > $end_date)
            {
                $taskId= Task::select('tasks.*','task_history.created_at as submission_date')
                ->join('task_history','task_history.task_id','tasks.id')
                ->where('task_history.board_column_id',9)
                ->where('tasks.project_id',$goal->project_id)->first();
                if($taskId == null || $current_date > $taskId->submission_date)
                {
                    $goal_update= ProjectPmGoal::where('id',$goal->id)->first();
                    $goal_update->description = 'The first submission has not been completed and it is not ready for submission to the client';
                    $goal_update->updated_at = Carbon::now();
                    $goal_update->save();

                }
                
                
            }elseif($goal->goal_code == 'FPMR' && $current_date > $end_date)
            {
                $projectId= Payment::where('project_id',$goal->project_id)->first();
                $total_milestones= ProjectMilestone::where('project_id',$goal->project_id)->count();
                $complete_milestones= ProjectMilestone::join('payments','payments.project_id','project_milestones.project_id')->where('project_milestones.project_id',$goal->project_id)->where('payments.status','complete')->count();

                $total_milestones_value= ProjectMilestone::where('project_id',$goal->project_id)->sum('cost');
                $total_complete_milestones_value= ProjectMilestone::join('payments','payments.project_id','project_milestones.project_id')->where('project_milestones.project_id',$goal->project_id)->where('payments.status','complete')->sum('amount');
                $completion_percent = $total_complete_milestones_value/$total_milestones_value;
                if(($projectId == null || $current_date > $projectId->created_at) && $completion_percent < 0.5)
                {
                    $goal_update= ProjectPmGoal::where('id',$goal->id)->first();
                    $goal_update->description = $complete_milestones . ' out of '.$total_milestones. ' milestones could not be released in this week';
                    $goal_update->updated_at = Carbon::now();
                    $goal_update->save();

                }
                
                
            }
            
            elseif($goal->goal_code == 'MPMR' && $current_date > $end_date)
            {
                $projectId= Payment::where('project_id',$goal->project_id)->first();
                $total_milestones= ProjectMilestone::where('project_id',$goal->project_id)->count();
                $complete_milestones= ProjectMilestone::join('payments','payments.project_id','project_milestones.project_id')->where('project_milestones.project_id',$goal->project_id)->where('payments.status','complete')->count();

                
                $completion_percent = $total_milestones/2;
                $required_to_complete= round($completion_percent +1,0);
                if(($projectId == null || $current_date > $projectId->created_at) && $complete_milestones >= $required_to_complete )
                {
                    $goal_update= ProjectPmGoal::where('id',$goal->id)->first();
                    $goal_update->description = $complete_milestones . ' out of '.$required_to_complete. ' milestones could not be released in this week';
                    $goal_update->updated_at = Carbon::now();
                    $goal_update->save();

                }
                
                
            }
            elseif($goal->goal_code == 'MMPMR' && $current_date > $end_date)
            {
                $projectId= Payment::where('project_id',$goal->project_id)->first();
                $total_milestones= ProjectMilestone::where('project_id',$goal->project_id)->count();
                $complete_milestones= ProjectMilestone::join('payments','payments.project_id','project_milestones.project_id')->where('project_milestones.project_id',$goal->project_id)->where('payments.status','complete')->count();

                
                $completion_percent = $total_milestones/2;
                $required_to_complete= round($completion_percent +2,0);
                if(($projectId == null || $current_date > $projectId->created_at) && $complete_milestones >= $required_to_complete )
                {
                    $goal_update= ProjectPmGoal::where('id',$goal->id)->first();
                    $goal_update->description = $complete_milestones . ' out of '.$required_to_complete. ' milestones could not be released in this week';
                    $goal_update->updated_at = Carbon::now();
                    $goal_update->save();

                }
                
                
            }
            elseif($goal->goal_code == 'LM' && $current_date > $end_date)
            {
                $projectId= Payment::where('project_id',$goal->project_id)->first();
                $total_milestones= ProjectMilestone::where('project_id',$goal->project_id)->count();
                $complete_milestones= ProjectMilestone::join('payments','payments.project_id','project_milestones.project_id')->where('project_milestones.project_id',$goal->project_id)->where('payments.status','complete')->count();

                
                $completion_percent = $total_milestones/2;
                $required_to_complete= round($completion_percent +3,0);
                if(($projectId == null || $current_date > $projectId->created_at) && $complete_milestones >= $required_to_complete )
                {
                    $goal_update= ProjectPmGoal::where('id',$goal->id)->first();
                    $goal_update->description = $complete_milestones . ' out of '.$required_to_complete. ' milestones could not be released in this week';
                    $goal_update->updated_at = Carbon::now();
                    $goal_update->save();

                }
                
                
            }
            
            elseif($goal->goal_code == 'FMR' && $current_date > $end_date)
            {
                $projectId= Payment::where('project_id',$goal->project_id)->first();
                $total_milestones= ProjectMilestone::where('project_id',$goal->project_id)->count();
                $complete_milestones= ProjectMilestone::join('payments','payments.project_id','project_milestones.project_id')->where('project_milestones.project_id',$goal->project_id)->where('payments.status','complete')->count();

                if(($projectId == null || $current_date > $projectId->created_at) && $complete_milestones >= 1 )
                {
                    $goal_update= ProjectPmGoal::where('id',$goal->id)->first();
                    $goal_update->description = $complete_milestones . ' out of 1 milestones could not be released in this week';
                    $goal_update->updated_at = Carbon::now();
                    $goal_update->save();

                }

            }
        }
  
        

        $this->info('PM Goal Check Successfully');
    }
}
