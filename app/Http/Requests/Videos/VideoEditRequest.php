<?php

namespace App\Http\Requests\Videos;

use Illuminate\Foundation\Http\FormRequest;

class VideoEditRequest extends FormRequest
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
        return [
            'subject_id'=>'nullable|exists:subjects,id',
            'grade_id'=>'nullable|exists:grades,id',
            'day'=>'nullable|numeric',
            'description'=>'nullable',
            'title'=>'required',
            'video_mode_id'=>'required'
        ];
    }
}
