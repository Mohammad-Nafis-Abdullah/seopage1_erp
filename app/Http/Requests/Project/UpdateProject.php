<?php

namespace App\Http\Requests\Project;

use App\Http\Requests\CoreRequest;
use App\Models\CustomField;
use App\Models\Project;
use AWS\CRT\HTTP\Request;

class UpdateProject extends CoreRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        $rules = [
            'project_name' => 'required|max:150',
            'start_date' => 'required',
            'comments' => [
                function ($attribute, $value, $fail) {
                    $allowedChallenges = [
                        'Has Challenge But We Can Do It',
                        'Has Challenge But We Cannot Do It',
                        'Has Challenge, But We May/May Not be Able to Do It',
                    ];
                    
                    $projectChallenge = request()->input('project_challenge');
        
                    if (in_array($projectChallenge, $allowedChallenges) && empty($value)) {
                        $fail("The $attribute field is required when selecting certain project challenges.");
                    }
                },
            ],

            // 'hours_allocated' => 'required|numeric',
            'client_id' => 'requiredIf:client_view_task,true',
            'project_code' => 'required|unique:projects,project_short_code,'.$this->route('project'),
        ];
        if ($this->project_summary) {
            $rules['project_summary'] = 'required';
        }
        if ($this->project_challenge) {
            $rules['project_challenge'] = 'required';
        }

        // if (!$this->has('without_deadline')) {
        //     $rules['deadline'] = 'required';
        // }

        if ($this->project_budget != '') {
            $rules['project_budget'] = 'numeric';

        }
        if($this->dept_status != 'DM'){
            if($this->status_validation == 'not started')
            {
                $rules['requirement_defined'] = 'required';
                $rules['deadline_meet'] = 'required';

            }
        }

        $project = Project::find(request()->project_id);

        if (request()->private && in_array('employee', user_roles()))  {
            $rules['user_id.0'] = 'required';
        }

        if (!request()->private && !request()->public && $project->public == 0 && request()->member_id) {
            $rules['member_id.0'] = 'required';
        }
    //    dd($project->deadline);
        // if($project->deal->project_type != 'hourly')
        // {
        //    $rules['deadline'] = 'required';
        // }

        if (request()->get('custom_fields_data')) {
            $fields = request()->get('custom_fields_data');

            foreach ($fields as $key => $value) {
                $idarray = explode('_', $key);
                $id = end($idarray);
                $customField = CustomField::findOrFail($id);

                if ($customField->required == 'yes' && (is_null($value) || $value == '')) {
                    $rules['custom_fields_data['.$key.']'] = 'required';
                }
            }
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'user_id.0.required' => __('messages.atleastOneValidation'),
            'project_code.required' => __('messages.projectCodeRequired'),
            'member_id.0.required' => __('messages.atleastOneValidation')
        ];
    }

    public function attributes()
    {
        $attributes = [];

        if (request()->get('custom_fields_data')) {
            $fields = request()->get('custom_fields_data');

            foreach ($fields as $key => $value) {
                $idarray = explode('_', $key);
                $id = end($idarray);
                $customField = CustomField::findOrFail($id);

                if ($customField->required == 'yes') {
                    $attributes['custom_fields_data['.$key.']'] = $customField->label;
                }
            }
        }

        return $attributes;
    }

}
