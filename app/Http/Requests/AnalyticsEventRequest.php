<?php

namespace App\Http\Requests;

use App\Models\AnalyticsEvent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnalyticsEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event' => [
                'required',
                Rule::in([
                    AnalyticsEvent::EVENT_VISIT,
                    AnalyticsEvent::EVENT_ITEM_COPY,
                ]),
            ],

            'visitor_id' => [
                'required',
                'uuid',
            ],

            'session_id' => [
                'required',
                'uuid',
            ],

            'item_id' => [
                'required_if:event,' . AnalyticsEvent::EVENT_ITEM_COPY,
                'prohibited_if:event,' . AnalyticsEvent::EVENT_VISIT,
                'integer',
                'exists:items,id',
            ],
        ];
    }
}
