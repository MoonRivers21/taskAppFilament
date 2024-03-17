<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CreateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'min:3',
                'max:100',
                Rule::unique('tasks')->where(function ($query) {
                    // Get the authenticated user's ID
                    $userId = Auth::id();
                    // Add a condition to check uniqueness only within the user's task records
                    return $query->where('user_id', $userId);
                }),
            ],
            'content' => 'required|min:3',
            'user_id' => 'required|integer|not_in:0,null',
            'image' => ''

        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Title is required',
            'title.unique' => 'The title must be unique',
            'title.min' => 'Minimum of 3 characters for title',
            'title.max' => 'Maximum of 100 characters only for the field title',
            'content.required' => 'Content is required',
            'user_id' => 'Unauthorized action, Please contact system admin'
        ];

    }


}
