<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class BtcGainCalculatorWebRequest
 *
 * Use to validate the request for Web for the BTCGainCalculator
 *
 *
 * @package App\Http\Requests
 */
class BtcGainCalculatorWebRequest extends FormRequest
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
            'months' => 'sometimes|integer|max:18',
            'amount' => 'sometimes|integer|max:10000',
            'currency' => 'sometimes|in:nzd,aud,usd',
        ];
    }

    public function messages()
    {
        return [
            'months.integer' => 'Months should should be a number',
            'months.max' => 'Months should be equal or less than 18',
            'amount.integer' => 'Amount should should be a number',
            'amount.max' => 'Amount should be equal or less than 10000',
            'currency.in' => 'Currencies allowed are nzd,aud,usd',
        ];
    }

}
